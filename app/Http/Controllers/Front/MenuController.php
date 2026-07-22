<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Enums\MenuCatalogKind;
use App\Http\Controllers\Controller;
use App\Models\MenuProduct;
use App\Models\RestaurantMenu;
use App\Models\RestaurantMenuEntry;
use App\Models\RestaurantMenuSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function food(): Response
    {
        return $this->renderMenu('FoodMenu', '!=', 'beverage');
    }

    public function drink(): Response
    {
        return $this->renderMenu('DrinkMenu', '=', 'beverage');
    }

    private function renderMenu(string $component, string $typeOperator, string $typeValue = 'beverage'): Response
    {
        /** @var array<int, array<string, mixed>> $sections */
        $sections = [];

        if (Schema::hasTable('restaurant_menus')) {
            $menu = $this->findActiveMenu($typeOperator, $typeValue);

            if ($menu instanceof RestaurantMenu) {
                $sections = $this->buildSectionsPayload($menu);
            }
        }

        return Inertia::render($component, [
            'sections' => $sections,
        ]);
    }

    private function findActiveMenu(string $typeOperator, string $typeValue): ?RestaurantMenu
    {
        /** @var ?RestaurantMenu $menu */
        $menu = RestaurantMenu::query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->where('type', $typeOperator, $typeValue)
            ->where(function (Builder $q): void {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with([
                'sections' => function ($q): void {
                    /** @var Builder<RestaurantMenuSection> $q */
                    $q->orderBy('sort_order')->with('category');
                },
                'sections.entries' => function ($q): void {
                    /** @var Builder<RestaurantMenuEntry> $q */
                    $q->where('is_available', true)
                        ->orderBy('sort_order')
                        ->with([
                            'product' => function ($q): void {
                                /** @var Builder<MenuProduct> $q */
                                $q->with([
                                    'category',
                                    'servingUnit',
                                    'components' => function ($q): void {
                                        /** @var Builder<MenuProductComponent> $q */
                                        $q->orderBy('sort_order')->with([
                                            'catalogItems' => function ($q): void {
                                                /** @var Builder<MenuCatalogItem> $q */
                                                $q->orderBy('sort_order')->with(['allergens', 'unit']);
                                            },
                                        ]);
                                    },
                                    'parent.category',
                                    'parent.components' => function ($q): void {
                                        /** @var Builder<MenuProductComponent> $q */
                                        $q->orderBy('sort_order')->with([
                                            'catalogItems' => function ($q): void {
                                                /** @var Builder<MenuCatalogItem> $q */
                                                $q->orderBy('sort_order')->with(['allergens', 'unit']);
                                            },
                                        ]);
                                    },
                                ]);
                            },
                        ]);
                },
            ])
            ->first();

        return $menu;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSectionsPayload(RestaurantMenu $menu): array
    {
        return $menu->sections
            ->map(function (RestaurantMenuSection $section): array {
                return [
                    'id' => $section->id,
                    'title' => $section->category?->name ?? '',
                    'items' => $section->entries
                        ->map(function (RestaurantMenuEntry $entry): array {
                            return $this->buildItemPayload($entry);
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $section): bool => $section['items'] !== [])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildItemPayload(RestaurantMenuEntry $entry): array
    {
        $product = $entry->product;

        if (! $product instanceof MenuProduct) {
            return [
                'id' => $entry->id,
                'amount' => '',
                'unit' => '',
                'title' => '',
                'description' => '',
                'allergens' => '',
                'price' => $this->formatPrice($entry->price),
            ];
        }

        $product->loadMissing([
            'category',
            'servingUnit',
            'components.catalogItems.allergens',
            'components.catalogItems.unit',
            'parent.category',
            'parent.components.catalogItems.allergens',
            'parent.components.catalogItems.unit',
        ]);

        $displayProduct = $product->isVariant() && $product->parent instanceof MenuProduct
            ? $product->parent
            : $product;

        $description = $this->isBeverageProduct($displayProduct)
            ? $displayProduct->composeDescriptionFromComponents()
            : $displayProduct->description;

        $allergenSnapshot = $product->computeAllergenSnapshot();
        $allergenCodes = array_keys($allergenSnapshot);
        sort($allergenCodes, SORT_NATURAL);

        return [
            'id' => $entry->id,
            'amount' => $this->formatAmount($product->getRawServingAmount()),
            'unit' => $product->getServingUnitSymbol() ?? '',
            'title' => $displayProduct->composeNameFromComponents(),
            'description' => (string) ($description ?? ''),
            'allergens' => implode(', ', $allergenCodes),
            'price' => $this->formatPrice($entry->price),
        ];
    }

    private function isBeverageProduct(MenuProduct $product): bool
    {
        $product->loadMissing('category');

        return $product->category?->menu_kind === MenuCatalogKind::Beverage;
    }

    private function formatPrice(mixed $price): string
    {
        if ($price === null || $price === '') {
            return '';
        }

        $formattedPrice = number_format((float) $price, 2, '.', '');

        return str_ends_with($formattedPrice, '.00')
            ? substr($formattedPrice, 0, -3)
            : rtrim(rtrim($formattedPrice, '0'), '.');
    }

    private function formatAmount(mixed $amount): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $float = (float) $amount;

        if (floor($float) === $float) {
            return (string) (int) $float;
        }

        $formatted = number_format($float, 3, ',', '');
        $formatted = rtrim($formatted, '0');

        return rtrim($formatted, ',') ?: '0';
    }
}
