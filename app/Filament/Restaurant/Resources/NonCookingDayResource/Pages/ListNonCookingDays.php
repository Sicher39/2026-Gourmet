<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\NonCookingDayResource\Pages;

use App\Filament\Restaurant\Resources\NonCookingDayResource;
use App\Models\User;
use App\Services\Menu\CzechHolidayService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListNonCookingDays extends ListRecords
{
    protected static string $resource = NonCookingDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importHolidays')
                ->label('Nahrát státní svátky')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(function (): bool {
                    $user = auth()->user();

                    return $user instanceof User && ($user->hasRole('super_admin') || $user->can('Create:NonCookingDay'));
                })
                ->schema([
                    TextInput::make('year')->label('Rok')->integer()->minValue(2000)->maxValue(2100)->default((int) now()->format('Y'))->required(),
                ])
                ->action(function (array $data): void {
                    $created = app(CzechHolidayService::class)->importYear((int) $data['year'], auth()->id());
                    Notification::make()->success()->title("Přidáno nevařících dnů: {$created}")->send();
                }),
            CreateAction::make(),
        ];
    }
}
