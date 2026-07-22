<?php

namespace App\Filament\Restaurant\Resources\MenuCatalogItemResource\Pages;

use App\Filament\Restaurant\Resources\MenuCatalogItemResource;
use App\Models\MenuCatalogType;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMenuCatalogItems extends ListRecords
{
    protected static string $resource = MenuCatalogItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => static::getResource()::getUrl('create', [
                    'catalogType' => $this->activeTab !== 'all' ? $this->activeTab : null,
                ])),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [];

        $types = MenuCatalogType::query()
            ->where('is_active', true)
            ->where('menu_kind', static::getResource()::catalogKind()->value)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($types as $type) {
            $tabs[$type->slug] = Tab::make($type->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('menu_catalog_type_id', $type->id));
        }

        $tabs['all'] = Tab::make('Vše');

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return array_key_first($this->getTabs());
    }
}
