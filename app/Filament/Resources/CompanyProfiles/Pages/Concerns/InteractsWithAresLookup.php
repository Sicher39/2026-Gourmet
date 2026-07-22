<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyProfiles\Pages\Concerns;

use App\Services\AresService;
use App\Services\Exceptions\AresLookupNotFoundException;
use App\Services\Exceptions\AresLookupUnavailableException;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

trait InteractsWithAresLookup
{
    protected function getAresLookupAction(): Action
    {
        return Action::make('loadFromAres')
            ->label('Načíst z ARES')
            ->icon('heroicon-o-magnifying-glass')
            ->schema([
                TextInput::make('company_id_number')
                    ->label('IČO')
                    ->required()
                    ->rule('regex:/^\d{8}$/')
                    ->validationMessages([
                        'required' => 'Zadejte IČO.',
                        'regex' => 'IČO musí obsahovat přesně 8 číslic.',
                    ])
                    ->helperText('Zadejte IČO ve formátu 8 číslic bez mezer.')
                    ->maxLength(8)
                    ->default(fn (): ?string => $this->getAresPrefilledCompanyIdNumber())
                    ->formatStateUsing(fn (?string $state): ?string => $state ?? $this->getAresPrefilledCompanyIdNumber()),
            ])
            ->action(function (array $data): void {
                try {
                    $aresData = app(AresService::class)->lookup((string) $data['company_id_number']);
                } catch (InvalidArgumentException) {
                    Notification::make()
                        ->title('Neplatné IČO')
                        ->body('Zadejte prosím platné IČO o 8 číslicích.')
                        ->danger()
                        ->send();

                    return;
                } catch (AresLookupNotFoundException) {
                    Notification::make()
                        ->title('Firma nebyla nalezena')
                        ->body('Subjekt s tímto IČO nebyl v ARES nalezen.')
                        ->danger()
                        ->send();

                    return;
                } catch (AresLookupUnavailableException) {
                    Notification::make()
                        ->title('ARES je nedostupný')
                        ->body('Služba ARES je dočasně nedostupná. Zkuste to prosím později.')
                        ->danger()
                        ->send();

                    return;
                } catch (Throwable $exception) {
                    Log::error('ARES lookup failed unexpectedly.', [
                        'company_id_number' => $data['company_id_number'] ?? null,
                        'exception' => $exception,
                    ]);

                    Notification::make()
                        ->title('Načtení z ARES se nezdařilo')
                        ->body('Došlo k neočekávané chybě. Zkuste to prosím znovu později.')
                        ->danger()
                        ->send();

                    return;
                }

                $this->applyAresDataToForm($aresData);
            });
    }

    protected function getAresPrefilledCompanyIdNumber(): ?string
    {
        $formState = $this->form->getRawState();

        if (filled($formState['company_id_number'] ?? null)) {
            return (string) $formState['company_id_number'];
        }

        if (property_exists($this, 'record') && $this->record !== null && filled($this->record->company_id_number)) {
            return (string) $this->record->company_id_number;
        }

        return null;
    }

    protected function applyAresDataToForm(array $aresData): void
    {
        $street = $aresData['street'] ?? null;
        $zip = $aresData['zip'] ?? null;
        $city = $aresData['city'] ?? null;

        $currentState = $this->form->getRawState();
        $updates = [
            'company_name' => $aresData['company_name'] ?? null,
            'company_id_number' => $aresData['company_id'] ?? null,
            'vat_id' => $aresData['vat_id'] ?? null,
            'street' => $street,
            'city' => $city,
            'zip' => $zip,
            'country' => $aresData['country'] ?? 'CZ',

        ];

        $this->form->fill(array_merge($currentState, $updates));

        Notification::make()
            ->title('Údaje byly načteny z ARES')
            ->success()
            ->send();
    }
}
