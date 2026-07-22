<?php

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\BeverageMenuCatalogItemResource\Pages;

class BeverageMenuCatalogItemResource extends MenuCatalogItemResource
{
    protected static ?string $modelLabel = 'komponenta nápoje';

    protected static ?string $pluralModelLabel = 'komponenty nápojů';

    protected static ?string $navigationLabel = 'Komponenty nápojů';

    protected static ?int $navigationSort = 31;

    protected static ?string $slug = 'beverage-menu-catalog-items';

    public static function catalogKind(): MenuCatalogKind
    {
        return MenuCatalogKind::Beverage;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeverageMenuCatalogItems::route('/'),
            'create' => Pages\CreateBeverageMenuCatalogItem::route('/create'),
            'edit' => Pages\EditBeverageMenuCatalogItem::route('/{record}/edit'),
        ];
    }
}
