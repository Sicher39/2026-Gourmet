<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\PlannedMenuResource\Pages;

use App\Filament\Restaurant\Resources\PlannedMenuResource;
use App\Services\Menu\PlannedMenuService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPlannedMenu extends ViewRecord
{
    protected static string $resource = PlannedMenuResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->isDraft()) {
            app(PlannedMenuService::class)->initialize($this->record);
            $this->fillForm();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => $this->record->isDraft()),
        ];
    }
}
