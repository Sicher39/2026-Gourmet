<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Enums\BranchMenuStatus;
use App\Enums\MenuItemType;
use App\Models\BranchMenuDay;
use App\Models\BranchMenuItem;
use App\Models\RestaurantContactInformation;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BranchMenuFrontendService
{
    /**
     * @return array{today: array<string, mixed>, upcoming: array<int, array<string, mixed>>}
     */
    public function forRestaurant(RestaurantContactInformation $restaurant, bool $onlyWebVisible = false): array
    {
        $today = CarbonImmutable::today();
        $upcomingDates = $this->upcomingDates($today);
        $dates = collect([$today, ...$upcomingDates]);
        $days = $this->menuDays($restaurant, $dates, $onlyWebVisible);

        return [
            'today' => $this->dayPayload($today, $days->get($today->toDateString()), true),
            'upcoming' => collect($upcomingDates)
                ->map(fn (CarbonImmutable $date): array => $this->dayPayload(
                    $date,
                    $days->get($date->toDateString()),
                ))
                ->all(),
        ];
    }

    /** @return array<int, CarbonImmutable> */
    private function upcomingDates(CarbonImmutable $today): array
    {
        if ($today->dayOfWeekIso >= 5) {
            $nextMonday = $today->next(CarbonInterface::MONDAY);

            return collect(range(0, 4))
                ->map(fn (int $offset): CarbonImmutable => $nextMonday->addDays($offset))
                ->all();
        }

        return collect(range($today->dayOfWeekIso, 4))
            ->map(fn (int $offset): CarbonImmutable => $today->startOfWeek()->addDays($offset))
            ->all();
    }

    /**
     * @param  Collection<int, CarbonImmutable>  $dates
     * @return Collection<string, BranchMenuDay>
     */
    private function menuDays(RestaurantContactInformation $restaurant, Collection $dates, bool $onlyWebVisible): Collection
    {
        return BranchMenuDay::query()
            ->whereBetween('date', [
                $dates->min()->toDateString(),
                $dates->max()->toDateString(),
            ])
            ->whereHas('branchMenu', fn (Builder $query): Builder => $query
                ->where('restaurant_contact_information_id', $restaurant->getKey())
                ->where('status', BranchMenuStatus::Ready)
                ->whereColumn('branch_menu_days.date', '>=', 'branch_menus.week_start')
                ->whereColumn('branch_menu_days.date', '<=', 'branch_menus.week_end'))
            ->with([
                'items' => function (HasMany $query) use ($onlyWebVisible): void {
                    $query
                        ->where('is_available', true)
                        ->when($onlyWebVisible, fn (Builder $query): Builder => $query->where('show_on_web', true))
                        ->with(['sideItems', 'otherItems']);
                },
            ])
            ->get()
            ->keyBy(fn (BranchMenuDay $day): string => $day->date->toDateString());
    }

    /** @return array<string, mixed> */
    private function dayPayload(CarbonImmutable $date, ?BranchMenuDay $day, bool $isToday = false): array
    {
        $isMissing = ! $day instanceof BranchMenuDay;
        $isNonCookingDay = $isMissing || $day->is_non_cooking_day;
        $items = $day?->items ?? collect();

        return [
            'day' => ucfirst($date->locale('cs')->isoFormat('dddd')),
            'date' => $date->format('j. n. Y'),
            'isNonCookingDay' => $isNonCookingDay,
            'nonCookingMessage' => match (true) {
                ! $isNonCookingDay => null,
                $isToday => 'Dnes nevaříme',
                $isMissing => 'Nabídku připravujeme',
                default => 'Tento den nevaříme',
            },
            'soupItems' => $this->itemsPayload($items, MenuItemType::Soup),
            'menuItems' => $this->itemsPayload($items, MenuItemType::Main),
            'pizzaItems' => $this->itemsPayload($items, MenuItemType::Pizza),
            'grillItems' => $this->itemsPayload($items, MenuItemType::Grill),
        ];
    }

    /**
     * @param  Collection<int, BranchMenuItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private function itemsPayload(Collection $items, MenuItemType $type): array
    {
        return $items
            ->filter(fn (BranchMenuItem $item): bool => $item->type === $type)
            ->values()
            ->map(function (BranchMenuItem $item, int $index) use ($type): array {
                $sideItems = $item->sideItems
                    ->pluck('name_snapshot')
                    ->filter()
                    ->implode(' / ');
                $name = collect([
                    $item->item_name_snapshot,
                    $sideItems,
                    ...$item->otherItems->pluck('name_snapshot'),
                ])->filter()->implode(', ');

                return [
                    $this->itemIndexKey($type) => $index + 1,
                    'allergens' => collect($item->allergens_snapshot)->filter()->implode(', '),
                    'weight' => $this->formatDecimal($item->amount),
                    'unit' => (string) ($item->unit_symbol_snapshot ?? ''),
                    $this->itemNameKey($type) => $name,
                    'price' => $this->formatDecimal($item->price),
                    'enabled' => true,
                ];
            })
            ->all();
    }

    private function itemIndexKey(MenuItemType $type): string
    {
        return $type === MenuItemType::Soup ? 'soupIndex' : 'menuIndex';
    }

    private function itemNameKey(MenuItemType $type): string
    {
        return match ($type) {
            MenuItemType::Soup => 'soupName',
            MenuItemType::Main => 'foodName',
            MenuItemType::Pizza => 'pizzaName',
            MenuItemType::Grill => 'grillName',
        };
    }

    private function formatDecimal(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $formatted = number_format((float) $value, 3, ',', '');

        return rtrim(rtrim($formatted, '0'), ',');
    }
}
