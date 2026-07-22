<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalDocument extends CreateRecord
{
    protected static string $resource = LegalDocumentResource::class;

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
