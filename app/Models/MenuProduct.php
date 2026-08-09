<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuProduct extends Model
{
    use SoftDeletes;

    protected $table = 'menu_products';

    protected $fillable = [
        'name',
        'description',
        'default_price',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function components(): HasMany
    {
        return $this->hasMany(MenuProductComponent::class, 'menu_product_id')->orderBy('sort_order');
    }

    public function menuEntries(): HasMany
    {
        return $this->hasMany(RestaurantMenuEntry::class, 'menu_product_id');
    }

    public function getServingAmount(): ?string
    {
        $raw = $this->getFirstCatalogItemAmountRaw();

        return $raw === null ? null : $this->formatAmount($raw);
    }

    public function getServingUnitSymbol(): ?string
    {
        $firstItem = $this->getFirstComponentCatalogItem();

        return $firstItem instanceof MenuCatalogItem && $firstItem->unit instanceof MenuUnit
            ? (string) ($firstItem->unit->symbol ?? '')
            : null;
    }

    public function getRawServingAmount(): ?string
    {
        return $this->getFirstCatalogItemAmountRaw();
    }

    public function getDisplayMeasure(): string
    {
        $amount = $this->getServingAmount();
        $unit = $this->getServingUnitSymbol();

        if ($amount === null || $amount === '') {
            return $unit ?? '';
        }

        return $unit === null || $unit === '' ? $amount : $amount.' '.$unit;
    }

    public function getDisplayLabel(): string
    {
        $measure = $this->getDisplayMeasure();
        $name = $this->composeNameFromComponents();

        return $measure === '' ? $name : $measure.' '.$name;
    }

    public function composeNameFromComponents(): string
    {
        $this->loadMissing('components.catalogItems');
        $names = [];

        foreach ($this->components as $component) {
            foreach ($component->catalogItems as $catalogItem) {
                if ($catalogItem instanceof MenuCatalogItem) {
                    $names[] = $catalogItem->name;
                }
            }
        }

        return $names === [] ? (string) ($this->name ?? '') : implode(', ', $names);
    }

    public function composeDescriptionFromComponents(): ?string
    {
        $this->loadMissing('components.catalogItems');
        $descriptions = [];

        foreach ($this->components as $component) {
            foreach ($component->catalogItems as $catalogItem) {
                if ($catalogItem instanceof MenuCatalogItem && is_string($catalogItem->description)) {
                    $description = trim($catalogItem->description);

                    if ($description !== '') {
                        $descriptions[] = $description;
                    }
                }
            }
        }

        return $descriptions === [] ? null : implode("\n", array_values(array_unique($descriptions)));
    }

    /** @return array<int, string> */
    public function computeAllergenSnapshot(): array
    {
        $this->loadMissing('components.catalogItems.allergens');
        $allergenMap = [];

        foreach ($this->components as $component) {
            foreach ($component->catalogItems as $catalogItem) {
                foreach ($catalogItem->allergens as $allergen) {
                    $allergenMap[$allergen->id] = $allergen->code;
                }
            }
        }

        return $allergenMap;
    }

    private function formatAmount(string $amount): string
    {
        $float = (float) $amount;

        if (floor($float) === $float) {
            return (string) (int) $float;
        }

        return rtrim(rtrim(number_format($float, 3, ',', ''), '0'), ',') ?: '0';
    }

    private function getFirstCatalogItemAmountRaw(): ?string
    {
        $firstItem = $this->getFirstComponentCatalogItem();

        return $firstItem instanceof MenuCatalogItem && $firstItem->amount !== null ? (string) $firstItem->amount : null;
    }

    private function getFirstComponentCatalogItem(): ?MenuCatalogItem
    {
        $this->loadMissing('components.componentItems.catalogItem.unit');
        $component = $this->components->first();
        $componentItem = $component instanceof MenuProductComponent ? $component->componentItems->first() : null;

        return $componentItem instanceof MenuProductComponentItem && $componentItem->catalogItem instanceof MenuCatalogItem
            ? $componentItem->catalogItem
            : null;
    }
}
