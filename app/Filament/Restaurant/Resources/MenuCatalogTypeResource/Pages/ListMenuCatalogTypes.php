<?php

namespace App\Filament\Restaurant\Resources\MenuCatalogTypeResource\Pages;

use App\Filament\Restaurant\Resources\MenuCatalogTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMenuCatalogTypes extends ListRecords
{
    protected static string $resource = MenuCatalogTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => MenuCatalogTypeResource::getUrl('create', [
                    'kind' => $this->activeTab !== 'all' ? $this->activeTab : null,
                ])),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'food' => Tab::make('Jídelní lístek')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('menu_kind', 'food')),
            'beverage' => Tab::make('Nápojový lístek')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('menu_kind', 'beverage')),
            'all' => Tab::make('Vše'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'food';
    }
}
