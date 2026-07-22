<?php

declare(strict_types=1);

namespace App\Filament\Resources\CookieSettings\Pages;

use App\Filament\Resources\CookieSettings\CookieSettingResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateCookieSetting extends CreateRecord
{
    protected static string $resource = CookieSettingResource::class;

    protected function getFormActions(): array
    {
        return [$this->getCreateFormAction(), $this->getCancelFormAction()];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Vytvořit a odejít');
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
