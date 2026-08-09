<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\PlannedMenuResource\Pages;

use App\Filament\Restaurant\Resources\PlannedMenuResource;
use App\Models\User;
use App\Services\Menu\PlannedMenuService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPlannedMenu extends EditRecord
{
    protected static string $resource = PlannedMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Odsouhlasit jídelní lístek')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Odsouhlasením vznikne okamžitě zveřejněný jídelní lístek pro každou pobočku. Plán již nebude možné upravovat.')
                ->visible(function (): bool {
                    $user = auth()->user();

                    return $this->record->isDraft() && $user instanceof User && $user->canApprovePlannedMenu();
                })
                ->action(function (): void {
                    $user = auth()->user();

                    if (! $user instanceof User) {
                        return;
                    }

                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    app(PlannedMenuService::class)->approve($this->record->refresh(), $user);
                    Notification::make()->success()->title('Jídelní lístek byl odsouhlasen')->send();
                    $this->redirect(PlannedMenuResource::getUrl('view', ['record' => $this->record]));
                }),
            DeleteAction::make()->visible(fn (): bool => $this->record->isDraft()),
        ];
    }
}
