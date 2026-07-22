<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuProduct extends Model
{
    use SoftDeletes;

    protected $table = 'menu_products';

    protected $fillable = [
        'parent_id',
        'menu_category_id',
        'name',
        'description',
        'default_price',
        'serving_amount',
        'serving_unit_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'is_active' => 'boolean',
            'default_price' => 'decimal:2',
            'serving_amount' => 'decimal:3',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MenuProduct $product): void {
            if ($product->parent_id !== null && $product->menu_category_id === null) {
                $parent = $product->parent()->first();

                if ($parent instanceof MenuProduct) {
                    $product->menu_category_id = $parent->menu_category_id;
                }
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(MenuProductComponent::class, 'menu_product_id')->orderBy('sort_order');
    }

    public function menuEntries(): HasMany
    {
        return $this->hasMany(RestaurantMenuEntry::class, 'menu_product_id');
    }

    public function servingUnit(): BelongsTo
    {
        return $this->belongsTo(MenuUnit::class, 'serving_unit_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuProduct::class, 'parent_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MenuProduct::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function isVariant(): bool
    {
        return $this->parent_id !== null;
    }

    public function getServingAmount(): ?string
    {
        if ($this->serving_amount !== null) {
            return $this->formatAmount($this->serving_amount);
        }

        return $this->getFirstCatalogItemAmount();
    }

    public function getServingUnitSymbol(): ?string
    {
        if ($this->serving_unit_id !== null) {
            $this->loadMissing('servingUnit');

            return $this->servingUnit instanceof MenuUnit
                ? (string) ($this->servingUnit->symbol ?? '')
                : null;
        }

        return $this->getFirstCatalogItemUnitSymbol();
    }

    public function getRawServingAmount(): ?string
    {
        if ($this->serving_amount !== null) {
            return (string) $this->serving_amount;
        }

        return $this->getFirstCatalogItemAmountRaw();
    }

    public function getDisplayMeasure(): string
    {
        $amount = $this->getServingAmount();
        $unit = $this->getServingUnitSymbol();

        if ($amount === null || $amount === '') {
            return $unit !== null && $unit !== '' ? $unit : '';
        }

        if ($unit === null || $unit === '') {
            return $amount;
        }

        return $amount.' '.$unit;
    }

    public function getDisplayLabel(): string
    {
        if ($this->isVariant()) {
            $measure = $this->getDisplayMeasure();
            $parent = $this->parent;
            $parentName = $parent instanceof MenuProduct
                ? $parent->composeNameFromComponents()
                : (string) ($this->name ?? '');

            if ($measure === '') {
                return $parentName !== '' ? $parentName : (string) ($this->name ?? '');
            }

            return $measure.' '.($parentName !== '' ? $parentName : (string) ($this->name ?? ''));
        }

        $measure = $this->getDisplayMeasure();
        $name = $this->composeNameFromComponents();

        if ($measure === '') {
            return $name;
        }

        return $measure.' '.$name;
    }

    private function formatAmount(string $amount): ?string
    {
        $float = (float) $amount;

        if (floor($float) === $float) {
            return (string) (int) $float;
        }

        $formatted = number_format($float, 3, ',', '');
        $formatted = rtrim($formatted, '0');

        return rtrim($formatted, ',') ?: '0';
    }

    private function getFirstCatalogItemAmount(): ?string
    {
        $raw = $this->getFirstCatalogItemAmountRaw();

        if ($raw === null) {
            return null;
        }

        return $this->formatAmount($raw);
    }

    private function getFirstCatalogItemAmountRaw(): ?string
    {
        $firstItem = $this->getFirstComponentCatalogItem();

        if (! $firstItem instanceof MenuCatalogItem || $firstItem->amount === null) {
            return null;
        }

        return (string) $firstItem->amount;
    }

    private function getFirstCatalogItemUnitSymbol(): ?string
    {
        $firstItem = $this->getFirstComponentCatalogItem();

        if (! $firstItem instanceof MenuCatalogItem) {
            return null;
        }

        return $firstItem->unit instanceof MenuUnit
            ? (string) ($firstItem->unit->symbol ?? '')
            : null;
    }

    private function getFirstComponentCatalogItem(): ?MenuCatalogItem
    {
        $this->loadMissing('components.componentItems.catalogItem.unit');

        $firstComponent = $this->components->first();

        if (! $firstComponent instanceof MenuProductComponent) {
            return null;
        }

        $firstComponentItem = $firstComponent->componentItems->first();

        if (! $firstComponentItem instanceof MenuProductComponentItem) {
            return null;
        }

        return $firstComponentItem->catalogItem instanceof MenuCatalogItem
            ? $firstComponentItem->catalogItem
            : null;
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

        if ($names === []) {
            return (string) ($this->name ?? '');
        }

        return implode(', ', $names);
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

        if ($descriptions === []) {
            return null;
        }

        return implode("\n", array_values(array_unique($descriptions)));
    }

    /**
     * @return array<string, string>
     */
    public function computeAllergenSnapshot(): array
    {
        if ($this->isVariant()) {
            $this->loadMissing('components.catalogItems.allergens');

            if ($this->components->isEmpty()) {
                $parent = $this->parent;

                if ($parent instanceof MenuProduct) {
                    return $parent->computeAllergenSnapshot();
                }
            }
        }

        $allergenMap = [];

        $this->loadMissing('components.catalogItems.allergens');

        foreach ($this->components as $component) {
            foreach ($component->catalogItems as $catalogItem) {
                if ($catalogItem instanceof MenuCatalogItem) {
                    foreach ($catalogItem->allergens as $allergen) {
                        $allergenMap[$allergen->code] = $allergen->name;
                    }
                }
            }
        }

        return $allergenMap;
    }
}
