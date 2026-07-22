<?php

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\BeverageMenuProductResource\Pages;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BeverageMenuProductResource extends MenuProductResource
{
    protected static ?string $modelLabel = 'nápoj';

    protected static ?string $pluralModelLabel = 'nápoje';

    protected static ?string $navigationLabel = 'Nápoje';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?int $navigationSort = 21;

    protected static ?string $slug = 'beverage-menu-products';

    public static function catalogKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Beverage;
    }

    public static function categoryKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Beverage;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('parent_id')
            ->with('variants');
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

                Toggle::make('is_active')
                    ->label('Aktivní')
                    ->default(true),
            ])->columns(1)
                ->columnSpanFull(),

            Section::make('Složení nápoje')
                ->description('Vyberte skupinu komponent a k ní jednu nebo více komponent. Název nápoje se po uložení složí automaticky.')
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

            Section::make('Varianty')
                ->description('Definujte varianty nápoje (např. 0,5 l, 0,3 l). Každá varianta má vlastní cenu a míru.')
                ->schema([
                    Repeater::make('variants')
                        ->relationship()
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('serving_amount')
                                ->label('Obsah')
                                ->numeric()
                                ->step(0.001)
                                ->required(),

                            Select::make('serving_unit_id')
                                ->label('Jednotka')
                                ->options(fn (): array => static::unitOptions())
                                ->searchable()
                                ->required(),

                            TextInput::make('default_price')
                                ->label('Cena')
                                ->numeric()
                                ->step(0.01)
                                ->required(),

                            Toggle::make('is_active')
                                ->label('Aktivní')
                                ->default(true),

                            TextInput::make('sort_order')
                                ->label('Pořadí')
                                ->numeric()
                                ->step(1)
                                ->default(0),
                        ])
                        ->addActionLabel('Přidat variantu')
                        ->minItems(1)
                        ->columns(1)
                        ->orderColumn('sort_order')
                        ->collapsible(),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeverageMenuProducts::route('/'),
            'create' => Pages\CreateBeverageMenuProduct::route('/create'),
            'edit' => Pages\EditBeverageMenuProduct::route('/{record}/edit'),
        ];
    }
}
