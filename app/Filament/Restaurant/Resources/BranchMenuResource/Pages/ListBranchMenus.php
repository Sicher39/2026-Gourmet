<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BranchMenuResource\Pages;

use App\Filament\Restaurant\Resources\BranchMenuResource;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBranchMenus extends ListRecords
{
    protected static string $resource = BranchMenuResource::class;

    public function getTabs(): array
    {
        $currentWeekStart = CarbonImmutable::today()
            ->startOfWeek(CarbonInterface::MONDAY)
            ->toDateString();

        return [
            'current' => Tab::make('Aktuální')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereDate('week_start', $currentWeekStart)),
            'upcoming' => Tab::make('Následující')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereDate('week_start', '>', $currentWeekStart)),
            'all' => Tab::make('Vše'),
        ];
    }
}
