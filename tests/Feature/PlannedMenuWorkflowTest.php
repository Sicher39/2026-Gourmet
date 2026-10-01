<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\MenuItemType;
use App\Enums\PlannedMenuStatus;
use App\Filament\Restaurant\Resources\BranchMenuResource\Pages\EditBranchMenu;
use App\Filament\Restaurant\Resources\PlannedMenuResource\Pages\EditPlannedMenu;
use App\Models\BranchMenu;
use App\Models\CompanyProfile;
use App\Models\MenuCatalogItem;
use App\Models\MenuCatalogType;
use App\Models\NonCookingDay;
use App\Models\PlannedMenu;
use App\Models\RestaurantContactInformation;
use App\Models\User;
use App\Services\Menu\BranchMenuFrontendService;
use App\Services\Menu\PlannedMenuService;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\UsesIsolatedTestDatabase;
use Tests\TestCase;

class PlannedMenuWorkflowTest extends TestCase
{
    use UsesIsolatedTestDatabase;

    public function test_planned_menu_record_title_formats_the_week_without_a_time(): void
    {
        $menu = new PlannedMenu(['week_start' => '2026-10-19', 'week_end' => '2026-10-23']);
        self::assertSame('19. – 23. 10. 2026', $menu->week_period);

        $menu->week_start = '2026-09-28';
        $menu->week_end = '2026-10-02';
        self::assertSame('28. 9. – 2. 10. 2026', $menu->week_period);
    }

    public function test_initialization_snapshots_all_branches_and_marks_non_cooking_days(): void
    {
        [$user, $restaurants] = $this->createPlanningContext();
        NonCookingDay::query()->create(['date' => '2026-06-24', 'created_by' => $user->getKey()]);
        $plannedMenu = PlannedMenu::query()->create([
            'week_start' => '2026-06-22',
            'week_end' => '2026-06-26',
            'status' => PlannedMenuStatus::Draft,
            'created_by' => $user->getKey(),
        ]);

        app(PlannedMenuService::class)->initialize($plannedMenu);

        self::assertCount(2, $plannedMenu->fresh()->branches);
        self::assertCount(5, $plannedMenu->fresh()->days);
        $holiday = $plannedMenu->fresh()->days->first(fn ($day): bool => $day->date->isSameDay(CarbonImmutable::parse('2026-06-24')));
        self::assertNotNull($holiday);
        self::assertTrue($holiday->is_non_cooking_day);
        self::assertSame($restaurants->pluck('business_name')->sort()->values()->all(), $plannedMenu->fresh()->branches->pluck('branch_name_snapshot')->sort()->values()->all());
    }

    public function test_approval_creates_an_independent_menu_for_every_branch(): void
    {
        [$user] = $this->createPlanningContext();
        $plannedMenu = PlannedMenu::query()->create([
            'week_start' => '2026-06-22',
            'week_end' => '2026-06-26',
            'status' => PlannedMenuStatus::Draft,
            'created_by' => $user->getKey(),
        ]);
        $service = app(PlannedMenuService::class);
        $service->initialize($plannedMenu);
        $catalogType = MenuCatalogType::query()->create(['name' => 'Hlavní jídla', 'slug' => 'hlavni-jidla', 'is_active' => true]);
        $catalogItem = MenuCatalogItem::query()->create([
            'menu_catalog_type_id' => $catalogType->getKey(),
            'name' => 'Vepřový řízek',
            'default_price' => 169,
            'is_active' => true,
        ]);

        foreach ($plannedMenu->fresh()->days as $day) {
            $item = $day->items()->create([
                'type' => MenuItemType::Main,
                'menu_catalog_item_id' => $catalogItem->getKey(),
                'default_price' => 169,
                'sort_order' => 1,
            ]);
            $service->createMissingBranchVariants($item);
        }

        $approvedMenu = $service->approve($plannedMenu, $user);

        self::assertSame(PlannedMenuStatus::Approved, $approvedMenu->status);
        self::assertSame(2, BranchMenu::query()->count());
        self::assertSame(10, BranchMenu::query()->withCount('days')->get()->sum('days_count'));
        self::assertSame(10, BranchMenu::query()->with('days.items')->get()->flatMap->days->flatMap->items->count());
    }

