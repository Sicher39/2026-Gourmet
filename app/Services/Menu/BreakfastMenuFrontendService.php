<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Models\BreakfastMenu;
use App\Models\BreakfastMenuItem;
use App\Models\BreakfastMenuItemVariant;
use App\Models\RestaurantContactInformation;

class BreakfastMenuFrontendService
{
    /** @return ?array{items: array<int, array{foodName: string, allergens: string, price: string, enabled: bool, menuVariants: array<int, array{name: string, allergens: string}>}>} */
    public function forRestaurant(RestaurantContactInformation $restaurant): ?array
    {
        $menu = BreakfastMenu::query()
            ->where('restaurant_contact_information_id', $restaurant->getKey())
            ->where('is_active', true)
            ->whereDate('valid_from', '<=', today())
            ->where(fn ($query) => $query->whereNull('valid_to')->orWhereDate('valid_to', '>=', today()))
            ->with(['items' => fn ($query) => $query
                ->where('is_available', true)
                ->with('variants')])
            ->latest('valid_from')
            ->first();

        if (! $menu instanceof BreakfastMenu) {
            return null;
        }

        return [
            'items' => $menu->items
                ->map(fn (BreakfastMenuItem $item): array => [
                    'foodName' => $item->name_snapshot,
                    'allergens' => $this->allergens($item->allergens_snapshot),
                    'price' => $this->price($item->price),
                    'enabled' => true,
                    'menuVariants' => $item->variants
                        ->map(fn (BreakfastMenuItemVariant $variant): array => [
                            'name' => $variant->name,
                            'allergens' => $this->allergens($variant->allergens_snapshot),
                        ])
                        ->all(),
                ])
                ->all(),
        ];
    }

    /** @param ?array<int, string> $allergens */
    private function allergens(?array $allergens): string
    {
        return collect($allergens)->filter()->implode(', ');
    }

    private function price(mixed $price): string
    {
        return rtrim(rtrim(number_format((float) $price, 2, ',', ''), '0'), ',');
    }
}
