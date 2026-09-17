<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\MenuItemType;
use App\Enums\PlannedMenuStatus;
use App\Filament\Restaurant\Resources\BranchMenuResource;
use App\Filament\Restaurant\Resources\PlannedMenuResource;
use App\Models\BranchMenu;
use App\Models\BranchMenuItem;
use App\Models\BranchMenuItemCatalogItem;
use App\Models\MenuCatalogItem;
use App\Models\MenuCatalogType;
use App\Models\PlannedMenu;
use App\Models\RestaurantContactInformation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Nástěnka';

    protected string $view = 'filament.pages.dashboard';

    public string $greeting = 'Dobrý den';

    public string $todayLabel = '';

    /** @var array<string, mixed> */
    public array $nextWeekMenu = [];

    /** @var array<int, array<string, mixed>> */
    public array $todayMenus = [];

    /** @var array<int, array<string, mixed>> */
    public array $catalogStats = [];

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    public function editMenuItemAction(): Action
    {
        return Action::make('editMenuItem')
            ->modalHeading(function (array $arguments): string {
                $item = $this->menuItemForEditing($arguments);

                return 'Upravit '.($item->catalogItem?->name ?? $item->item_name_snapshot);
            })
            ->modalDescription('Změny se projeví pouze v dnešním jídelním lístku této provozovny.')
            ->modalSubmitActionLabel('Uložit změny')
            ->modalWidth(Width::TwoExtraLarge)
            ->fillForm(function (array $arguments): array {
                $item = $this->menuItemForEditing($arguments);

                return [
                    'is_available' => $item->is_available,
                    'show_on_web' => $item->show_on_web,
                    'side_item_ids' => $item->sideItems()->pluck('menu_catalog_item_id')->all(),
                    'other_item_ids' => $item->otherItems()->pluck('menu_catalog_item_id')->all(),
                ];
            })
            ->schema([
                Section::make('Dostupnost')
                    ->description('Položku můžete stáhnout z nabídky nebo ji ponechat jen pro obsluhu.')
                    ->schema([
                        Toggle::make('is_available')
                            ->label('Položka je v nabídce')
                            ->helperText('Vypněte po vyprodání.'),
                        Toggle::make('show_on_web')
                            ->label('Zobrazit na internetu')
                            ->helperText('Vypněte, pokud položka nemá být vidět na webu.'),
                    ])
                    ->columns(2),
                Section::make('Přílohy a ostatní součásti')
                    ->description('Zvolené položky nahradí současné přílohy pouze u tohoto jídla a této provozovny.')
                    ->schema([
                        Select::make('side_item_ids')
                            ->label('Přílohy')
                            ->options(fn (): array => $this->componentOptions('prilohy'))
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Select::make('other_item_ids')
                            ->label('Omáčky a ostatní')
                            ->options(fn (): array => $this->componentOptions('omacky-a-ostatni'))
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
            ])
            ->action(function (array $arguments, array $data): void {
                $item = $this->menuItemForEditing($arguments);
                $item->update([
                    'is_available' => (bool) $data['is_available'],
                    'show_on_web' => (bool) $data['show_on_web'],
                ]);

                $this->syncComponentType($item, $data['side_item_ids'] ?? [], 'side', 'prilohy');
                $this->syncComponentType($item, $data['other_item_ids'] ?? [], 'other', 'omacky-a-ostatni');
                $this->loadDashboardData();

                Notification::make()
                    ->success()
                    ->title('Položka byla upravena')
                    ->body('Dnešní nabídka provozovny je aktuální.')
                    ->send();
            })
            ->databaseTransaction();
    }

    private function loadDashboardData(): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $now = CarbonImmutable::now();
        $today = $now->startOfDay();
        $this->greeting = $this->greetingFor($now);
        $this->todayLabel = Str::ucfirst($today->locale('cs')->translatedFormat('l j. F Y'));

        $restaurants = $this->restaurantsFor($user);
        $this->nextWeekMenu = $this->nextWeekMenuFor($user, $today->startOfWeek()->addWeek());
        $this->todayMenus = $this->todayMenusFor($user, $restaurants, $today);
        $this->catalogStats = $this->catalogStats();
    }

    /** @return array<string, mixed> */
    private function nextWeekMenuFor(User $user, CarbonImmutable $weekStart): array
    {
        $plannedMenu = PlannedMenu::query()
            ->whereDate('week_start', $weekStart)
            ->first();

        if (! $plannedMenu instanceof PlannedMenu) {
            return [
                'title' => 'Menu není naplánované',
                'description' => 'Pro příští týden zatím nebyl založen plánovaný jídelní lístek.',
                'period' => $this->weekPeriod($weekStart),
                'color' => 'danger',
                'actionLabel' => $user->can('create', PlannedMenu::class) ? 'Naplánovat menu' : null,
                'actionUrl' => $user->can('create', PlannedMenu::class) ? PlannedMenuResource::getUrl('create') : null,
            ];
        }

        $canEdit = $plannedMenu->status === PlannedMenuStatus::Draft && $user->can('update', $plannedMenu);
        $canView = $user->can('view', $plannedMenu);

        return [
            'title' => $plannedMenu->status === PlannedMenuStatus::Approved
                ? 'Menu je naplánované'
                : 'Menu je rozpracované',
            'description' => $plannedMenu->status === PlannedMenuStatus::Approved
                ? 'Plán byl odsouhlasený a jídelní lístky provozoven jsou připravené.'
                : 'Plán existuje, ale ještě čeká na dokončení a odsouhlasení.',
            'period' => $this->weekPeriod($weekStart),
            'color' => $plannedMenu->status === PlannedMenuStatus::Approved ? 'success' : 'warning',
            'actionLabel' => $canEdit ? 'Pokračovat v plánování' : ($canView ? 'Zobrazit plán' : null),
            'actionUrl' => $canEdit
                ? PlannedMenuResource::getUrl('edit', ['record' => $plannedMenu])
                : ($canView ? PlannedMenuResource::getUrl('view', ['record' => $plannedMenu]) : null),
        ];
    }

    /**
     * @param  Collection<int, RestaurantContactInformation>  $restaurants
     * @return array<int, array<string, mixed>>
     */
    private function todayMenusFor(User $user, Collection $restaurants, CarbonImmutable $today): array
    {
        $branchMenus = BranchMenu::query()
            ->whereIn('restaurant_contact_information_id', $restaurants->modelKeys())
            ->whereDate('week_start', '<=', $today)
            ->whereDate('week_end', '>=', $today)
            ->with([
                'days' => fn (HasMany $query): HasMany => $query
                    ->whereDate('date', $today)
                    ->with([
                        'items.catalogItem:id,name',
                        'items.unit:id,symbol',
                        'items.catalogItems.catalogItem:id,name',
                    ]),
            ])
            ->get()
            ->keyBy('restaurant_contact_information_id');

        return $restaurants
            ->map(function (RestaurantContactInformation $restaurant) use ($branchMenus, $today, $user): array {
                $branchMenu = $branchMenus->get($restaurant->getKey());

                if (! $branchMenu instanceof BranchMenu) {
                    return [
                        'restaurantId' => $restaurant->getKey(),
                        'restaurantName' => $restaurant->business_name,
                        'menuExists' => false,
                        'message' => $today->isWeekend()
                            ? 'Na dnešní víkendový den se běžné denní menu neplánuje.'
                            : 'Pro dnešní den není vytvořený jídelní lístek provozovny.',
                        'items' => [],
                        'summary' => ['soups' => 0, 'mains' => 0, 'available' => 0],
                        'actionLabel' => $user->can('viewAny', BranchMenu::class) ? 'Přejít na jídelní lístky' : null,
                        'actionUrl' => $user->can('viewAny', BranchMenu::class) ? BranchMenuResource::getUrl('index') : null,
                    ];
                }

                $day = $branchMenu->days->first();
                $items = $day?->items ?? collect();
                $canEdit = $user->can('update', $branchMenu);
                $canView = $user->can('view', $branchMenu);

                return [
                    'restaurantId' => $restaurant->getKey(),
                    'restaurantName' => $restaurant->business_name,
                    'menuExists' => true,
                    'isNonCookingDay' => $day?->is_non_cooking_day === true,
                    'message' => $day?->is_non_cooking_day === true
                        ? 'Dnešek je označený jako den, kdy se nevaří.'
                        : ($items->isEmpty() ? 'Dnešní jídelní lístek zatím neobsahuje žádné položky.' : null),
                    'status' => $branchMenu->status->label(),
                    'items' => $this->menuItemsData($items, $canEdit),
                    'summary' => [
                        'soups' => $items->where('type', MenuItemType::Soup)->count(),
                        'mains' => $items->whereIn('type', [MenuItemType::Main, MenuItemType::Pizza, MenuItemType::Grill])->count(),
                        'available' => $items->where('is_available', true)->count(),
                    ],
                    'actionLabel' => $canEdit ? 'Upravit dnešní menu' : ($canView ? 'Zobrazit jídelní lístek' : null),
                    'actionUrl' => $canEdit
                        ? BranchMenuResource::getUrl('edit', ['record' => $branchMenu])
                        : ($canView ? BranchMenuResource::getUrl('view', ['record' => $branchMenu]) : null),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, BranchMenuItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private function menuItemsData(Collection $items, bool $canEdit): array
    {
        $typeNumbers = [
            MenuItemType::Soup->value => 0,
            MenuItemType::Main->value => 0,
        ];

        return $items
            ->map(function (BranchMenuItem $item) use (&$typeNumbers, $canEdit): array {
                $typeNumber = null;

                if (array_key_exists($item->type->value, $typeNumbers)) {
                    $typeNumber = ++$typeNumbers[$item->type->value];
                }

                return $this->menuItemData($item, $canEdit, $typeNumber);
            })
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function menuItemData(BranchMenuItem $item, bool $canEdit, ?int $typeNumber): array
    {
        $componentName = fn (BranchMenuItemCatalogItem $component): ?string => $component->catalogItem?->name
            ?? $component->name_snapshot;
        $sideItems = $item->catalogItems
            ->where('kind', 'side')
            ->map($componentName)
            ->filter()
            ->values()
            ->all();
        $otherItems = $item->catalogItems
            ->where('kind', 'other')
            ->map($componentName)
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $item->getKey(),
            'type' => $item->type->value,
            'typeLabel' => $typeNumber === null
                ? $item->type->label()
                : $item->type->label().' '.$typeNumber,
            'name' => $item->catalogItem?->name ?? $item->item_name_snapshot,
            'sideItems' => $sideItems,
            'otherItems' => $otherItems,
            'amount' => $this->formattedAmount($item),
            'price' => $this->formattedPrice($item),
            'isAvailable' => $item->is_available,
            'showOnWeb' => $item->show_on_web,
            'canEdit' => $canEdit,
        ];
    }

    /** @param array<string, mixed> $arguments */
    private function menuItemForEditing(array $arguments): BranchMenuItem
    {
        $item = BranchMenuItem::query()
            ->with(['catalogItem:id,name', 'day.branchMenu'])
            ->find($arguments['item'] ?? null);
        $user = auth()->user();
        $branchMenu = $item?->day?->branchMenu;
        $isToday = $item?->day?->date?->isToday() === true;

        abort_unless(
            $item instanceof BranchMenuItem
                && $user instanceof User
                && $branchMenu instanceof BranchMenu
                && $isToday
                && $user->can('update', $branchMenu),
            403,
        );

        return $item;
    }

    /** @return array<int, string> */
    private function componentOptions(string $catalogTypeSlug): array
    {
        return MenuCatalogItem::query()
            ->where('is_active', true)
            ->whereHas('catalogType', fn (Builder $query): Builder => $query->where('slug', $catalogTypeSlug))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /** @param array<int|string, mixed> $catalogItemIds */
    private function syncComponentType(
        BranchMenuItem $item,
        array $catalogItemIds,
        string $kind,
        string $catalogTypeSlug,
    ): void {
        $allowedCatalogItemIds = MenuCatalogItem::query()
            ->whereKey(array_values(array_filter($catalogItemIds, 'is_numeric')))
            ->where('is_active', true)
            ->whereHas('catalogType', fn (Builder $query): Builder => $query->where('slug', $catalogTypeSlug))
            ->pluck('id')
            ->map(fn (int|string $id): int => (int) $id)
            ->all();

        $item->catalogItems()->where('kind', $kind)->delete();

        foreach ($allowedCatalogItemIds as $sortOrder => $catalogItemId) {
            $item->catalogItems()->create([
                'menu_catalog_item_id' => $catalogItemId,
                'kind' => $kind,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function catalogStats(): array
    {
        $icons = [
            'polevky' => 'heroicon-o-fire',
            'hlavni-jidla' => 'heroicon-o-sparkles',
            'pizza' => 'heroicon-o-chart-pie',
            'grill' => 'heroicon-o-bolt',
        ];

        return MenuCatalogType::query()
            ->where('is_active', true)
            ->whereIn('slug', array_keys($icons))
            ->withCount([
                'catalogItems as active_items_count' => fn (Builder $query): Builder => $query->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (MenuCatalogType $type): array => [
                'name' => $type->name,
                'count' => $type->active_items_count,
                'icon' => $icons[$type->slug] ?? 'heroicon-o-square-3-stack-3d',
            ])
            ->all();
    }

    /** @return Collection<int, RestaurantContactInformation> */
    private function restaurantsFor(User $user): Collection
    {
        if ($user->canManageSharedPlannedMenu()) {
            return RestaurantContactInformation::query()
                ->orderBy('business_name')
                ->get();
        }

        return $user->managedRestaurants()
            ->orderBy('business_name')
            ->get();
    }

    private function greetingFor(CarbonImmutable $now): string
    {
        return match (true) {
            $now->hour >= 5 && $now->hour < 11 => 'Dobré ráno',
            $now->hour >= 18 => 'Dobrý večer',
            default => 'Dobrý den',
        };
    }

    private function weekPeriod(CarbonImmutable $weekStart): string
    {
        return $weekStart->format('j. n.').' – '.$weekStart->addDays(4)->format('j. n. Y');
    }

    private function formattedAmount(BranchMenuItem $item): ?string
    {
        if ($item->amount === null) {
            return null;
        }

        $amount = rtrim(rtrim(number_format((float) $item->amount, 3, ',', ''), '0'), ',');
        $unit = $item->unit?->symbol ?? $item->unit_symbol_snapshot;

        return trim($amount.' '.$unit);
    }

    private function formattedPrice(BranchMenuItem $item): ?string
    {
        if ($item->price === null) {
            return null;
        }

        $price = (float) $item->price;
        $decimals = floor($price) === $price ? 0 : 2;

        return number_format($price, $decimals, ',', ' ').' Kč';
    }
}