    public function test_common_menu_items_are_removed_from_new_holidays_and_published_at_the_end_of_selected_days(): void
    {
        [$user] = $this->createPlanningContext();
        $plannedMenu = PlannedMenu::query()->create([
            'week_start' => '2026-06-22',
            'week_end' => '2026-06-26',
            'status' => PlannedMenuStatus::Draft,
            'created_by' => $user->getKey(),
        ]);
        $service = app(PlannedMenuService::class);
        $service->initialize($plannedMenu);
        $catalogType = MenuCatalogType::query()->create(['name' => 'Hlavní jídla', 'slug' => 'hlavni-jidla', 'is_active' => true]);
        $catalogItem = MenuCatalogItem::query()->create([
            'menu_catalog_type_id' => $catalogType->getKey(),
            'name' => 'Společný řízek',
            'default_price' => 179,
            'is_active' => true,
        ]);
        $commonItem = $plannedMenu->commonItems()->create([
            'type' => MenuItemType::Main,
            'menu_catalog_item_id' => $catalogItem->getKey(),
            'default_price' => 179,
            'sort_order' => 1,
        ]);
        $commonItem->scheduledDays()->sync($plannedMenu->days()->pluck('id'));
        $service->createMissingBranchVariants($commonItem);

        NonCookingDay::query()->create(['date' => '2026-06-24', 'created_by' => $user->getKey()]);

        self::assertCount(4, $commonItem->fresh()->scheduledDays);

        $approvedMenu = $service->approve($plannedMenu->fresh(), $user);
        $commonBranchItems = BranchMenu::query()
            ->with('days.items')
            ->get()
            ->flatMap->days
            ->flatMap->items
            ->where('is_common_menu_item', true);

        $mondayItems = BranchMenu::query()
            ->firstOrFail()
            ->days()
            ->whereDate('date', '2026-06-22')
            ->firstOrFail()
            ->items()
            ->get();

        self::assertSame(PlannedMenuStatus::Approved, $approvedMenu->status);
        self::assertCount(8, $commonBranchItems);
        self::assertSame([true], $commonBranchItems->pluck('is_common_menu_item')->unique()->all());
        self::assertTrue($mondayItems->last()->is_common_menu_item);
    }

    public function test_next_week_web_menu_contains_every_available_published_item_in_order(): void
    {
        [$user, $restaurants] = $this->createPlanningContext();
        $plannedMenu = PlannedMenu::query()->create([
            'week_start' => '2026-10-05',
            'week_end' => '2026-10-09',
            'status' => PlannedMenuStatus::Draft,
            'created_by' => $user->getKey(),
        ]);
        $service = app(PlannedMenuService::class);
        $service->initialize($plannedMenu);
        $catalogType = MenuCatalogType::query()->create(['name' => 'Hlavní jídla', 'slug' => 'hlavni-jidla', 'is_active' => true]);

        $catalogItems = collect(range(1, 6))->mapWithKeys(fn (int $number): array => [
            $number => MenuCatalogItem::query()->create([
                'menu_catalog_type_id' => $catalogType->getKey(),
                'name' => "Jídlo {$number}",
                'default_price' => 169,
                'is_active' => true,
            ]),
        ]);

        foreach ($plannedMenu->days()->orderBy('date')->get() as $day) {
            foreach ($catalogItems as $number => $catalogItem) {
                $item = $day->items()->create([
                    'type' => MenuItemType::Main,
                    'menu_catalog_item_id' => $catalogItem->getKey(),
                    'default_price' => 169,
                    'sort_order' => $number,
                ]);
                $service->createMissingBranchVariants($item);
            }
        }

        $service->approve($plannedMenu, $user);
        $this->travelTo(CarbonImmutable::parse('2026-10-02 12:00:00'));

        try {
            $payload = app(BranchMenuFrontendService::class)->forRestaurant($restaurants->first(), onlyWebVisible: true);
            self::assertCount(5, $payload['upcoming']);
            self::assertSame([1, 2, 3, 4, 5, 6], array_column($payload['upcoming'][0]['menuItems'], 'menuIndex'));
            self::assertSame(['Jídlo 1', 'Jídlo 2', 'Jídlo 3', 'Jídlo 4', 'Jídlo 5', 'Jídlo 6'], array_column($payload['upcoming'][0]['menuItems'], 'foodName'));
            self::assertSame([true, true, true, true, true, true], array_column($payload['upcoming'][0]['menuItems'], 'enabled'));
        } finally {
            $this->travelBack();
        }
    }

