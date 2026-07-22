<?php

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\MenuProductResource\Pages;
use App\Models\MenuCategory;
use App\Models\MenuProduct;
use App\Models\MenuUnit;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MenuProductResource extends Resource
{
    protected static ?string $model = MenuProduct::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $modelLabel = 'jídlo';

    protected static ?string $pluralModelLabel = 'jídla';

    protected static ?string $navigationLabel = 'Jídla';

    protected static ?int $navigationSort = 20;

    public static function catalogKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Food;
    }

    public static function categoryKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Food;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('parent_id')
            ->whereHas('category', fn (Builder $q) => $q->where('menu_kind', static::categoryKind()->value))
            ->with(['servingUnit', 'components.catalogItems.unit']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Základní údaje')->schema([
                Select::make('menu_category_id')
                    ->label('Sekce lístku')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('is_active', true)
                            ->where('menu_kind', static::categoryKind()->value)
                            ->orderBy('sort_order')
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->hidden(fn (): bool => static::shouldHideCategoryField())
                    ->dehydrated(),

                TextInput::make('default_price')
                    ->label('Výchozí cena')
                    ->numeric()
                    ->step(0.01),

                Textarea::make('description')
                    ->label('Poznámka')
                    ->rows(3)
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Aktivní')
                    ->default(true),

                TextInput::make('serving_amount')
                    ->label('Obsah')
                    ->numeric()
                    ->step(0.001)
                    ->visible(fn (): bool => static::catalogKind() === MenuCatalogKind::Beverage)
                    ->required(fn (): bool => static::catalogKind() === MenuCatalogKind::Beverage),

                Select::make('serving_unit_id')
                    ->label('Jednotka')
                    ->options(fn (): array => static::unitOptions())
                    ->searchable()
                    ->visible(fn (): bool => static::catalogKind() === MenuCatalogKind::Beverage)
                    ->required(fn (): bool => static::catalogKind() === MenuCatalogKind::Beverage),
            ])->columns(1)
                ->columnSpanFull(),

            Section::make('Složení jídla')
                ->description('Vyberte skupinu komponent a k ní jednu nebo více komponent. Název jídla se po uložení složí automaticky.')
                ->schema([
                    Repeater::make('components')
                        ->relationship()
                        ->hiddenLabel()
                        ->schema([
                            Select::make('menu_catalog_type_id')
                                ->label('Skupina komponent')
                                ->relationship(
                                    name: 'catalogType',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query
                                        ->where('is_active', true)
                                        ->where('menu_kind', static::catalogKind()->value)
                                        ->orderBy('sort_order')
                                        ->orderBy('name'),
                                )
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('catalogItems', []);
                                })
                                ->required(),

                            Select::make('catalogItems')
                                ->label('Komponenty')
                                ->relationship(
                                    name: 'catalogItems',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: function (Builder $query, Get $get): Builder {
                                        $typeId = $get('menu_catalog_type_id');

                                        return $query
                                            ->where('is_active', true)
                                            ->whereHas('catalogType', fn (Builder $query) => $query->where('menu_kind', static::catalogKind()->value))
                                            ->when($typeId, fn (Builder $query, $typeId) => $query->where('menu_catalog_type_id', $typeId))
                                            ->orderBy('sort_order')
                                            ->orderBy('name');
                                    },
                                )
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->addActionLabel('Přidat skupinu komponent')
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
                TextColumn::make('category.name')
                    ->label('Sekce')
                    ->badge()
                    ->sortable(),
                TextColumn::make('measure')
                    ->label('Míra')
                    ->state(fn (MenuProduct $record): string => $record->getDisplayMeasure())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('serving_amount', $direction);
                    }),
                TextColumn::make('default_price')
                    ->label('Cena')
                    ->money('CZK')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('components_count')
                    ->label('Komponent')
                    ->state(fn (MenuProduct $record): int => $record->components()->count()),
                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function shouldHideCategoryField(): bool
    {
        $slug = static::getSlug();

        return request()->routeIs("filament.*.resources.{$slug}.create")
            && static::categoryIdFromRequest() !== null;
    }

    public static function categoryIdFromRequest(): ?int
    {
        $slug = request()->query('category');

        if (! is_string($slug) || $slug === '') {
            return null;
        }

        $id = MenuCategory::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('menu_kind', static::categoryKind()->value)
            ->value('id');

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * @return array<int, string>
     */
    public static function unitOptions(): array
    {
        return MenuUnit::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (MenuUnit $unit): array => [
                $unit->id => $unit->name.' ('.$unit->symbol.')',
            ])
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuProducts::route('/'),
            'create' => Pages\CreateMenuProduct::route('/create'),
            'edit' => Pages\EditMenuProduct::route('/{record}/edit'),
        ];
    }
}
