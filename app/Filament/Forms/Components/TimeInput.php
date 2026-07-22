<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Support\OpeningHoursTimeHelper;
use Closure;
use Filament\Forms\Components\TextInput;

/**
 * A stable, TextInput-based HH:MM time component for opening-hours fields.
 *
 * Replaces the native Filament TimePicker, which uses a popup that clears
 * partial input. Uses Alpine.js mask "99:99" under the hood so that typing
 * and pasting remain practical. Supports literal "24:00" for close-time
 * fields (end-of-day midnight).
 *
 * Hydration normalizes legacy "HH:MM:SS" database values to "HH:MM".
 * Dehydration ensures canonical format is persisted.
 */
class TimeInput extends TextInput
{
    protected bool $isCloseTimeField = false;

    /**
     * Mark this field as a close-time field that allows "24:00".
     */
    public function closeTime(bool $condition = true): static
    {
        $this->isCloseTimeField = $condition;

        return $this;
    }

    public function isCloseTimeField(): bool
    {
        return $this->evaluate($this->isCloseTimeField);
    }

    /**
     * @param  string|Closure|null  $condition
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->mask('99:99')
            ->placeholder('HH:MM')
            ->maxLength(5);

        $this->afterStateHydrated(function (TimeInput $component, mixed $state): void {
            if ($state === null || $state === '') {
                return;
            }

            $normalized = OpeningHoursTimeHelper::normalize((string) $state);
            $component->state($normalized);
        });

        $this->dehydrateStateUsing(function (mixed $state): ?string {
            if ($state === null || $state === '') {
                return null;
            }

            return OpeningHoursTimeHelper::normalize((string) $state);
        });

        $this->rule(static function (TimeInput $component): Closure {
            return static function (string $attribute, mixed $value, Closure $fail) use ($component): void {
                if ($value === null || $value === '') {
                    return;
                }

                $value = (string) $value;

                // Normalize before validation so partial/legacy formats are canonical
                $normalized = OpeningHoursTimeHelper::normalize($value);
                if ($normalized !== null) {
                    $value = $normalized;
                }

                if ($component->isCloseTimeField()) {
                    if (! OpeningHoursTimeHelper::isValidCloseTime($value)) {
                        $fail(OpeningHoursTimeHelper::closeTimeValidationMessage());
                    }
                } else {
                    if (! OpeningHoursTimeHelper::isValidOpenTime($value)) {
                        $fail(OpeningHoursTimeHelper::openTimeValidationMessage());
                    }
                }
            };
        });
    }
}
