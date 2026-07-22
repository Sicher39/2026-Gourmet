<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Facades\FilamentView;

class EditLegalDocument extends EditRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getFormActions(): array
    {
        return [$this->getSaveFormAction(), Action::make('saveAndExit')->label('Uložit a odejít')->action('saveAndExit')->color('gray'), $this->getCancelFormAction()];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Uložit');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Zrušit');
    }

    public function saveAndExit(): void
    {
        $this->save(shouldRedirect: false);
        $redirectUrl = $this->getResourceUrl('index');
        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
