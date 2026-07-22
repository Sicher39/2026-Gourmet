<?php

namespace App\Filament\Restaurant\Resources\MenuCategoryResource\Pages;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\MenuCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMenuCategories extends ListRecords
{
    protected static string $resource = MenuCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => MenuCategoryResource::getUrl('create', [
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
            MenuCatalogKind::Food->value => Tab::make('Jídelní lístek')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('menu_kind', MenuCatalogKind::Food->value)),
            MenuCatalogKind::Beverage->value => Tab::make('Nápojový lístek')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('menu_kind', MenuCatalogKind::Beverage->value)),
            'all' => Tab::make('Vše'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return array_key_first($this->getTabs());
    }
}
