<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\MenuItemType;
use App\Enums\PlannedMenuStatus;
use App\Models\BranchMenu;
use App\Models\CompanyProfile;
use App\Models\MenuProduct;
use App\Models\NonCookingDay;
use App\Models\PlannedMenu;
use App\Models\RestaurantContactInformation;
use App\Models\User;
use App\Services\Menu\PlannedMenuService;
use Carbon\CarbonImmutable;
use Spatie\Permission\Models\Role;
use Tests\Concerns\UsesIsolatedTestDatabase;
use Tests\TestCase;

class PlannedMenuWorkflowTest extends TestCase
{
    use UsesIsolatedTestDatabase;

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
        $product = MenuProduct::query()->create(['name' => 'Vepřový řízek', 'default_price' => 169, 'is_active' => true]);

        foreach ($plannedMenu->fresh()->days as $day) {
            $day->items()->create([
                'type' => MenuItemType::Main,
                'menu_product_id' => $product->getKey(),
                'default_price' => 169,
                'sort_order' => 1,
            ]);
        }

        $approvedMenu = $service->approve($plannedMenu, $user);

        self::assertSame(PlannedMenuStatus::Approved, $approvedMenu->status);
        self::assertSame(2, BranchMenu::query()->count());
        self::assertSame(10, BranchMenu::query()->withCount('days')->get()->sum('days_count'));
        self::assertSame(10, BranchMenu::query()->with('days.items')->get()->flatMap->days->flatMap->items->count());
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