    public function test_branch_menu_editor_persists_and_removes_components_without_touching_other_branches(): void
    {
        [$user, $restaurants] = $this->createPlanningContext();
        $plannedMenu = PlannedMenu::query()->create([
            'week_start' => '2026-10-05',
            'week_end' => '2026-10-09',
            'status' => PlannedMenuStatus::Draft,
            'created_by' => $user->getKey(),
        ]);
        $service = app(PlannedMenuService::class);
        $service->initialize($plannedMenu);
        $mainType = MenuCatalogType::query()->create(['name' => 'Hlavní jídla', 'slug' => 'hlavni-jidla', 'is_active' => true]);
        $sideType = MenuCatalogType::query()->create(['name' => 'Přílohy', 'slug' => 'prilohy', 'is_active' => true]);
        $otherType = MenuCatalogType::query()->create(['name' => 'Ostatní', 'slug' => 'omacky-a-ostatni', 'is_active' => true]);
        $main = MenuCatalogItem::query()->create(['menu_catalog_type_id' => $mainType->getKey(), 'name' => 'Hlavní', 'default_price' => 169, 'is_active' => true]);
        $side = MenuCatalogItem::query()->create(['menu_catalog_type_id' => $sideType->getKey(), 'name' => 'Brambory', 'default_price' => 0, 'is_active' => true]);
        $other = MenuCatalogItem::query()->create(['menu_catalog_type_id' => $otherType->getKey(), 'name' => 'Omáčka', 'default_price' => 0, 'is_active' => true]);

        foreach ($plannedMenu->days()->get() as $day) {
            $item = $day->items()->create([
                'type' => MenuItemType::Main,
                'menu_catalog_item_id' => $main->getKey(),
                'default_price' => 169,
                'sort_order' => 1,
            ]);
            $service->createMissingBranchVariants($item);
        }

        $service->approve($plannedMenu, $user);
        $branchMenu = BranchMenu::query()->where('restaurant_contact_information_id', $restaurants->first()->getKey())->firstOrFail();
        $day = $branchMenu->days()->whereDate('date', '2026-10-05')->firstOrFail();
        $item = $day->items()->firstOrFail();
        $itemPath = "data.day_0.record-{$day->getKey()}.items.record-{$item->getKey()}";

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($user)
            ->test(EditBranchMenu::class, ['record' => $branchMenu->getKey()])
            ->set("{$itemPath}.sideItems", [$side->getKey()])
            ->set("{$itemPath}.otherItems", [$other->getKey()])
            ->call('save')
            ->assertHasNoErrors();

        self::assertSame([$side->getKey()], $item->sideItems()->pluck('menu_catalog_item_id')->all());
        self::assertSame([$other->getKey()], $item->otherItems()->pluck('menu_catalog_item_id')->all());
        self::assertSame([], BranchMenu::query()
            ->where('restaurant_contact_information_id', $restaurants->last()->getKey())
            ->firstOrFail()->days()->whereDate('date', '2026-10-05')->firstOrFail()
            ->items()->firstOrFail()->catalogItems()->pluck('menu_catalog_item_id')->all());

        Livewire::actingAs($user)
            ->test(EditBranchMenu::class, ['record' => $branchMenu->getKey()])
            ->set("{$itemPath}.sideItems", [])
            ->call('save')
            ->assertHasNoErrors();

        self::assertSame([], $item->sideItems()->pluck('menu_catalog_item_id')->all());
        self::assertSame([$other->getKey()], $item->otherItems()->pluck('menu_catalog_item_id')->all());
    }

    public function test_branch_manager_sees_names_and_numbers_in_collapsed_planned_menu_items(): void
    {
        [$admin, $restaurants] = $this->createPlanningContext();
        $plannedMenu = PlannedMenu::query()->create([
            'week_start' => '2026-10-19',
            'week_end' => '2026-10-23',
            'status' => PlannedMenuStatus::Draft,
            'created_by' => $admin->getKey(),
        ]);
        app(PlannedMenuService::class)->initialize($plannedMenu);
        $soupType = MenuCatalogType::query()->create(['name' => 'Polévky', 'slug' => 'polevky', 'is_active' => true]);
        $mainType = MenuCatalogType::query()->create(['name' => 'Hlavní jídla', 'slug' => 'hlavni-jidla', 'is_active' => true]);
        $soup = MenuCatalogItem::query()->create(['menu_catalog_type_id' => $soupType->getKey(), 'name' => 'Boršč', 'default_price' => 39, 'is_active' => true]);
        $main = MenuCatalogItem::query()->create(['menu_catalog_type_id' => $mainType->getKey(), 'name' => 'Hovězí guláš', 'default_price' => 169, 'is_active' => true]);
        $day = $plannedMenu->days()->whereDate('date', '2026-10-19')->firstOrFail();
        $day->items()->create(['type' => MenuItemType::Soup, 'menu_catalog_item_id' => $soup->getKey(), 'default_price' => 39, 'sort_order' => 1]);
        $day->items()->create(['type' => MenuItemType::Main, 'menu_catalog_item_id' => $main->getKey(), 'default_price' => 169, 'sort_order' => 2]);

        $manager = User::factory()->create();
        $manager->managedRestaurants()->attach($restaurants->first()->getKey());
        $manager->givePermissionTo(Permission::findOrCreate('Update:PlannedMenu', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($manager)
            ->test(EditPlannedMenu::class, ['record' => $plannedMenu->getKey()])
            ->assertSee('Polévka 1')
            ->assertSee('Boršč')
            ->assertSee('Menu 1')
            ->assertSee('Hovězí guláš');
    }

    private function createPlanningContext(): array
    {
        $role = Role::query()->create(['name' => 'super_admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $company = CompanyProfile::query()->create(['company_name' => 'Gourmet', 'country' => 'CZ']);
        $restaurants = collect([
            RestaurantContactInformation::query()->create(['company_profile_id' => $company->getKey(), 'business_name' => 'Ponávka']),
            RestaurantContactInformation::query()->create(['company_profile_id' => $company->getKey(), 'business_name' => 'Vaňkovka']),
        ]);

        return [$user, $restaurants];
    }
}
