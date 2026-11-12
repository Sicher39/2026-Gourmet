<?php

declare(strict_types=1);

namespace App\Filament\Support\Concerns;

trait ClearsSearchableSelectSearch
{
    /**
     * Filament keeps the typed search text inside a multiple searchable select after an option is
     * picked, which forces the user to clear the field before searching for the next item.
     *
     * @return array<string, string>
     */
    protected static function clearSearchAfterSelectionAttributes(): array
    {
        $clearSearchAfterSelection = <<<'JS'
            $nextTick(() => {
                const optionSelect = select

                if (! optionSelect || optionSelect.clearsSearchAfterSelection) {
                    return
                }

                optionSelect.clearsSearchAfterSelection = true

                const originalSelectOption = optionSelect.selectOption.bind(optionSelect)

                optionSelect.selectOption = (value) => {
                    const wasSelected = Array.isArray(optionSelect.state)
                        && optionSelect.state.some((selected) => String(selected) === String(value))

                    originalSelectOption(value)

                    if (wasSelected || ! optionSelect.searchInput) {
                        return
                    }

                    optionSelect.searchInput.value = ''
                    optionSelect.searchInput.dispatchEvent(new Event('input', { bubbles: true }))
                }
            })
            JS;

        return ['x-init' => $clearSearchAfterSelection];
    }
}
