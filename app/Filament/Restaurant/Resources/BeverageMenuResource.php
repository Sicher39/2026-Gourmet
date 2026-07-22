<?php

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\BeverageMenuResource\Pages;
use App\Models\RestaurantMenu;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BeverageMenuResource extends RestaurantMenuResource
{
    protected static ?string $modelLabel = 'nápojový lístek';

    protected static ?string $pluralModelLabel = 'nápojové lístky';

    protected static ?string $navigationLabel = 'Nápojové lístky';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?int $navigationSort = 11;

    public static function categoryKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Beverage;
    }

    public static function getEloquentQuery(): Builder
    {
        return RestaurantMenu::query()->where('type', 'beverage');
    }

    /**
     * @return array<string, string>
     */
    public static function menuTypeOptions(): array
    {
        return [
            'beverage' => 'Nápojový',
        ];
    }

    public static function getTypeDefault(): string
    {
        return 'beverage';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeverageMenus::route('/'),
            'create' => Pages\CreateBeverageMenu::route('/create'),
            'edit' => Pages\EditBeverageMenu::route('/{record}/edit'),
        ];
    }
}
