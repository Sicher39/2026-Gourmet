<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\BreakfastMenuResource\Pages;
use App\Models\BreakfastCatalogItem;
use App\Models\BreakfastMenu;
use App\Models\MenuAllergen;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use UnitEnum;

class BreakfastMenuResource extends Resource
{
    protected static ?string $model = BreakfastMenu::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sun';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $navigationLabel = 'Snídaňové menu';

    protected static ?string $modelLabel = 'snídaňové menu';

    protected static ?string $pluralModelLabel = 'snídaňová menu';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Snídaňové menu')->schema([
                Select::make('restaurant_contact_information_id')
                    ->label('Pobočka')
                    ->relationship('restaurant', 'business_name', fn (Builder $query) => $query->orderBy('business_name'))
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('valid_from')->label('Platnost od')->native(false)->required(),
                DatePicker::make('valid_to')->label('Platnost do')->native(false)->afterOrEqual('valid_from'),
                Toggle::make('is_active')->label('Aktivní')->default(true),
            ])->columns(2)->columnSpanFull(),
            Section::make('Položky')->schema([
                Repeater::make('items')
                    ->label('Snídaňová jídla')
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->addActionLabel('Přidat snídaňové jídlo')
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(fn (array $state): HtmlString => new HtmlString(e((string) ($state['name_snapshot'] ?? 'Snídaňové jídlo'))))
                    ->schema([
                        Select::make('breakfast_catalog_item_id')
                            ->label('Jídlo z katalogu')
                            ->relationship('catalogItem', 'name', function (Builder $query, Get $get): Builder {
                                $currentCatalogItemId = $get('breakfast_catalog_item_id');
                                $selectedCatalogItemIds = collect($get('../') ?? [])
                                    ->pluck('breakfast_catalog_item_id')
                                    ->filter()
                                    ->reject(fn (mixed $catalogItemId): bool => (string) $catalogItemId === (string) $currentCatalogItemId)
                                    ->unique()
                                    ->values()
                                    ->all();

                                return $query
                                    ->where('is_active', true)
                                    ->when(
                                        $selectedCatalogItemIds !== [],
                                        fn (Builder $query): Builder => $query->whereNotIn('breakfast_catalog_items.id', $selectedCatalogItemIds),
                                    )
                                    ->orderBy('sort_order')
                                    ->orderBy('name');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Get $get, Set $set): void {
                                $catalogItem = BreakfastCatalogItem::query()->with('allergens')->find($state);

                                if (! $catalogItem instanceof BreakfastCatalogItem) {
                                    $set('name_snapshot', null);
                                    $set('allergens_snapshot', []);
                                    $set('allergens_display', 'Bez alergenů');
                                    $set('price', null);

                                    return;
                                }

                                $allergenCodes = $catalogItem->allergens->pluck('code')->sort()->values()->all();

                                $set('name_snapshot', $catalogItem->name);
                                $set('allergens_snapshot', $allergenCodes);
                                $set('allergens_display', collect($allergenCodes)->implode(', ') ?: 'Bez alergenů');
                                $set('price', $catalogItem->default_price);
                            })
                            ->required(),
                        Hidden::make('name_snapshot')->required(),
                        Hidden::make('allergens_snapshot'),
                        TextInput::make('allergens_display')
                            ->label('Alergeny')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Get $get, Set $set): void {
                                $set('allergens_display', collect($get('allergens_snapshot') ?? [])
                                    ->filter()
                                    ->implode(', ') ?: 'Bez alergenů');
                            }),
                        TextInput::make('price')->label('Cena')->numeric()->step(0.01)->minValue(0)->required(),
                        Toggle::make('is_available')->label('V nabídce')->default(true),
                        Repeater::make('variants')
                            ->label('Varianty')
                            ->relationship()
                            ->defaultItems(0)
                            ->orderColumn('sort_order')
                            ->addActionLabel('Přidat variantu')
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => (string) ($state['name'] ?? 'Varianta'))
                            ->schema([
                                TextInput::make('name')->label('Název varianty')->required()->maxLength(255),
                                Select::make('allergens_snapshot')
                                    ->label('Alergeny varianty')
                                    ->options(fn (): array => MenuAllergen::query()
                                        ->where('is_active', true)
                                        ->orderBy('sort_order')
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn (MenuAllergen $allergen): array => [
                                            $allergen->code => $allergen->code.' '.$allergen->name,
                                        ])
                                        ->all())
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                            ])->columns(2)->columnSpanFull(),
                    ])->columns(1)->columnSpanFull(),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('valid_from', 'desc')
            ->columns([
                TextColumn::make('restaurant.business_name')->label('Pobočka')->searchable()->sortable(),
                TextColumn::make('valid_from')->label('Platnost od')->date('d.m.Y')->sortable(),
                TextColumn::make('valid_to')->label('Platnost do')->date('d.m.Y')->placeholder('Bez omezení')->sortable(),
                TextColumn::make('items_count')->label('Jídel')->counts('items'),
                IconColumn::make('is_active')->label('Aktivní')->boolean()->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    /** @return array<int, string> */
    public static function allergenCodes(?string $allergens): array
    {
        return collect(explode(',', (string) $allergens))
            ->map(fn (string $allergen): string => trim($allergen))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBreakfastMenus::route('/'),
            'create' => Pages\CreateBreakfastMenu::route('/create'),
            'edit' => Pages\EditBreakfastMenu::route('/{record}/edit'),
        ];
    }
}
