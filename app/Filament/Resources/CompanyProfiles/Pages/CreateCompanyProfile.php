<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyProfiles\Pages;

use App\Filament\Resources\CompanyProfiles\CompanyProfileResource;
use App\Filament\Resources\CompanyProfiles\Pages\Concerns\InteractsWithAresLookup;
use App\Models\CompanyProfile;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyProfile extends CreateRecord
{
    use InteractsWithAresLookup;

    protected static string $resource = CompanyProfileResource::class;

    public function mount(): void
    {
        $companyProfile = CompanyProfile::current();

        if ($companyProfile !== null) {
            $this->redirect(CompanyProfileResource::getUrl('edit', ['record' => $companyProfile]));

            return;
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getAresLookupAction(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
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
