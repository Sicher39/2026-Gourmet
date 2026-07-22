<?php

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\BeverageRestaurantMenuResource\Pages;
use App\Models\RestaurantMenu;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BeverageRestaurantMenuResource extends RestaurantMenuResource
{
    protected static ?string $modelLabel = 'nápojový lístek';

    protected static ?string $pluralModelLabel = 'nápojové lístky';

    protected static ?string $navigationLabel = 'Nápojové lístky';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'beverage-restaurant-menus';

    public static function categoryKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Beverage;
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<RestaurantMenu> $query */
        $query = static::getModel()::query();

        return $query->where('type', '=', 'beverage');
    }

    public static function getTypeDefault(): string
    {
        return 'beverage';
    }

    /**
     * @return array<string, string>
     */
    public static function menuTypeOptions(): array
    {
        return [
            'beverage' => 'Nápojový',
            'daily' => 'Denní',
            'weekly' => 'Týdenní',
            'special' => 'Speciální',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeverageRestaurantMenus::route('/'),
            'create' => Pages\CreateBeverageRestaurantMenu::route('/create'),
            'edit' => Pages\EditBeverageRestaurantMenu::route('/{record}/edit'),
        ];
    }
}
