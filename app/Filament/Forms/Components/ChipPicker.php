<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class ChipPicker extends Field
{
    protected string $view = 'filament.forms.components.chip-picker';

    protected array|Closure $options = [];

    protected bool|Closure $multipleCondition = false;

    protected bool|Closure $searchableCondition = true;

    protected string|Closure|null $placeholderValue = null;

    protected string|Closure|null $emptyMessageValue = null;

    protected int|Closure|null $optionColumnsValue = null;

    protected bool|Closure $groupedCondition = false;

    /** @var array<int|string, string>|Closure */
    protected array|Closure $selectedOptionLabelsCondition = [];

    /** @var array<int|string>|Closure */
    protected array|Closure $disabledValuesCondition = [];

    /**
     * Optional Filament action name to invoke when a disabled option is clicked.
     *
     * When null (default), disabled options are truly disabled and unclickable.
     * When set, clicking a disabled option invokes $wire.mountAction(actionName, { value: option.value }).
     * The option remains non-selectable — it triggers an action, not a selection.
     */
    protected string|Closure|null $disabledValueActionName = null;

    protected string|Closure|null $relationshipNameValue = null;

    protected string|Closure $relationshipTitleAttributeValue = 'name';

    /**
     * @param  array<string|int, string>|array<string, array<string|int, string>>|Closure  $options
     *                                                                                               Flat: ['id' => 'label', ...]
     *                                                                                               Grouped: ['Group Name' => ['id' => 'label', ...], ...]
     */
    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array<string|int, string>|array<string, array<string|int, string>>
     */
    public function getOptions(): array
    {
        return $this->evaluate($this->options);
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->multipleCondition = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->evaluate($this->multipleCondition);
    }

    public function searchable(bool|Closure $condition = true): static
    {
        $this->searchableCondition = $condition;

        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->evaluate($this->searchableCondition);
    }

    public function placeholder(string|Closure|null $placeholder): static
    {
        $this->placeholderValue = $placeholder;

        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->evaluate($this->placeholderValue);
    }

    public function emptyMessage(string|Closure|null $message): static
    {
        $this->emptyMessageValue = $message;

        return $this;
    }

    public function getEmptyMessage(): ?string
    {
        return $this->evaluate($this->emptyMessageValue);
    }

    /**
     * Enable grouped mode where options are organised into named groups.
     *
     * In grouped mode, options() must return [group => [id => label, ...], ...].
     * Each group renders in its own column with a visible heading.
     */
    public function grouped(bool|Closure $condition = true): static
    {
        $this->groupedCondition = $condition;

        return $this;
    }

    public function isGrouped(): bool
    {
        return $this->evaluate($this->groupedCondition);
    }

    /**
     * Provide fallback labels for selected values that are no longer present
     * in the current options. Useful when a selected record was soft-deleted
     * or became unavailable between form renders.
     *
     * @param  array<int|string, string>|Closure  $labels
     */
    public function selectedOptionLabels(array|Closure $labels): static
    {
        $this->selectedOptionLabelsCondition = $labels;

        return $this;
    }

    /**
     * @return array<int|string, string>
     */
    public function getSelectedOptionLabels(): array
    {
        return $this->evaluate($this->selectedOptionLabelsCondition);
    }

    /**
     * Set option values that should be rendered as disabled.
     *
     * Disabled options appear with muted styling, cannot be toggled,
     * and have native disabled/aria attributes.  Existing pickers that
     * do not supply disabled values are unaffected.
     *
     * @param  array<int|string>|Closure  $values
     */
    public function disabledValues(array|Closure $values): static
    {
        $this->disabledValuesCondition = $values;

        return $this;
    }

    /**
     * @return array<int|string>
     */
    public function getDisabledValues(): array
    {
        return $this->evaluate($this->disabledValuesCondition);
    }

    /**
     * Configure a Filament page action to invoke when a disabled option is clicked.
     *
     * When null (default), disabled options are completely unclickable.
     * When set, clicking invokes $wire.mountAction(actionName, { value: option.value })
     * and the option remains non-selectable.
     *
     * @param  string|Closure|null  $actionName  The public action method name on the page.
     */
    public function disabledValueAction(string|Closure|null $actionName): static
    {
        $this->disabledValueActionName = $actionName;

        return $this;
    }

    public function getDisabledValueAction(): ?string
    {
        return $this->evaluate($this->disabledValueActionName);
    }

    public function optionColumns(int|Closure|null $columns): static
    {
        $this->optionColumnsValue = $columns;

        return $this;
    }

    public function getOptionColumns(): ?int
    {
        return $this->evaluate($this->optionColumnsValue);
    }

    /**
     * Configure a BelongsToMany relationship for this field.
     *
     * When used, the field loads existing related IDs from the relationship
     * and saves selected IDs back to the pivot table with is_primary and
     * sort_order metadata. Options must still be provided separately via
     * the options() method to allow dynamic availability filtering.
     *
     * @param  string  $name  Relationship method name on the model.
     * @param  string  $titleAttribute  Column name used to generate option labels.
     */
    public function relationship(string $name, string $titleAttribute = 'name'): static
    {
        $this->relationshipNameValue = $name;
        $this->relationshipTitleAttributeValue = $titleAttribute;
        $this->multiple();

        $this->loadStateFromRelationshipsUsing(function (ChipPicker $component, mixed $record) use ($name): void {
            if (! $record) {
                return;
            }

            $relatedKeyName = $record->{$name}()->getRelated()->getKeyName();
            $relatedIds = $record->{$name}()->pluck($relatedKeyName)->toArray();

            $component->state($relatedIds);
        });

        $this->saveRelationshipsUsing(function (ChipPicker $component, mixed $record, mixed $state) use ($name): void {
            if ($record === null) {
                return;
            }

            if (! is_array($state)) {
                $record->{$name}()->sync([]);

                return;
            }

            $pivotData = [];
            foreach ($state as $index => $id) {
                $pivotData[$id] = [
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ];
            }

            $record->{$name}()->sync($pivotData);
        });

        return $this;
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationshipNameValue);
    }

    public function getRelationshipTitleAttribute(): string
    {
        return $this->evaluate($this->relationshipTitleAttributeValue);
    }
}
