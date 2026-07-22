<?php

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\RestaurantMenuResource\Pages;
use App\Models\MenuCategory;
use App\Models\MenuProduct;
use App\Models\RestaurantMenu;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use UnitEnum;

class RestaurantMenuResource extends Resource
{
    protected static ?string $model = RestaurantMenu::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $modelLabel = 'jídelní lístek';

    protected static ?string $pluralModelLabel = 'jídelní lístky';

    protected static ?string $navigationLabel = 'Jídelní lístky';

    protected static ?int $navigationSort = 10;

    public static function categoryKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Food;
    }

    public static function form(Schema $schema): Schema
    {
        $kind = static::categoryKind();
        $isBeverage = $kind === MenuCatalogKind::Beverage;
        $kindValue = $kind->value;

        return $schema->components([
            Section::make('Základní údaje')
                ->schema([
                    TextInput::make('name')
                        ->label('Název')
                        ->required()
                        ->maxLength(255),

                    Select::make('type')
                        ->label('Typ')
                        ->options(static::menuTypeOptions())
                        ->default(static::getTypeDefault())
                        ->required(),

                    Select::make('status')
                        ->label('Stav')
                        ->options(static::statusOptions())
                        ->default('draft')
                        ->required(),

                    DatePicker::make('valid_from')
                        ->label('Platný od')
                        ->native(false),

                    DatePicker::make('valid_to')
                        ->label('Platný do')
                        ->native(false),

                    Textarea::make('note')
                        ->label('Poznámka')
                        ->maxLength(65535),

                    Toggle::make('is_active')
                        ->label('Aktivní')
                        ->default(true),
                ])
                ->columns([
                    'default' => 1,
                    'xl' => 3,
                ])
                ->columnSpanFull(),

            Section::make('Sekce menu')
                ->description('Nejdříve vyberte sekci, například Hlavní jídla nebo Polévky. Uvnitř sekce pak vybíráte jen jídla z této sekce.')
                ->schema([
                    Repeater::make('sections')
                        ->relationship()
                        ->hiddenLabel()
                        ->itemLabel(fn (array $state): string|Htmlable => static::sectionItemLabel($state['menu_category_id'] ?? null))
                        ->extraAttributes(['class' => 'restaurant-menu-sections-repeater'])
                        ->schema([
                            Select::make('menu_category_id')
                                ->label('Sekce')
                                ->relationship(
                                    name: 'category',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query
                                        ->where('is_active', true)
                                        ->where('menu_kind', $kindValue)
                                        ->orderBy('sort_order')
                                        ->orderBy('name'),
                                )
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(),

                            Repeater::make('entries')
                                ->relationship()
                                ->label('Produkty v sekci')
                                ->itemLabel(fn (array $state): ?string => static::productItemLabel($state['menu_product_id'] ?? null))
                                ->schema([
                                    Select::make('menu_product_id')
                                        ->label('Produkt')
                                        ->options(fn (Get $get): array => $isBeverage
                                ? static::beverageProductOptions(
                                    $get('../../menu_category_id'),
                                    excludedProductIds: static::selectedSiblingProductIds($get),
                                )
                                : static::foodProductOptions(
                                    $get('../../menu_category_id'),
                                    excludedProductIds: static::selectedSiblingProductIds($get),
                                ))
                                        ->getOptionLabelUsing(fn (mixed $value): ?string => static::productItemLabel($value))
                                        ->getSearchResultsUsing(fn (string $search, Get $get): array => $isBeverage
                                ? static::beverageProductOptions(
                                    $get('../../menu_category_id'),
                                    $search,
                                    static::selectedSiblingProductIds($get),
                                )
                                : static::foodProductOptions(
                                    $get('../../menu_category_id'),
                                    $search,
                                    static::selectedSiblingProductIds($get),
                                ))
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->required()

                                        ->afterStateUpdated(function ($state, Set $set): void {
                                            if (! $state) {
                                                return;
                                            }

                                            $product = MenuProduct::query()->find($state);

                                            if (! $product instanceof MenuProduct) {
                                                return;
                                            }

                                            $set('price', $product->default_price);
                                        }),

                                    TextInput::make('price')
                                        ->label('Cena')
                                        ->numeric()
                                        ->step(0.01)
                                        ->required(),

                                    Toggle::make('is_available')
                                        ->label('Dostupné')
                                        ->default(true)
                                        ->columnSpanFull(),
                                ])
                                ->addActionLabel('Přidat produkt')
                                ->columns([
                                    'default' => 1,
                                    'lg' => 2,
                                ])
                                ->orderColumn('sort_order')
                                ->collapsible(),
                        ])
                        ->addActionLabel('Přidat sekci')
                        ->columns(1)
                        ->orderColumn('sort_order')
                        ->collapsible(),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::menuTypeLabel($state) ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'gray',
                        'daily' => 'success',
                        'weekly' => 'info',
                        'special' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::statusLabel($state) ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('valid_from')
                    ->label('Od')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('valid_to')
                    ->label('Do')
                    ->date('d.m.Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('entries_count')
                    ->label('Položek')
                    ->state(fn (RestaurantMenu $record): int => $record->entries()->count()),
                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ')
                    ->options(static::menuTypeOptions()),
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options(static::statusOptions()),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function sectionItemLabel(mixed $menuCategoryId): string|Htmlable
    {
        if (! $menuCategoryId) {
            return new HtmlString('<span class="restaurant-menu-section-label"><span class="restaurant-menu-section-label__bullet restaurant-menu-section-label--tone-0"></span>'.e('Nová sekce').'</span>');
        }

        $categoryName = MenuCategory::query()->whereKey($menuCategoryId)->value('name');
        $label = is_string($categoryName) ? $categoryName : 'Nová sekce';
        $tone = is_numeric($menuCategoryId) ? ((int) $menuCategoryId % 6) : 0;

        return new HtmlString('<span class="restaurant-menu-section-label"><span class="restaurant-menu-section-label__bullet restaurant-menu-section-label--tone-'.$tone.'"></span>'.e($label).'</span>');
    }

    public static function productItemLabel(mixed $menuProductId): ?string
    {
        if (! $menuProductId) {
            return null;
        }

        $query = MenuProduct::query()->whereKey($menuProductId);

        if (static::categoryKind() === MenuCatalogKind::Beverage) {
            $query->with(['servingUnit', 'parent.components.catalogItems']);
        } else {
            $query->with(['servingUnit', 'components.componentItems.catalogItem.unit', 'components.catalogItems.unit']);
        }

        $product = $query->first();

        if (! $product instanceof MenuProduct) {
            return null;
        }

        return $product->getDisplayLabel();
    }

    /**
     * @return array<int, string>
     */
    public static function productOptions(mixed $menuCategoryId, ?string $search = null, array $excludedProductIds = []): array
    {
        if (static::categoryKind() === MenuCatalogKind::Beverage) {
            return static::beverageProductOptions($menuCategoryId, $search, $excludedProductIds);
        }

        return static::foodProductOptions($menuCategoryId, $search, $excludedProductIds);
    }

    /**
     * @return array<int, string>
     */
    protected static function foodProductOptions(mixed $menuCategoryId, ?string $search = null, array $excludedProductIds = []): array
    {
        return MenuProduct::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->whereHas('category', fn (Builder $q) => $q->where('menu_kind', static::categoryKind()->value))
            ->when($menuCategoryId, fn (Builder $query, mixed $menuCategoryId) => $query->where('menu_category_id', $menuCategoryId))
            ->when($search, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($excludedProductIds !== [], fn (Builder $query) => $query->whereNotIn('id', $excludedProductIds))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['servingUnit', 'components.componentItems.catalogItem.unit', 'components.catalogItems.unit'])
            ->get()
            ->mapWithKeys(fn (MenuProduct $product): array => [
                $product->id => $product->getDisplayLabel(),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected static function beverageProductOptions(mixed $menuCategoryId, ?string $search = null, array $excludedProductIds = []): array
    {
        return MenuProduct::query()
            ->where('is_active', true)
            ->whereNotNull('parent_id')
            ->whereHas('parent.category', fn (Builder $q) => $q->where('menu_kind', MenuCatalogKind::Beverage->value))
            ->when($menuCategoryId, fn (Builder $query, mixed $menuCategoryId) => $query
                ->whereHas('parent', fn (Builder $q) => $q->where('menu_category_id', $menuCategoryId)))
            ->when($search, fn (Builder $query, string $search) => $query
                ->where(fn (Builder $q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhereHas('parent', fn (Builder $pq) => $pq->where('name', 'like', "%{$search}%"))))
            ->when($excludedProductIds !== [], fn (Builder $query) => $query->whereNotIn('id', $excludedProductIds))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['servingUnit', 'parent.components.catalogItems'])
            ->get()
            ->mapWithKeys(fn (MenuProduct $product): array => [
                $product->id => $product->getDisplayLabel(),
            ])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public static function selectedSiblingProductIds(Get $get): array
    {
        $entries = $get('../../entries');
        $currentProductId = $get('menu_product_id');

        if (! is_array($entries)) {
            return [];
        }

        return collect($entries)
            ->pluck('menu_product_id')
            ->filter(fn (mixed $productId): bool => filled($productId) && (string) $productId !== (string) $currentProductId)
            ->map(fn (mixed $productId): int => (int) $productId)
            ->values()
            ->all();
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<RestaurantMenu> $query */
        $query = parent::getEloquentQuery();

        return $query->where('type', '!=', 'beverage');
    }

    public static function getTypeDefault(): string
    {
        return 'fixed';
    }

    /**
     * @return array<string, string>
     */
    public static function menuTypeOptions(): array
    {
        return [
            'fixed' => 'Pevný',
            'daily' => 'Denní',
            'weekly' => 'Týdenní',
            'special' => 'Speciální',
        ];
    }

    public static function menuTypeLabel(mixed $value): ?string
    {
        return is_string($value) ? (static::menuTypeOptions()[$value] ?? null) : null;
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'draft' => 'Koncept',
            'published' => 'Publikováno',
            'archived' => 'Archivovaný',
        ];
    }

    public static function statusLabel(mixed $value): ?string
    {
        return is_string($value) ? (static::statusOptions()[$value] ?? null) : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurantMenus::route('/'),
            'create' => Pages\CreateRestaurantMenu::route('/create'),
            'edit' => Pages\EditRestaurantMenu::route('/{record}/edit'),
        ];
    }
}
