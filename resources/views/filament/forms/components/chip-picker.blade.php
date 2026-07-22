@php
    $options = $getOptions();
    $isGrouped = $isGrouped();
    $isMultiple = $isMultiple();
    $isSearchable = $isSearchable();
    $placeholder = $getPlaceholder() ?? __('filament-forms::components.select.placeholder');
    $emptyMessage = $getEmptyMessage() ?? 'Žádné položky';
    $columns = min(max(1, $getOptionColumns() ?? 3), 6);
    $gridClass = match ($columns) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
        5 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-5',
        default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-6',
    };
    $statePath = $getStatePath();
    $entangledState = $applyStateBindingModifiers('$entangle(' . \Illuminate\Support\Js::from($statePath) . ')');
    $isDisabled = $isDisabled();
    $selectedOptionLabels = $getSelectedOptionLabels();
    $disabledValues = $getDisabledValues();
    $disabledSet = array_flip(array_map('strval', $disabledValues));
    $disabledValueAction = $getDisabledValueAction();

    // Build options data for Alpine — flat or grouped shape.
    $optionsData = [];
    if ($isGrouped) {
        foreach ($options as $groupName => $groupOptions) {
            $items = [];
            foreach ($groupOptions as $key => $label) {
                $items[] = ['value' => $key, 'label' => $label, 'disabled' => isset($disabledSet[(string) $key])];
            }
            $optionsData[] = ['group' => $groupName, 'options' => $items];
        }
    } else {
        foreach ($options as $key => $label) {
            $optionsData[] = ['value' => $key, 'label' => $label, 'disabled' => isset($disabledSet[(string) $key])];
        }
    }

    // Stable key that changes when options, statePath, multiple-mode, grouped-mode,
    // fallback labels, disabled values, or disabled action change — forcing Alpine
    // to remount after a Livewire morph.
    $componentKey = 'chip-' . hash('xxh3', json_encode([
        'options'  => $optionsData,
        'path'     => $statePath,
        'multi'    => $isMultiple,
        'grouped'  => $isGrouped,
        'labels'   => $selectedOptionLabels,
        'disabled' => array_values($disabledValues),
        'action'   => $disabledValueAction,
    ]));
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        wire:key="{{ $componentKey }}"
        x-data="{
            state: $wire.{{ $entangledState }},
            search: '',
            allOptions: @js($optionsData),
            isMultiple: @js($isMultiple),
            isGrouped: @js($isGrouped),
            disabled: @js($isDisabled),
            fallbackLabels: @js($selectedOptionLabels),
            disabledValueAction: @js($disabledValueAction),

            get selectedKeys() {
                if (this.isMultiple) {
                    return Array.isArray(this.state) ? this.state.map(String) : [];
                }

                return (this.state !== null && this.state !== undefined) ? [String(this.state)] : [];
            },

            get filteredOptions() {
                const q = this.search.toLowerCase().trim();
                if (!q) {
                    return this.allOptions;
                }

                // Grouped mode: filter within each group, remove empty groups.
                if (this.isGrouped) {
                    return this.allOptions.map(group => ({
                        group: group.group,
                        options: group.options.filter(o => String(o.label).toLowerCase().includes(q)),
                    })).filter(group => group.options.length > 0);
                }

                return this.allOptions.filter(o => String(o.label).toLowerCase().includes(q));
            },

            get availableOptions() {
                const selected = new Set(this.selectedKeys);

                return this.filteredOptions.filter(o => !selected.has(String(o.value)));
            },

            get availableGroupedOptions() {
                const selected = new Set(this.selectedKeys);

                return this.filteredOptions.map(group => ({
                    group: group.group,
                    options: group.options.filter(o => !selected.has(String(o.value))),
                })).filter(group => group.options.length > 0);
            },

            get selectedOptions() {
                return this.selectedKeys.map(key => {
                    // Search flat options first.
                    let option = this.findOption(key);
                    if (option) {
                        return { ...option, stale: false };
                    }

                    // If grouped, search through nested groups.
                    if (this.isGrouped) {
                        for (const group of this.allOptions) {
                            if (group.options && Array.isArray(group.options)) {
                                option = group.options.find(item => String(item.value) === key);
                                if (option) {
                                    return { ...option, stale: false };
                                }
                            }
                        }
                    }

                    // Check fallback labels (e.g. selected tables no longer available).
                    const fallbackLabel = this.fallbackLabels[String(key)];
                    if (fallbackLabel !== undefined) {
                        return { value: String(key), label: fallbackLabel, stale: true, disabled: false };
                    }

                    return {
                        value: key,
                        label: `Hodnota ${key} (již nedostupné)`,
                        stale: true,
                        disabled: false,
                    };
                });
            },

            findOption(key) {
                if (!this.isGrouped) {
                    return this.allOptions.find(item => String(item.value) === key);
                }

                for (const group of this.allOptions) {
                    if (group.options && Array.isArray(group.options)) {
                        const found = group.options.find(item => String(item.value) === key);
                        if (found) {
                            return found;
                        }
                    }
                }

                return undefined;
            },

            toggle(key) {
                if (this.disabled) {
                    return;
                }

                const option = this.findOption(String(key));
                if (!option) {
                    return;
                }

                // Disabled option with an action: commit pending Livewire state
                // (e.g. deferred entangle updates) before invoking the action
                // to prevent the server-side handler from reading stale form data.
                if (option.disabled && this.disabledValueAction) {
                    const commit = typeof $wire.$commit === 'function'
                        ? $wire.$commit()
                        : Promise.resolve();
                    commit.then(() => {
                        $wire.mountAction(this.disabledValueAction, { value: option.value });
                    });
                    return;
                }

                // Truly disabled option (no action configured).
                if (option.disabled) {
                    return;
                }

                const keyStr = String(key);

                if (this.isMultiple) {
                    const current = Array.isArray(this.state) ? this.state.map(String) : [];

                    if (current.includes(keyStr)) {
                        this.state = this.state.filter(k => String(k) !== keyStr);
                    } else {
                        this.state = [...(Array.isArray(this.state) ? this.state : []), key];
                    }
                } else {
                    const stateStr = (this.state !== null && this.state !== undefined) ? String(this.state) : null;
                    this.state = (stateStr === keyStr) ? null : key;
                }
            },

            remove(key) {
                if (this.disabled) {
                    return;
                }

                const keyStr = String(key);

                if (this.isMultiple) {
                    this.state = (Array.isArray(this.state) ? this.state : []).filter(k => String(k) !== keyStr);
                } else {
                    this.state = null;
                }
            },
        }"
        x-on:click.outside="search = ''"
        class="chip-picker"
    >
        <div class="flex flex-wrap gap-1.5 min-h-[1.5rem]">
            <template x-for="option in selectedOptions" :key="option.value">
                <span
                    :class="option.disabled
                        ? 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-400 dark:bg-gray-800/50 dark:text-gray-500 line-through'
                        : (option.stale
                            ? 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300'
                            : 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-300')"
                >
                    <span x-text="option.label"></span>
                    <button
                        type="button"
                        x-on:click.stop="remove(option.value)"
                        x-bind:disabled="disabled"
                        title="Odebrat" aria-label="Odebrat"
                        :class="option.disabled
                            ? 'inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 -mr-1 disabled:opacity-50 disabled:cursor-not-allowed'
                            : (option.stale
                                ? 'inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-yellow-200 dark:hover:bg-yellow-500/40 -mr-1 disabled:opacity-50 disabled:cursor-not-allowed'
                                : 'inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-primary-200 dark:hover:bg-primary-500/40 -mr-1 disabled:opacity-50 disabled:cursor-not-allowed')"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
        </div>

        @if ($isSearchable)
            <div class="relative mt-2">
                <input
                    type="text"
                    x-model="search"
                    x-bind:disabled="disabled"
                    placeholder="{{ $placeholder }}"
                    class="block w-full px-3 py-2 text-sm border rounded-lg
                        border-gray-300 dark:border-gray-600
                        bg-white dark:bg-gray-800
                        text-gray-900 dark:text-gray-100
                        placeholder-gray-400 dark:placeholder-gray-500
                        focus:border-primary-500 dark:focus:border-primary-400
                        focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400
                        disabled:opacity-50 disabled:cursor-not-allowed"
                />
            </div>
        @endif

        @if ($isGrouped)
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="group in availableGroupedOptions" :key="group.group">
                    <div>
                        <div
                            class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5"
                            x-text="group.group"
                        ></div>
                        <div class="flex flex-col gap-1.5">
                            <template x-for="option in group.options" :key="option.value">
                                <button
                                    type="button"
                                    x-on:click="toggle(option.value)"
                                    x-bind:disabled="disabled || (option.disabled && !disabledValueAction)"
                                    x-bind:class="option.disabled
                                        ? (disabledValueAction
                                            ? 'px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40 cursor-pointer'
                                            : 'px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors bg-gray-100 dark:bg-gray-800/50 text-gray-400 dark:text-gray-500 border-gray-200 dark:border-gray-700 line-through cursor-not-allowed')
                                        : 'px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed'"
                                    x-bind:aria-disabled="option.disabled && !disabledValueAction ? 'true' : 'false'"
                                                                        x-text="option.label"
                                ></button>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="availableGroupedOptions.length === 0 && allOptions.length > 0">
                    <div class="text-sm text-gray-500 dark:text-gray-400 py-2 col-span-full">
                        {{ $emptyMessage }}
                    </div>
                </template>
                <template x-if="allOptions.length === 0">
                    <div class="text-sm text-gray-500 dark:text-gray-400 py-2 col-span-full">
                        {{ $emptyMessage }}
                    </div>
                </template>
            </div>
        @else
            <div @class(['mt-2 grid gap-1.5', $gridClass])>
                <template x-for="option in availableOptions" :key="option.value">
                    <button
                        type="button"
                        x-on:click="toggle(option.value)"
                        x-bind:disabled="disabled || (option.disabled && !disabledValueAction)"
                        x-bind:class="option.disabled
                            ? (disabledValueAction
                                ? 'px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40 cursor-pointer'
                                : 'px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors bg-gray-100 dark:bg-gray-800/50 text-gray-400 dark:text-gray-500 border-gray-200 dark:border-gray-700 line-through cursor-not-allowed')
                            : 'px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed'"
                        x-bind:aria-disabled="option.disabled && !disabledValueAction ? 'true' : 'false'"
                                                x-text="option.label"
                    ></button>
                </template>
                <template x-if="availableOptions.length === 0 && allOptions.length > 0">
                    <div class="text-sm text-gray-500 dark:text-gray-400 py-2">
                        {{ $emptyMessage }}
                    </div>
                </template>
                <template x-if="allOptions.length === 0">
                    <div class="text-sm text-gray-500 dark:text-gray-400 py-2">
                        {{ $emptyMessage }}
                    </div>
                </template>
            </div>
        @endif
    </div>
</x-dynamic-component>
