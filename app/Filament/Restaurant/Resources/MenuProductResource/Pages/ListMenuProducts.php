<?php

namespace App\Filament\Restaurant\Resources\MenuProductResource\Pages;

use App\Filament\Restaurant\Resources\MenuProductResource;
use App\Models\MenuCategory;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMenuProducts extends ListRecords
{
    protected static string $resource = MenuProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => static::getResource()::getUrl('create', [
                    'category' => $this->activeTab !== 'all' ? $this->activeTab : null,
                ])),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [];

        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->where('menu_kind', static::getResource()::categoryKind()->value)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($categories as $category) {
            $tabs[$category->slug] = Tab::make($category->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('menu_category_id', $category->id));
        }

        $tabs['all'] = Tab::make('Vše');

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return array_key_first($this->getTabs());
    }
}
