<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\BranchMenuStatus;
use App\Enums\MenuItemType;
use App\Filament\Restaurant\Resources\BranchMenuResource\Pages;
use App\Models\BranchMenu;
use App\Models\BranchMenuDay;
use App\Models\BranchMenuItem;
use App\Models\MenuCatalogItem;
use App\Models\NonCookingDay;
use App\Models\User;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\HtmlString;
use UnitEnum;

class BranchMenuResource extends Resource
{
    protected static ?string $model = BranchMenu::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';
    protected static ?string $navigationLabel = 'Jídelní lístky provozoven';
    protected static ?string $modelLabel = 'jídelní lístek provozovny';
    protected static ?string $pluralModelLabel = 'jídelní lístky provozoven';
    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('restaurant');
        $user = auth()->user();

        if ($user instanceof User && ! $user->canManageSharedPlannedMenu()) {
            $query->whereIn('restaurant_contact_information_id', $user->managedRestaurants()->select('restaurant_contact_information.id'));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Jídelní lístek')->schema([
                TextInput::make('branch_name_snapshot')->label('Provozovna')->disabled(),
                TextInput::make('week_start')->label('Od')->formatStateUsing(fn ($state): string => CarbonImmutable::parse($state)->format('d.m.Y'))->disabled(),
                TextInput::make('week_end')->label('Do')->formatStateUsing(fn ($state): string => CarbonImmutable::parse($state)->format('d.m.Y'))->disabled(),
                TextInput::make('status')->label('Stav')->formatStateUsing(fn (mixed $state): string => static::statusLabel($state))->disabled(),
            ])->columns(4)->columnSpanFull(),
            Tabs::make('Pracovní týden')
                ->tabs([
                    static::dayTab('Pondělí', 0),
                    static::dayTab('Úterý', 1),
                    static::dayTab('Středa', 2),
                    static::dayTab('Čtvrtek', 3),
                    static::dayTab('Pátek', 4),
                ])
                ->persistTabInQueryString('den')
                ->columnSpanFull(),
        ]);
    }

    private static function dayTab(string $label, int $offset): Tab
    {
        return Tab::make(function ($livewire) use ($label, $offset): string {
            $branchMenu = $livewire->getRecord();

            return static::dayTabLabel($branchMenu instanceof BranchMenu ? $branchMenu : null, $label, $offset);
        })
            ->icon(function ($livewire) use ($offset): ?string {
                $branchMenu = $livewire->getRecord();

                return $branchMenu instanceof BranchMenu && static::isNonCookingDay($branchMenu, $offset)
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
                            $branchMenu = $livewire->getRecord();

                            if (! $branchMenu instanceof BranchMenu) {
                                return $query->whereRaw('1 = 0');
                            }

                            $date = CarbonImmutable::parse($branchMenu->week_start)
                                ->addDays($offset)
                                ->toDateString();

                            return $query->whereDate('branch_menu_days.date', $date);
                        },
                    )
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->itemHeaders(false)
                    ->schema([
                        Callout::make('Tento den se nevaří')
                            ->description(fn (?BranchMenuDay $record): string => static::nonCookingDayReason($record))
                            ->warning()
                            ->visible(fn (?BranchMenuDay $record): bool => $record?->is_non_cooking_day === true)
                            ->columnSpanFull(),
                        Repeater::make('items')
                            ->label('Denní menu, pizza a grill')
                            ->relationship(
                                modifyRecordsUsing: fn (EloquentCollection $records): EloquentCollection => $records
                                    ->sortBy(fn (BranchMenuItem $item): string => sprintf(
                                        '%d-%d-%010d',
                                        $item->is_common_menu_item ? 1 : 0,
                                        $item->type === MenuItemType::Soup ? 0 : 1,
                                        $item->sort_order,
                                    )),
                            )
                            ->defaultItems(0)
                            ->hidden(fn (?BranchMenuDay $record): bool => $record?->is_non_cooking_day === true)
                            ->orderColumn('sort_order')
                            ->addActionLabel('Přidat položku')
                            ->addable(fn ($livewire): bool => static::currentUserCanEditMenu($livewire->getRecord()))
                            ->deletable(fn ($livewire): bool => static::currentUserCanEditMenu($livewire->getRecord()))
                            ->reorderable(fn ($livewire): bool => static::currentUserCanEditMenu($livewire->getRecord()))
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
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    private static function dayTabLabel(?BranchMenu $branchMenu, string $label, int $offset): string
    {
        if (! $branchMenu instanceof BranchMenu || $branchMenu->week_start === null) {
            return $label;
        }

        return $label.' '.CarbonImmutable::parse($branchMenu->week_start)->addDays($offset)->format('j. n.');
    }

    private static function isNonCookingDay(BranchMenu $branchMenu, int $offset): bool
    {
        $date = CarbonImmutable::parse($branchMenu->week_start)->addDays($offset)->toDateString();

        return $branchMenu->days()->whereDate('date', $date)->where('is_non_cooking_day', true)->exists();
    }

    private static function nonCookingDayReason(?BranchMenuDay $day): string
    {
        if (! $day instanceof BranchMenuDay) {
            return 'Pro tento den se jídelní lístek neplánuje.';
        }

        return (string) (NonCookingDay::query()->whereDate('date', $day->date)->value('internal_note')
            ?: 'Pro tento den se jídelní lístek neplánuje.');
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, array<string, mixed>>  $repeaterState
     */
    private static function menuItemLabel(array $state, string $itemKey, array $repeaterState): HtmlString
    {
        $type = MenuItemType::tryFrom((string) ($state['type'] ?? ''));
        $typeNumber = 0;

        foreach ($repeaterState as $key => $itemState) {
            if (($itemState['type'] ?? null) === $type?->value) {
                $typeNumber++;
            }

            if ((string) $key === $itemKey) {
                break;
            }
        }

        $typeLabel = $type?->label() ?? 'Položka';
        $numberedType = $type instanceof MenuItemType ? $typeLabel.' '.max(1, $typeNumber) : $typeLabel;
        $name = (string) ($state['item_name_snapshot'] ?? '');
        $badgeColor = $type === MenuItemType::Soup ? '#f59e0b' : '#10b981';
        $badgeBackground = $type === MenuItemType::Soup ? 'rgba(245, 158, 11, 0.16)' : 'rgba(16, 185, 129, 0.16)';

        return new HtmlString(
            '<span style="display:inline-flex;align-items:center;border-radius:9999px;padding:0.125rem 0.5rem;font-weight:600;color:'
            .$badgeColor.';background-color:'.$badgeBackground.'">'.e($numberedType).'</span>'
            .(filled($name) ? '<span style="margin-left:0.35rem"> – '.e($name).'</span>' : ''),
        );
    }

    /** @return array<int, mixed> */
    private static function menuItemSchema(): array
    {
        return [
            Select::make('type')
                ->label('Typ')
                ->options(collect(MenuItemType::cases())->mapWithKeys(fn (MenuItemType $type): array => [$type->value => $type->label()])->all())
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('menu_catalog_item_id', null);
                    $set('item_name_snapshot', null);
                })
                ->required(),
            Select::make('menu_catalog_item_id')
                ->label('Základní položka')
                ->relationship('catalogItem', 'name', fn (Builder $query, Get $get) => $query
                    ->where('menu_catalog_items.is_active', true)
                    ->whereHas('catalogType', fn (Builder $query) => $query->where('slug', match ($get('type')) {
                        MenuItemType::Soup->value => 'polevky',
                        MenuItemType::Pizza->value => 'pizza',
                        MenuItemType::Grill->value => 'grill',
                        default => 'hlavni-jidla',
                    }))
                    ->orderBy('menu_catalog_items.name'))
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function (mixed $state, Set $set): void {
                    $catalogItem = MenuCatalogItem::query()->find($state);

                    if (! $catalogItem instanceof MenuCatalogItem) {
                        return;
                    }

                    $set('item_name_snapshot', $catalogItem->name);
                    $set('price', $catalogItem->default_price);
                    $set('amount', $catalogItem->amount);
                    $set('menu_unit_id', $catalogItem->menu_unit_id);
                })
                ->required(),
            Hidden::make('item_name_snapshot'),
            TextInput::make('price')->label('Cena')->numeric()->step(0.01)->minValue(0)->required(),
            TextInput::make('amount')->label('Množství')->numeric()->step(0.001)->minValue(0),
            Select::make('menu_unit_id')->label('Jednotka')->relationship('unit', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('sort_order'))->searchable()->preload(),
            Group::make([
                Toggle::make('is_available')->label('V nabídce')->default(true),
                Toggle::make('show_on_web')->label('Zobrazit na webu')->default(true),
            ])->columns(2)->columnSpanFull(),
            Repeater::make('sideItems')
                ->label('Přílohy')
                ->relationship()
                ->orderColumn('sort_order')
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                    $data['kind'] = 'side';

                    return $data;
                })
                ->schema([
                    Select::make('menu_catalog_item_id')->label('Příloha')->relationship('catalogItem', 'name', fn (Builder $query) => $query
                        ->where('menu_catalog_items.is_active', true)
                        ->whereHas('catalogType', fn (Builder $query) => $query->where('slug', 'prilohy'))
                        ->orderBy('menu_catalog_items.sort_order')
                        ->orderBy('menu_catalog_items.name'))->searchable()->preload()->required(),
                    Hidden::make('kind')->default('side'),
                    Hidden::make('name_snapshot'),
                ])->columns(1)->columnSpanFull(),
            Repeater::make('otherItems')
                ->label('Ostatní')
                ->relationship()
                ->orderColumn('sort_order')
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                    $data['kind'] = 'other';

                    return $data;
                })
                ->schema([
                    Select::make('menu_catalog_item_id')->label('Položka')->relationship('catalogItem', 'name', fn (Builder $query) => $query
                        ->where('menu_catalog_items.is_active', true)
                        ->whereHas('catalogType', fn (Builder $query) => $query->where('slug', 'omacky-a-ostatni'))
                        ->orderBy('menu_catalog_items.sort_order')
                        ->orderBy('menu_catalog_items.name'))->searchable()->preload()->required(),
                    Hidden::make('kind')->default('other'),
                    Hidden::make('name_snapshot'),
                ])->columns(1)->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('week_start', 'desc')->columns([
            TextColumn::make('branch_name_snapshot')->label('Provozovna')->searchable()->sortable(),
            TextColumn::make('week_start')->label('Týden od')->date('d.m.Y')->sortable(),
            TextColumn::make('week_end')->label('Do')->date('d.m.Y')->sortable(),
            TextColumn::make('status')->label('Stav')->badge()->formatStateUsing(fn (mixed $state): string => static::statusLabel($state)),
        ])->filters([
            SelectFilter::make('restaurant_contact_information_id')->label('Provozovna')->relationship('restaurant', 'business_name'),
        ])->recordActions([
            ViewAction::make(),
            EditAction::make()->visible(fn (BranchMenu $record): bool => $record->isEditable()),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranchMenus::route('/'),
            'view' => Pages\ViewBranchMenu::route('/{record}'),
            'edit' => Pages\EditBranchMenu::route('/{record}/edit'),
        ];
    }

    private static function statusLabel(mixed $state): string
    {
        $status = $state instanceof BranchMenuStatus
            ? $state
            : BranchMenuStatus::tryFrom((string) $state);

        return $status?->label() ?? (string) $state;
    }

    private static function currentUserCanEditMenu(mixed $record): bool
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $record instanceof BranchMenu || ! $record->isEditable()) {
            return false;
        }

        return $user->canManageSharedPlannedMenu()
            || ($user->can('Update:BranchMenu') && $user->managesRestaurant($record->restaurant_contact_information_id));
    }
}
