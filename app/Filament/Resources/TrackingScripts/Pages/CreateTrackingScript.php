<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackingScripts\Pages;

use App\Filament\Resources\TrackingScripts\TrackingScriptResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateTrackingScript extends CreateRecord
{
    protected static string $resource = TrackingScriptResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return TrackingScriptResource::normalizeJsonPathFields($data);
    }

    protected function getFormActions(): array
    {
        return [$this->getCreateFormAction(), $this->getCreateAnotherFormAction(), $this->getCancelFormAction()];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Vytvořit a odejít');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Vytvořit další');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Zrušit');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }
}
