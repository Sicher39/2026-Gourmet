<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Enums\BranchMenuStatus;
use App\Enums\PlannedMenuStatus;
use App\Models\BranchMenu;
use App\Models\MenuCatalogItem;
use App\Models\NonCookingDay;
use App\Models\PlannedMenu;
use App\Models\PlannedMenuItem;
use App\Models\PlannedMenuItemBranch;
use App\Models\RestaurantContactInformation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlannedMenuService
{
    public function initialize(PlannedMenu $plannedMenu): void
    {
        DB::transaction(function () use ($plannedMenu): void {
            foreach (RestaurantContactInformation::query()->orderBy('business_name')->get() as $restaurant) {
                $plannedMenu->branches()->firstOrCreate(
                    ['restaurant_contact_information_id' => $restaurant->getKey()],
                    ['branch_name_snapshot' => $restaurant->business_name],
                );
            }

            $weekStart = CarbonImmutable::parse($plannedMenu->week_start);
            $nonCookingDates = NonCookingDay::query()
                ->whereBetween('date', [$weekStart->toDateString(), $weekStart->addDays(4)->toDateString()])
                ->pluck('date')
                ->map(fn (mixed $date): string => CarbonImmutable::parse($date)->toDateString())
                ->all();

            for ($offset = 0; $offset < 5; $offset++) {
                $date = $weekStart->addDays($offset)->toDateString();
                $plannedMenu->days()->firstOrCreate(
                    ['date' => $date],
                    ['is_non_cooking_day' => in_array($date, $nonCookingDates, true)],
                );
            }
        });
    }

    public function createMissingBranchVariants(PlannedMenuItem $item): void
    {
        $item->loadMissing('day.plannedMenu.branches');

        foreach ($item->day->plannedMenu->branches as $branch) {
            $item->branchVariants()->firstOrCreate(
                ['planned_menu_branch_id' => $branch->getKey()],
                [
                    'price' => $item->default_price,
                    'amount' => $item->amount,
                    'menu_unit_id' => $item->menu_unit_id,
                    'is_available' => true,
                ],
            );
        }
    }

    public function approve(PlannedMenu $plannedMenu, User $approver): PlannedMenu
    {
        if (! $approver->canApprovePlannedMenu()) {
            throw ValidationException::withMessages(['approval' => 'Nemáte oprávnění jídelní lístek odsouhlasit.']);
        }

        return DB::transaction(function () use ($plannedMenu, $approver): PlannedMenu {
            $lockedMenu = PlannedMenu::query()->lockForUpdate()->findOrFail($plannedMenu->getKey());

            if (! $lockedMenu->isDraft()) {
                throw ValidationException::withMessages(['approval' => 'Jídelní lístek již byl odsouhlasen.']);
            }

            $lockedMenu->load([
                'branches.restaurant',
                'days.items.product.components.catalogItems.allergens',
                'days.items.unit',
                'days.items.branchVariants.catalogItems.allergens',
                'days.items.branchVariants.unit',
            ]);

            $this->validateForApproval($lockedMenu);

            foreach ($lockedMenu->branches as $plannedBranch) {
                $branchMenu = BranchMenu::query()->create([
                    'planned_menu_id' => $lockedMenu->getKey(),
                    'restaurant_contact_information_id' => $plannedBranch->restaurant_contact_information_id,
                    'branch_name_snapshot' => $plannedBranch->branch_name_snapshot,
                    'week_start' => $lockedMenu->week_start,
                    'week_end' => $lockedMenu->week_end,
                    'status' => BranchMenuStatus::Ready,
                ]);

                foreach ($lockedMenu->days as $plannedDay) {
                    $branchDay = $branchMenu->days()->create([
                        'date' => $plannedDay->date,
                        'is_non_cooking_day' => $plannedDay->is_non_cooking_day,
                    ]);

                    if ($plannedDay->is_non_cooking_day) {
                        continue;
                    }

                    foreach ($plannedDay->items as $plannedItem) {
                        $variant = $plannedItem->branchVariants->firstWhere('planned_menu_branch_id', $plannedBranch->getKey());

                        if (! $variant instanceof PlannedMenuItemBranch) {
                            continue;
                        }

                        $productAllergens = $plannedItem->product->computeAllergenSnapshot();
                        $extraAllergens = $variant->catalogItems
                            ->flatMap(fn (MenuCatalogItem $catalogItem) => $catalogItem->allergens->pluck('code'))
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        $branchItem = $branchDay->items()->create([
                            'source_planned_menu_item_id' => $plannedItem->getKey(),
                            'type' => $plannedItem->type,
                            'menu_product_id' => $plannedItem->menu_product_id,
                            'product_name_snapshot' => $plannedItem->product->composeNameFromComponents(),
                            'amount' => $variant->amount,
                            'menu_unit_id' => $variant->menu_unit_id,
                            'unit_symbol_snapshot' => $variant->unit?->symbol,
                            'price' => $variant->price,
                            'is_available' => $variant->is_available,
                            'sort_order' => $plannedItem->sort_order,
                            'allergens_snapshot' => collect(array_values($productAllergens))->merge($extraAllergens)->unique()->sort()->values()->all(),
                        ]);

                        foreach ($variant->catalogItems as $catalogItem) {
                            $branchItem->catalogItems()->create([
                                'menu_catalog_item_id' => $catalogItem->getKey(),
                                'name_snapshot' => $catalogItem->name,
                                'allergens_snapshot' => $catalogItem->allergens->pluck('code')->filter()->sort()->values()->all(),
                                'sort_order' => $catalogItem->pivot->sort_order ?? 0,
                            ]);
                        }
                    }
                }
            }

            $lockedMenu->update([
                'status' => PlannedMenuStatus::Approved,
                'approved_by' => $approver->getKey(),
                'approved_at' => now(),
            ]);

            return $lockedMenu->refresh();
        });
    }

    private function validateForApproval(PlannedMenu $plannedMenu): void
    {
        if ($plannedMenu->branches->isEmpty()) {
            throw ValidationException::withMessages(['approval' => 'Plán neobsahuje žádnou provozovnu.']);
        }

        foreach ($plannedMenu->days as $day) {
            if ($day->is_non_cooking_day) {
                continue;
            }

            if ($day->items->isEmpty()) {
                throw ValidationException::withMessages(['approval' => 'Každý vařící den musí obsahovat alespoň jednu položku.']);
            }

            foreach ($day->items as $item) {
                if ($item->branchVariants->count() !== $plannedMenu->branches->count()) {
                    throw ValidationException::withMessages(['approval' => 'Každá položka musí být připravena pro všechny provozovny.']);
                }
            }
        }
    }
}
