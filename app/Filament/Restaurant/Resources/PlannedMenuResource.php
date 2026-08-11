<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuItemType;
use App\Filament\Restaurant\Resources\PlannedMenuResource\Pages;
use App\Models\MenuProduct;
use App\Models\NonCookingDay;
use App\Models\PlannedMenu;
use App\Models\PlannedMenuBranch;
use App\Models\PlannedMenuDay;
use App\Models\PlannedMenuItemBranch;
use App\Models\User;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

    private static function dayTab(string $label, int $offset): Tab
    {
        return Tab::make($label)
            ->schema([
                Repeater::make("day_{$offset}")
                    ->hiddenLabel()
                    ->relationship(
                        name: 'days',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->orderBy('planned_menu_days.date')
                            ->offset($offset)
                            ->limit(1),
                    )
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->itemHeaders(false)
                    ->schema([
                        Hidden::make('date'),
                        TextInput::make('date_display')
                            ->label('Datum')
                            ->formatStateUsing(fn ($state, ?PlannedMenuDay $record): string => $record?->date?->format('d.m.Y') ?? '')
                            ->dehydrated(false)
                            ->disabled(),
                        Toggle::make('is_non_cooking_day')
                            ->label('Tento den se nevaří')
                            ->disabled(),
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
                            ->relationship()
                            ->hidden(fn (?PlannedMenuDay $record): bool => $record?->is_non_cooking_day === true)
                            ->orderColumn('sort_order')
                            ->addActionLabel('Přidat polévku nebo menu')
                            ->addable(fn (): bool => static::currentUserCanManageShared())
                            ->deletable(fn (): bool => static::currentUserCanManageShared())
                            ->reorderable(fn (): bool => static::currentUserCanManageShared())
                            ->itemLabel(function (array $state): string {
                                $type = MenuItemType::tryFrom((string) ($state['type'] ?? ''))?->label() ?? 'Položka';
                                $product = isset($state['menu_product_id'])
                                    ? MenuProduct::query()->find($state['menu_product_id'])?->name
                                    : null;

                                return $product ? $type.': '.$product : $type;
                            })
                            ->schema(static::menuItemSchema())
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<int, mixed> */
    private static function menuItemSchema(): array
    {
        return [
            Select::make('type')
                ->label('Typ')
                ->options(collect(MenuItemType::cases())->mapWithKeys(fn (MenuItemType $type): array => [$type->value => $type->label()])->all())
                ->required()
                ->disabled(fn (): bool => ! static::currentUserCanManageShared()),
            Select::make('menu_product_id')
                ->label('Hlavní jídlo / polévka')
                ->relationship('product', 'name', fn (Builder $query) => $query->where('menu_products.is_active', true)->orderBy('menu_products.name'))
                ->searchable()
                ->preload()
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
                    ? 'Provozovna – '.((string) PlannedMenuBranch::query()->find($state['planned_menu_branch_id'])?->branch_name_snapshot)
                    : 'Provozovna')
                ->schema([
                    Hidden::make('planned_menu_branch_id'),
                    TextInput::make('price')
                        ->label('Cena')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->required()
                        ->disabled(fn (?PlannedMenuItemBranch $record): bool => ! static::currentUserCanEditVariant($record)),
                    TextInput::make('amount')
                        ->label('Množství')
                        ->numeric()
                        ->step(0.001)
                        ->minValue(0)
                        ->disabled(fn (?PlannedMenuItemBranch $record): bool => ! static::currentUserCanEditVariant($record)),
                    Select::make('menu_unit_id')
                        ->label('Jednotka')
                        ->relationship('unit', 'name', fn (Builder $query) => $query->where('menu_units.is_active', true)->orderBy('menu_units.sort_order'))
                        ->searchable()
                        ->preload()
                        ->disabled(fn (?PlannedMenuItemBranch $record): bool => ! static::currentUserCanEditVariant($record)),
                    Select::make('catalogItems')
                        ->label('Přílohy, omáčky a další komponenty')
                        ->relationship('catalogItems', 'name', fn (Builder $query) => $query
                            ->where('menu_catalog_items.is_active', true)
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
