<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Enums\BranchMenuStatus;
use App\Enums\PlannedMenuStatus;
use App\Models\BranchMenu;
use App\Models\BranchMenuItem;
use App\Models\MenuCatalogItem;
use App\Models\NonCookingDay;
use App\Models\PlannedMenu;
use App\Models\PlannedMenuItem;
use App\Models\PlannedMenuItemBranch;
use App\Models\RestaurantContactInformation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
                $isNonCookingDay = in_array($date, $nonCookingDates, true);
                $plannedDay = $plannedMenu->days()->updateOrCreate(
                    ['date' => $date],
                    ['is_non_cooking_day' => $isNonCookingDay],
                );

                if ($isNonCookingDay) {
                    DB::table('planned_menu_common_item_days')
                        ->where('planned_menu_day_id', $plannedDay->getKey())
                        ->delete();
                }
            }
        });
    }

    public function createMissingBranchVariants(PlannedMenuItem $item): void
    {
        $item->loadMissing('day.plannedMenu.branches', 'plannedMenu.branches');
        $plannedMenu = $item->plannedMenu ?? $item->day?->plannedMenu;

        if (! $plannedMenu instanceof PlannedMenu) {
            return;
        }

        foreach ($plannedMenu->branches as $branch) {
            $item->branchVariants()->firstOrCreate(
                ['planned_menu_branch_id' => $branch->getKey()],
                ['is_available' => true],
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
                'days.items.catalogItem.allergens',
                'days.items.unit',
                'days.items.branchVariants.sideItems.allergens',
                'days.items.branchVariants.otherItems.allergens',
                'commonItems.scheduledDays',
                'commonItems.catalogItem.allergens',
                'commonItems.unit',
                'commonItems.branchVariants.sideItems.allergens',
                'commonItems.branchVariants.otherItems.allergens',
            ]);

            $this->validateForApproval($lockedMenu);

            $plannedDays = $this->plannedWeekDays($lockedMenu);

            foreach ($lockedMenu->branches as $plannedBranch) {
                $branchMenu = BranchMenu::query()->create([
                    'planned_menu_id' => $lockedMenu->getKey(),
                    'restaurant_contact_information_id' => $plannedBranch->restaurant_contact_information_id,
                    'branch_name_snapshot' => $plannedBranch->branch_name_snapshot,
                    'week_start' => $lockedMenu->week_start,
                    'week_end' => $lockedMenu->week_end,
                    'status' => BranchMenuStatus::Ready,
                ]);

                foreach ($plannedDays as $plannedDay) {
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

                        $this->publishBranchMenuItem($branchDay, $plannedItem, $variant);
                    }

                    $lastDailySortOrder = (int) ($plannedDay->items->max('sort_order') ?? 0);
                    $commonItems = $lockedMenu->commonItems
                        ->filter(fn (PlannedMenuItem $item): bool => $item->scheduledDays->contains('id', $plannedDay->getKey()))
                        ->values();

                    foreach ($commonItems as $commonItemIndex => $commonItem) {
                        $variant = $commonItem->branchVariants->firstWhere('planned_menu_branch_id', $plannedBranch->getKey());

                        if (! $variant instanceof PlannedMenuItemBranch) {
                            continue;
                        }

                        $commonItem->setAttribute('sort_order', $lastDailySortOrder + $commonItemIndex + 1);
                        $this->publishBranchMenuItem($branchDay, $commonItem, $variant);
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

    private function publishBranchMenuItem(
        \App\Models\BranchMenuDay $branchDay,
        PlannedMenuItem $plannedItem,
        PlannedMenuItemBranch $variant,
    ): void {
        $baseAllergens = $plannedItem->catalogItem->allergens->pluck('code')->filter()->all();
        $localItems = $variant->sideItems->concat($variant->otherItems);
        $extraAllergens = $localItems
            ->flatMap(fn (MenuCatalogItem $catalogItem) => $catalogItem->allergens->pluck('code'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        BranchMenuItem::withoutEvents(function () use ($branchDay, $plannedItem, $variant, $baseAllergens, $extraAllergens): void {
            $branchItem = $branchDay->items()->create([
                'source_planned_menu_item_id' => $plannedItem->getKey(),
                'type' => $plannedItem->type,
                'menu_catalog_item_id' => $plannedItem->menu_catalog_item_id,
                'item_name_snapshot' => $plannedItem->catalogItem->name,
                'amount' => $plannedItem->amount,
                'menu_unit_id' => $plannedItem->menu_unit_id,
                'unit_symbol_snapshot' => $plannedItem->unit?->symbol,
                'price' => $plannedItem->default_price,
                'is_available' => $variant->is_available,
                'is_common_menu_item' => $plannedItem->planned_menu_id !== null,
                'sort_order' => $plannedItem->sort_order,
                'allergens_snapshot' => collect($baseAllergens)->merge($extraAllergens)->unique()->sort()->values()->all(),
            ]);

            foreach (['side' => $variant->sideItems, 'other' => $variant->otherItems] as $kind => $catalogItems) {
                foreach ($catalogItems as $sortOrder => $catalogItem) {
                    $branchItem->catalogItems()->create([
                        'menu_catalog_item_id' => $catalogItem->getKey(),
                        'kind' => $kind,
                        'name_snapshot' => $catalogItem->name,
                        'allergens_snapshot' => $catalogItem->allergens->pluck('code')->filter()->sort()->values()->all(),
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
        });
    }

    /** @return EloquentCollection<int, \App\Models\PlannedMenuDay> */
    private function plannedWeekDays(PlannedMenu $plannedMenu): EloquentCollection
    {
        $weekStart = CarbonImmutable::parse($plannedMenu->week_start);
        $dates = collect(range(0, 4))
            ->map(fn (int $offset): string => $weekStart->addDays($offset)->toDateString());

        return $plannedMenu->days
            ->filter(fn ($day): bool => $dates->contains($day->date->toDateString()))
            ->sortBy('date')
            ->values();
    }

    private function validateForApproval(PlannedMenu $plannedMenu): void
    {
        if ($plannedMenu->branches->isEmpty()) {
            throw ValidationException::withMessages(['approval' => 'Plán neobsahuje žádnou provozovnu.']);
        }

        foreach ($this->plannedWeekDays($plannedMenu) as $day) {
            if ($day->is_non_cooking_day) {
                continue;
            }

            $commonItems = $plannedMenu->commonItems
                ->filter(fn (PlannedMenuItem $item): bool => $item->scheduledDays->contains('id', $day->getKey()));
            $items = $day->items->concat($commonItems);

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['approval' => 'Každý vařící den musí obsahovat alespoň jednu položku.']);
            }

            foreach ($items as $item) {
                if ($item->branchVariants->count() !== $plannedMenu->branches->count()) {
                    throw ValidationException::withMessages(['approval' => 'Každá položka musí být připravena pro všechny provozovny.']);
                }
            }
        }
    }
}
