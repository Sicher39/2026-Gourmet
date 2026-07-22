<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Exceptions\AresLookupNotFoundException;
use App\Services\Exceptions\AresLookupUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class AresService
{
    public function lookup(string $ico): array
    {
        $normalizedIco = $this->normalizeIco($ico);

        return Cache::remember("ares.lookup.{$normalizedIco}", now()->addDay(), function () use ($normalizedIco): array {
            try {
                $response = Http::timeout(5)
                    ->retry(2, 200)
                    ->get("https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/{$normalizedIco}");
            } catch (ConnectionException $exception) {
                throw new AresLookupUnavailableException('ARES služba je dočasně nedostupná.', 0, $exception);
            }

            if ($response->status() === 404) {
                throw new AresLookupNotFoundException('Subjekt s tímto IČO nebyl v ARES nalezen.');
            }

            if ($response->failed()) {
                throw new AresLookupUnavailableException('ARES služba vrátila neočekávanou odpověď.');
            }

            /** @var array<string, mixed> $raw */
            $raw = $response->json();

            return $this->normalizePayload($normalizedIco, $raw);
        });
    }

    private function normalizeIco(string $ico): string
    {
        $normalizedIco = preg_replace('/\D+/', '', $ico) ?? '';

        if (strlen($normalizedIco) !== 8) {
            throw new InvalidArgumentException('IČO musí obsahovat přesně 8 číslic.');
        }

        return $normalizedIco;
    }

    private function normalizePayload(string $ico, array $raw): array
    {
        $sidlo = Arr::get($raw, 'sidlo', []);
        $street = $this->buildStreet($sidlo, Arr::get($raw, 'adresaDorucovaci.radekAdresy1'));

        return [
            'company_name' => Arr::get($raw, 'obchodniJmeno'),
            'company_id' => $ico,
            'vat_id' => Arr::get($raw, 'dic'),
            'street' => $street,
            'city' => Arr::get($sidlo, 'nazevObce') ?? Arr::get($sidlo, 'nazevMestskeCasti') ?? Arr::get($sidlo, 'nazevObceCasti'),
            'zip' => $this->normalizeZip((string) Arr::get($sidlo, 'psc', '')),
            'country' => 'CZ',
            'raw' => $raw,
        ];
    }

    private function buildStreet(array $sidlo, ?string $deliveryLine): ?string
    {
        $houseNumber = trim((string) Arr::get($sidlo, 'cisloDomovni', ''));
        $orientationNumber = trim((string) Arr::get($sidlo, 'cisloOrientacni', ''));

        $number = $houseNumber;

        if ($orientationNumber !== '') {
            $number = $houseNumber !== '' ? "{$houseNumber}/{$orientationNumber}" : $orientationNumber;
        }

        $streetName = trim((string) Arr::get($sidlo, 'nazevUlice', ''));
        $street = trim("{$streetName} {$number}");

        if ($street !== '') {
            return $street;
        }

        $textAddress = trim((string) Arr::get($sidlo, 'textovaAdresa', ''));

        if ($textAddress !== '') {
            return $textAddress;
        }

        $deliveryAddressLine = trim((string) ($deliveryLine ?? ''));

        return $deliveryAddressLine !== '' ? $deliveryAddressLine : null;
    }

    private function normalizeZip(string $zip): string
    {
        return preg_replace('/\s+/', '', $zip) ?? '';
    }
}
