<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuItemType;
use App\Filament\Restaurant\Resources\PlannedMenuResource\Pages;
use App\Models\MenuCatalogItem;
use App\Models\NonCookingDay;
use App\Models\PlannedMenu;
use App\Models\PlannedMenuBranch;
use App\Models\PlannedMenuDay;
use App\Models\PlannedMenuItem;
use App\Models\PlannedMenuItemBranch;
use App\Models\User;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\HtmlString;
use UnitEnum;

class PlannedMenuResource extends Resource
{
    protected static ?string $model = PlannedMenu::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $navigationLabel = 'Plánovaný jídelní lístek';

    protected static ?string $modelLabel = 'plánovaný jídelní lístek';

    protected static ?string $pluralModelLabel = 'plánované jídelní lístky';

    protected static ?int $navigationSort = 10;

    /** @var array<int, string> */
    private static array $menuCatalogItemNames = [];

    /** @var array<int, string>|null */
    private static ?array $plannedBranchNames = null;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Týden')
                ->schema([
                    DatePicker::make('week_start')
                        ->label('Pondělí')
                        ->native(false)
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?PlannedMenu $record): bool => $record !== null)
                        ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                            if (CarbonImmutable::parse($value)->dayOfWeekIso !== 1) {
                                $fail('Počáteční datum musí být pondělí.');
                            }
                        }),
                    Textarea::make('note')
                        ->label('Interní poznámka')
                        ->rows(2)
                        ->disabled(fn (): bool => ! static::currentUserCanManageShared())
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Tabs::make('Pracovní týden')
                ->tabs([
                    static::dayTab('Pondělí', 0),
                    static::dayTab('Úterý', 1),
                    static::dayTab('Středa', 2),
                    static::dayTab('Čtvrtek', 3),
                    static::dayTab('Pátek', 4),
                    static::commonMenuTab(),
                ])
                ->persistTabInQueryString('den')
                ->visible(fn (?PlannedMenu $record): bool => $record !== null)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('week_start', 'desc')
            ->columns([
                TextColumn::make('week_start')->label('Týden od')->date('d.m.Y')->sortable(),
                TextColumn::make('week_end')->label('Do')->date('d.m.Y')->sortable(),
                TextColumn::make('status')->label('Stav')->badge()->formatStateUsing(fn ($state): string => $state->label()),
                TextColumn::make('branches_count')->counts('branches')->label('Provozoven'),
                TextColumn::make('approved_at')->label('Odsouhlaseno')->dateTime('d.m.Y H:i')->placeholder('—'),
                TextColumn::make('approver.name')->label('Odsouhlasil')->placeholder('—'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->visible(fn (PlannedMenu $record): bool => $record->isDraft()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlannedMenus::route('/'),
            'create' => Pages\CreatePlannedMenu::route('/create'),
            'view' => Pages\ViewPlannedMenu::route('/{record}'),
            'edit' => Pages\EditPlannedMenu::route('/{record}/edit'),
        ];
    }

    private static function commonMenuTab(): Tab
    {
        return Tab::make('Společné menu')
            ->icon('heroicon-o-rectangle-stack')
            ->schema([
                Callout::make('Společné položky')
                    ->description('Položky se při odsouhlasení přidají na konec vybraných dnů. V jídelním lístku provozovny je pak lze samostatně upravovat, včetně dostupnosti a příloh.')
                    ->info()
                    ->columnSpanFull(),
                Repeater::make('commonItems')
                    ->label('Položky společného menu')
                    ->relationship()
                    ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                        $item = PlannedMenuItem::query()->with('plannedMenu')->find($data['id'] ?? null);

                        $data['scheduled_day_ids'] = $item instanceof PlannedMenuItem && $item->plannedMenu instanceof PlannedMenu
                            ? $item->scheduledDays()
                                ->whereKey(static::commonMenuCookingDays($item->plannedMenu)->modelKeys())
                                ->pluck('planned_menu_days.id')
                                ->map(fn (int|string $id): string => (string) $id)
                                ->all()
                            : [];

                        return $data;
                    })
                    ->afterCreate(fn (array $data, PlannedMenuItem $record) => static::syncScheduledDays($record, $data))
                    ->afterUpdate(fn (array $data, PlannedMenuItem $record) => static::syncScheduledDays($record, $data))
                    ->defaultItems(0)
                    ->orderColumn('sort_order')
                    ->addActionLabel('Přidat společnou položku')
                    ->addable(fn (): bool => static::currentUserCanManageShared())
                    ->deletable(fn (): bool => static::currentUserCanManageShared())
                    ->reorderable(fn (): bool => static::currentUserCanManageShared())
                    ->collapsible()
                    ->collapsed()
                    ->collapseAllAction(fn (Action $action): Action => $action->label('Skrýt vše'))
                    ->expandAllAction(fn (Action $action): Action => $action->label('Otevřít vše'))
                    ->itemLabel(fn (array $state, string $key, Repeater $component): HtmlString => static::menuItemLabel(
                        state: $state,
                        itemKey: $key,
                        repeaterState: $component->getState(),
                    ))
                    ->schema([
                        CheckboxList::make('scheduled_day_ids')
                            ->label('Nabízet ve dnech')
                            ->options(function ($livewire): array {
                                $plannedMenu = $livewire->getRecord();

                                if (! $plannedMenu instanceof PlannedMenu) {
                                    return [];
                                }

                                return static::commonMenuCookingDays($plannedMenu)
                                    ->mapWithKeys(fn (PlannedMenuDay $day): array => [
                                        $day->getKey() => $day->date->translatedFormat('l j. n.'),
                                    ])
                                    ->all();
                            })
                            ->required()
                            ->columns(5)
                            ->disabled(fn (): bool => ! static::currentUserCanManageShared())
                            ->columnSpanFull(),
                        ...static::menuItemSchema(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function dayTab(string $label, int $offset): Tab
    {
        return Tab::make(function ($livewire) use ($label, $offset): string {
            $plannedMenu = $livewire->getRecord();

            return static::dayTabLabel(
                $plannedMenu instanceof PlannedMenu ? $plannedMenu : null,
                $label,
                $offset,
            );
        })
            ->icon(function ($livewire) use ($offset): ?string {
                $plannedMenu = $livewire->getRecord();

                return $plannedMenu instanceof PlannedMenu && static::isNonCookingDay($plannedMenu, $offset)
                    ? 'heroicon-o-exclamation-triangle'
                    : null;
            })
            ->schema([
                Repeater::make("day_{$offset}")
                    ->hiddenLabel()
                    ->defaultItems(0)
                    ->relationship(
                        name: 'days',
                        modifyQueryUsing: function (Builder $query, $livewire) use ($offset): Builder {
                            $plannedMenu = $livewire->getRecord();

                            if (! $plannedMenu instanceof PlannedMenu) {
                                return $query->whereRaw('1 = 0');
                            }

                            $date = CarbonImmutable::parse($plannedMenu->week_start)
                                ->addDays($offset)
                                ->toDateString();

                            return $query->whereDate('planned_menu_days.date', $date);
                        },
                    )
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->itemHeaders(false)
                    ->schema([
                        Callout::make('Tento den se nevaří')
                            ->description(function (?PlannedMenuDay $record): string {
                                if (! $record instanceof PlannedMenuDay) {
                                    return 'Pro tento den se jídelní lístek neplánuje.';
                                }

                                $note = NonCookingDay::query()
                                    ->whereDate('date', $record->date)
                                    ->value('internal_note');

                                return filled($note)
                                    ? (string) $note
                                    : 'Pro tento den se jídelní lístek neplánuje.';
                            })
                            ->warning()
                            ->visible(fn (?PlannedMenuDay $record): bool => $record?->is_non_cooking_day === true)
                            ->columnSpanFull(),
                        Repeater::make('items')
                            ->label('Polévky a menu')
                            ->relationship(
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderByRaw(
                                    'CASE WHEN planned_menu_items.type = ? THEN 0 ELSE 1 END',
                                    [MenuItemType::Soup->value],
                                ),
                                modifyRecordsUsing: fn (EloquentCollection $records): EloquentCollection => $records
                                    ->sortBy(fn (PlannedMenuItem $item): string => sprintf(
                                        '%d-%010d',
                                        $item->type === MenuItemType::Soup ? 0 : 1,
                                        $item->sort_order,
                                    )),
                            )
                            ->defaultItems(0)
                            ->hidden(fn (?PlannedMenuDay $record): bool => $record?->is_non_cooking_day === true)
                            ->orderColumn('sort_order')
                            ->addActionLabel('Přidat polévku nebo menu')
                            ->addable(fn (): bool => static::currentUserCanManageShared())
                            ->deletable(fn (): bool => static::currentUserCanManageShared())
                            ->reorderable(fn (): bool => static::currentUserCanManageShared())
                            ->collapsible()
                            ->collapsed()
                            ->collapseAllAction(fn (Action $action): Action => $action->label('Skrýt vše'))
                            ->expandAllAction(fn (Action $action): Action => $action->label('Otevřít vše'))
                            ->itemLabel(fn (array $state, string $key, Repeater $component): HtmlString => static::menuItemLabel(
                                state: $state,
                                itemKey: $key,
                                repeaterState: $component->getState(),
                            ))
                            ->schema(static::menuItemSchema())
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function dayTabLabel(?PlannedMenu $plannedMenu, string $label, int $offset): string
    {
        if (! $plannedMenu instanceof PlannedMenu || $plannedMenu->week_start === null) {
            return $label;
        }

        $date = CarbonImmutable::parse($plannedMenu->week_start)->addDays($offset);

        return $label.' '.$date->format('j. n.');
    }

    private static function isNonCookingDay(?PlannedMenu $plannedMenu, int $offset): bool
    {
        if (! $plannedMenu instanceof PlannedMenu || $plannedMenu->week_start === null) {
            return false;
        }

        $date = CarbonImmutable::parse($plannedMenu->week_start)->addDays($offset)->toDateString();

        return $plannedMenu->days()->whereDate('date', $date)->where('is_non_cooking_day', true)->exists();
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, array<string, mixed>>  $repeaterState
     */
    private static function menuItemLabel(array $state, string $itemKey, array $repeaterState): HtmlString
    {
        $type = MenuItemType::tryFrom((string) ($state['type'] ?? ''));
        $typeLabel = $type?->label() ?? 'Položka';
        $typeNumber = 0;

        foreach ($repeaterState as $key => $itemState) {
            if (($itemState['type'] ?? null) === $type?->value) {
                $typeNumber++;
            }

            if ((string) $key === $itemKey) {
                break;
            }
        }

        $catalogItemName = static::menuCatalogItemName(
            catalogItemId: $state['menu_catalog_item_id'] ?? null,
            repeaterState: $repeaterState,
        );
        $badgeColor = match ($type) {
            MenuItemType::Soup => '#f59e0b',
            MenuItemType::Main => '#10b981',
            default => '#64748b',
        };
        $badgeBackground = match ($type) {
            MenuItemType::Soup => 'rgba(245, 158, 11, 0.16)',
            MenuItemType::Main => 'rgba(16, 185, 129, 0.16)',
            default => 'rgba(100, 116, 139, 0.16)',
        };
        $numberedType = $type instanceof MenuItemType ? $typeLabel.' '.max(1, $typeNumber) : $typeLabel;
        $name = filled($catalogItemName) ? ' – '.e((string) $catalogItemName) : '';

        return new HtmlString(
            '<span style="display:inline-flex;align-items:center;border-radius:9999px;padding:0.125rem 0.5rem;font-weight:600;color:'
            .$badgeColor.';background-color:'.$badgeBackground.'">'.e($numberedType).'</span>'
            .'<span style="margin-left:0.35rem">'.$name.'</span>',
        );
    }

    /**
     * @param  array<string, array<string, mixed>>  $repeaterState
     */
    private static function menuCatalogItemName(mixed $catalogItemId, array $repeaterState): ?string
    {
        if (! filled($catalogItemId)) {
            return null;
        }

        $catalogItemIds = collect($repeaterState)
            ->pluck('menu_catalog_item_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
        $missingCatalogItemIds = array_diff($catalogItemIds, array_keys(static::$menuCatalogItemNames));

        if ($missingCatalogItemIds !== []) {
            static::$menuCatalogItemNames += MenuCatalogItem::query()
                ->whereKey($missingCatalogItemIds)
                ->pluck('name', 'id')
                ->mapWithKeys(fn (string $name, int|string $id): array => [(int) $id => $name])
                ->all();
        }

        return static::$menuCatalogItemNames[(int) $catalogItemId] ?? null;
    }

    /** @param array<string, mixed> $item */
    private static function menuItemTypeSortOrder(array $item): int
    {
        return ($item['type'] ?? null) === MenuItemType::Soup->value ? 0 : 1;
    }

    private static function plannedBranchName(mixed $plannedMenuBranchId): string
    {
        static::$plannedBranchNames ??= PlannedMenuBranch::query()
            ->pluck('branch_name_snapshot', 'id')
            ->mapWithKeys(fn (string $name, int|string $id): array => [(int) $id => $name])
            ->all();

        return static::$plannedBranchNames[(int) $plannedMenuBranchId] ?? '';
    }

    /** @return array<int, mixed> */
    private static function menuItemSchema(): array
    {
        return [
            Select::make('type')
                ->label('Typ')
                ->options(collect(MenuItemType::cases())->mapWithKeys(fn (MenuItemType $type): array => [$type->value => $type->label()])->all())
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set): void {
                    $set('menu_catalog_item_id', null);

                    $items = $get('../');

                    if (! is_array($items) || collect($items)->contains(fn (mixed $item): bool => ! is_array($item))) {
                        return;
                    }

                    uasort($items, fn (array $firstItem, array $secondItem): int => static::menuItemTypeSortOrder($firstItem)
                        <=> static::menuItemTypeSortOrder($secondItem));

                    $set('../', $items);
                })
                ->required()
                ->disabled(fn (): bool => ! static::currentUserCanManageShared()),
            Select::make('menu_catalog_item_id')
                ->label('Základní položka')
                ->relationship('catalogItem', 'name', function (Builder $query, Get $get): Builder {
                    $currentCatalogItemId = $get('menu_catalog_item_id');
                    $usedCatalogItemIds = collect($get('../') ?? [])
                        ->pluck('menu_catalog_item_id')
                        ->filter()
                        ->reject(fn (mixed $catalogItemId): bool => (string) $catalogItemId === (string) $currentCatalogItemId)
                        ->unique()
                        ->values()
                        ->all();

                    return $query
                        ->where('menu_catalog_items.is_active', true)
                        ->whereHas('catalogType', function (Builder $query) use ($get): void {
                            $query->where('slug', $get('type') === MenuItemType::Soup->value ? 'polevky' : 'hlavni-jidla');
                        })
                        ->when(
                            $usedCatalogItemIds !== [],
                            fn (Builder $query): Builder => $query->whereNotIn('menu_catalog_items.id', $usedCatalogItemIds),
                        )
                        ->orderBy('menu_catalog_items.name');
                })
                ->searchable()
                ->preload()
                ->optionsLimit(50)
                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                ->live()
                ->afterStateUpdated(function (mixed $state, Set $set): void {
                    $catalogItem = MenuCatalogItem::query()->find($state);

                    if (! $catalogItem instanceof MenuCatalogItem) {
                        $set('default_price', null);
                        $set('amount', null);
                        $set('menu_unit_id', null);

                        return;
                    }

                    $set('default_price', $catalogItem->default_price);
                    $set('amount', $catalogItem->amount);
                    $set('menu_unit_id', $catalogItem->menu_unit_id);
                })
                ->required()
                ->disabled(fn (): bool => ! static::currentUserCanManageShared()),
            TextInput::make('default_price')
                ->label('Výchozí cena')
                ->numeric()
                ->step(0.01)
                ->minValue(0)
                ->required()
                ->disabled(fn (): bool => ! static::currentUserCanManageShared()),
            TextInput::make('amount')
                ->label('Množství')
                ->numeric()
                ->step(0.001)
                ->minValue(0)
                ->disabled(fn (): bool => ! static::currentUserCanManageShared()),
            Select::make('menu_unit_id')
                ->label('Jednotka')
                ->relationship('unit', 'name', fn (Builder $query) => $query->where('menu_units.is_active', true)->orderBy('menu_units.sort_order'))
                ->searchable()
                ->preload()
                ->disabled(fn (): bool => ! static::currentUserCanManageShared()),
            Repeater::make('branchVariants')
                ->label('Provozovny')
                ->relationship()
                ->default(function ($livewire): array {
                    $plannedMenu = $livewire->getRecord();

                    if (! $plannedMenu instanceof PlannedMenu) {
                        return [];
                    }

                    return $plannedMenu->branches()
                        ->orderBy('branch_name_snapshot')
                        ->get()
                        ->map(fn (PlannedMenuBranch $branch): array => [
                            'planned_menu_branch_id' => $branch->getKey(),
                            'is_available' => true,
                        ])
                        ->all();
                })
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->collapsible()
                ->collapsed()
                ->itemLabel(fn (array $state): string => isset($state['planned_menu_branch_id'])
                    ? 'Provozovna – '.static::plannedBranchName($state['planned_menu_branch_id'])
                    : 'Provozovna')
                ->schema([
                    Hidden::make('planned_menu_branch_id'),
                    Select::make('sideItems')
                        ->label('Přílohy')
                        ->relationship('sideItems', 'name', fn (Builder $query) => $query
                            ->where('menu_catalog_items.is_active', true)
                            ->whereHas('catalogType', fn (Builder $query) => $query->where('slug', 'prilohy'))
                            ->orderBy('menu_catalog_items.sort_order')
                            ->orderBy('menu_catalog_items.name'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->disabled(fn (?PlannedMenuItemBranch $record): bool => ! static::currentUserCanEditVariant($record)),
                    Select::make('otherItems')
                        ->label('Ostatní')
                        ->relationship('otherItems', 'name', fn (Builder $query) => $query
                            ->where('menu_catalog_items.is_active', true)
                            ->whereHas('catalogType', fn (Builder $query) => $query->where('slug', 'omacky-a-ostatni'))
                            ->orderBy('menu_catalog_items.sort_order')
                            ->orderBy('menu_catalog_items.name'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->disabled(fn (?PlannedMenuItemBranch $record): bool => ! static::currentUserCanEditVariant($record)),
                    Toggle::make('is_available')
                        ->label('Dostupné na provozovně')
                        ->default(true)
                        ->disabled(fn (?PlannedMenuItemBranch $record): bool => ! static::currentUserCanEditVariant($record)),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    /** @return EloquentCollection<int, PlannedMenuDay> */
    private static function commonMenuCookingDays(PlannedMenu $plannedMenu): EloquentCollection
    {
        $cookingDays = new EloquentCollection;

        foreach (range(0, 4) as $offset) {
            if (static::isNonCookingDay($plannedMenu, $offset)) {
                continue;
            }

            $date = CarbonImmutable::parse($plannedMenu->week_start)
                ->addDays($offset)
                ->toDateString();
            $day = $plannedMenu->days()->whereDate('date', $date)->first();

            if ($day instanceof PlannedMenuDay) {
                $cookingDays->push($day);
            }
        }

        return $cookingDays;
    }

    /** @param array<string, mixed> $data */
    private static function syncScheduledDays(PlannedMenuItem $item, array $data): void
    {
        $scheduledDayIds = collect($data['scheduled_day_ids'] ?? [])
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->unique();

        $plannedMenu = $item->plannedMenu()->first();

        if (! $plannedMenu instanceof PlannedMenu) {
            $item->scheduledDays()->sync([]);

            return;
        }

        $validDayIds = static::commonMenuCookingDays($plannedMenu)
            ->whereIn('id', $scheduledDayIds)
            ->modelKeys();

        $item->scheduledDays()->sync($validDayIds);
    }

    private static function currentUserCanManageShared(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canManageSharedPlannedMenu();
    }

    private static function currentUserCanEditVariant(?PlannedMenuItemBranch $variant): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->canManageSharedPlannedMenu()) {
            return true;
        }

        if (! $variant instanceof PlannedMenuItemBranch) {
            return false;
        }

        $variant->loadMissing('plannedBranch');

        return $user->managesRestaurant($variant->plannedBranch->restaurant_contact_information_id);
    }
}
