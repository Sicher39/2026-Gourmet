<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BreakfastMenuResource\Pages;

use App\Filament\Restaurant\Resources\BreakfastMenuResource;
use App\Models\RestaurantContactInformation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBreakfastMenus extends ListRecords
{
    protected static string $resource = BreakfastMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function getTabs(): array
    {
        $tabs = RestaurantContactInformation::query()
            ->whereIn('business_name', ['Gourmet Ponávka', 'Gourmet U Vaňkovky'])
            ->orderByRaw("CASE business_name WHEN 'Gourmet Ponávka' THEN 0 ELSE 1 END")
            ->get()
            ->mapWithKeys(fn (RestaurantContactInformation $restaurant): array => [
                'branch-'.$restaurant->getKey() => Tab::make($restaurant->business_name)
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query
                        ->where('restaurant_contact_information_id', $restaurant->getKey())),
            ])
            ->all();

        $tabs['all'] = Tab::make('Vše');

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
