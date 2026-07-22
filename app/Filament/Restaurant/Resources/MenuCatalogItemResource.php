<?php

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\MenuCatalogItemResource\Pages;
use App\Models\MenuAllergen;
use App\Models\MenuCatalogItem;
use App\Models\MenuCatalogType;
use App\Models\MenuUnit;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MenuCatalogItemResource extends Resource
{
    protected static ?string $model = MenuCatalogItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $modelLabel = 'komponenta jídla';

    protected static ?string $pluralModelLabel = 'komponenty jídel';

    protected static ?string $navigationLabel = 'Komponenty jídel';

    protected static ?int $navigationSort = 30;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('catalogType', fn (Builder $q) => $q->where('menu_kind', static::catalogKind()->value));
    }

    public static function catalogKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Food;
    }

    public static function form(Schema $schema): Schema
    {
        $isBeverage = static::catalogKind() === MenuCatalogKind::Beverage;
        $showMeasure = static::shouldShowCatalogItemMeasureFields();

        return $schema->components([
            Section::make('Komponenta jídla')->schema([
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
                    ->hidden(fn (): bool => static::shouldHideCatalogTypeField())
                    ->dehydrated(),

                TextInput::make('name')
                    ->label('Název')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Poznámka')
                    ->maxLength(65535)
                    ->visible($isBeverage)
                    ->columnSpanFull(),

                TextInput::make('amount')
                    ->label('Množství')
                    ->numeric()
                    ->step(0.001)
                    ->visible($showMeasure),

                Select::make('menu_unit_id')
                    ->label('Jednotka')
                    ->options(fn (): array => static::unitOptions())
                    ->searchable()
                    ->visible($showMeasure),

                Select::make('allergens')
                    ->label('Alergeny')
                    ->relationship(
                        name: 'allergens',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('sort_order'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (MenuAllergen $record): string => static::allergenLabel($record))
                    ->multiple()
                    ->searchable()
                    ->preload(),

                TextInput::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Aktivní')
                    ->default(true),
            ])->columns(2),
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
                TextColumn::make('catalogType.name')
                    ->label('Skupina')
                    ->badge()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Množství')
                    ->numeric(3)
                    ->sortable()
                    ->visible(fn (): bool => static::shouldShowCatalogItemMeasureColumns()),
                TextColumn::make('unit.symbol')
                    ->label('Jednotka')
                    ->sortable()
                    ->visible(fn (): bool => static::shouldShowCatalogItemMeasureColumns()),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('allergens_count')
                    ->label('Alergenů')
                    ->state(fn (MenuCatalogItem $record): int => $record->allergens()->count()),
                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
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

    public static function allergenLabel(MenuAllergen $allergen): string
    {
        return $allergen->code.' '.$allergen->name;
    }

    public static function shouldHideCatalogTypeField(): bool
    {
        $slug = static::getSlug();

        return request()->routeIs("filament.*.resources.{$slug}.create")
            && static::catalogTypeIdFromRequest() !== null;
    }

    public static function catalogTypeIdFromRequest(): ?int
    {
        $slug = request()->query('catalogType');

        if (! is_string($slug) || $slug === '') {
            return null;
        }

        $id = MenuCatalogType::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('menu_kind', static::catalogKind()->value)
            ->value('id');

        return is_numeric($id) ? (int) $id : null;
    }

    public static function shouldShowCatalogItemMeasureFields(): bool
    {
        return static::catalogKind() === MenuCatalogKind::Food;
    }

    public static function shouldShowCatalogItemMeasureColumns(): bool
    {
        return static::catalogKind() === MenuCatalogKind::Food;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuCatalogItems::route('/'),
            'create' => Pages\CreateMenuCatalogItem::route('/create'),
            'edit' => Pages\EditMenuCatalogItem::route('/{record}/edit'),
        ];
    }
}
