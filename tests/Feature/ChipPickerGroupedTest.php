<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\RestaurantReservationSource;
use App\Enums\RestaurantReservationStatus;
use App\Filament\Forms\Components\ChipPicker;
use App\Filament\Restaurant\Resources\RestaurantReservationResource;
use App\Filament\Restaurant\Resources\RestaurantReservationResource\Pages\CreateRestaurantReservation;
use App\Models\RestaurantOpeningHour;
use App\Models\RestaurantOpeningHourSchedule;
use App\Models\RestaurantReservation;
use App\Models\RestaurantReservationSetting;
use App\Models\RestaurantTable;
use App\Models\RestaurantTableZone;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\UsesIsolatedTestDatabase;
use Tests\TestCase;

class ChipPickerGroupedTest extends TestCase
{
    use UsesIsolatedTestDatabase;

    private RestaurantReservationSetting $settings;

    private RestaurantTableZone $zone;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = RestaurantReservationSetting::first()
            ?? RestaurantReservationSetting::create([]);

        $this->settings->update([
            'min_guests' => 1,
            'max_guests' => 20,
            'default_duration_minutes' => 90,
            'buffer_before_minutes' => 30,
            'buffer_after_minutes' => 30,
            'last_minute_cutoff_minutes' => 0,
            'time_slot_interval_minutes' => 30,
            'cancellation_window_hours' => 24,
        ]);

        $this->zone = RestaurantTableZone::create([
            'name' => 'Hlavní sál',
            'is_active' => true,
        ]);

        $schedule = RestaurantOpeningHourSchedule::create([
            'name' => 'Standard',
            'status' => ContentStatus::Published->value,
            'valid_from' => '2020-01-01',
            'valid_to' => null,
        ]);

        RestaurantOpeningHour::create([
            'restaurant_opening_hour_schedule_id' => $schedule->id,
            'days' => [0, 1, 2, 3, 4, 5, 6],
            'open_time' => '08:00',
            'close_time' => '22:00',
            'sort_order' => 0,
        ]);

        $this->user = User::factory()->create();

        // Grant permissions for Filament admin panel access.
        $permissions = [
            'ViewAny:RestaurantReservation',
            'View:RestaurantReservation',
            'Create:RestaurantReservation',
            'Update:RestaurantReservation',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $this->user->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    private function createTable(string $name, int $capacityMax = 4, ?int $zoneId = null): RestaurantTable
    {
        return RestaurantTable::create([
            'name' => $name,
            'restaurant_table_zone_id' => $zoneId ?? $this->zone->id,
            'capacity_min' => 1,
            'capacity_max' => $capacityMax,
            'is_active' => true,
            'sort_order' => 0,
            'priority' => 0,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // ChipPicker fluent API defaults (flat, non-grouped for table picker)
    // ─────────────────────────────────────────────────────────────────────

    public function test_chip_picker_fluent_api_defaults(): void
    {
        // The table picker is now flat (non-grouped), non-searchable, multiple.
        $tablePicker = ChipPicker::make('_table_ids')
            ->searchable(false)
            ->multiple(true);

        $this->assertFalse($tablePicker->isGrouped(), 'Table picker must NOT be grouped.');
        $this->assertFalse($tablePicker->isSearchable());
        $this->assertTrue($tablePicker->isMultiple());
        $this->assertNull($tablePicker->getPlaceholder(), 'Placeholder defaults to null.');

        // Time picker: flat, searchable, with explicit placeholder.
        $timePicker = ChipPicker::make('reservation_time')
            ->searchable(true)
            ->multiple(false)
            ->placeholder('Vyberte čas');

        $this->assertFalse($timePicker->isGrouped());
        $this->assertTrue($timePicker->isSearchable());
        $this->assertFalse($timePicker->isMultiple());
        $this->assertSame('Vyberte čas', $timePicker->getPlaceholder());
    }

    // ─────────────────────────────────────────────────────────────────────
    // Livewire integration: create page renders zone-selection flow
    // ─────────────────────────────────────────────────────────────────────

    public function test_create_page_renders_zone_select_and_flat_table_picker(): void
    {
        $table1 = $this->createTable('Stůl 1', 4);
        $table2 = $this->createTable('Stůl 2', 6);

        $component = Livewire::actingAs($this->user)
            ->test(CreateRestaurantReservation::class);

        // Set date/guests/duration first, then time and zone so
        // afterStateUpdated re-evaluates availability.
        $component
            ->set('data.reservation_date', '2026-08-01')
            ->set('data.duration_minutes', 90)
            ->set('data.guest_count', 4)
            ->set('data.reservation_time', '18:00')
            ->set('data._table_zone_id', (string) $this->zone->id);

        // Both tables from the selected zone should be selectable.
        $component->set('data._table_ids', [$table1->id, $table2->id]);

        $formData = $component->get('data');
        $this->assertContainsEquals($table1->id, $formData['_table_ids']);
        $this->assertContainsEquals($table2->id, $formData['_table_ids']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Flat options tests (zone-scoped)
    // ─────────────────────────────────────────────────────────────────────

    public function test_available_table_options_are_flat_and_scoped_to_zone(): void
    {
        $tableA = $this->createTable('Stůl A', 4);
        $tableB = $this->createTable('Stůl B', 6);

        $zoneB = RestaurantTableZone::create(['name' => 'Terasa', 'is_active' => true]);
        $tableC = RestaurantTable::create([
            'name' => 'Stůl C',
            'restaurant_table_zone_id' => $zoneB->id,
            'capacity_min' => 1,
            'capacity_max' => 4,
            'is_active' => true,
            'sort_order' => 0,
            'priority' => 0,
        ]);

        $get = $this->createGetMock([
            'reservation_date' => '2026-08-01',
            'reservation_time' => '18:00',
            'guest_count' => 4,
            'duration_minutes' => 90,
            '_table_zone_id' => (string) $this->zone->id,
        ]);

        $options = $this->callProtectedStaticMethod(
            RestaurantReservationResource::class,
            'getAvailableTableChipOptions',
            [$get, null],
        );

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        // Only tables from the selected zone should appear.
        $this->assertArrayHasKey($tableA->id, $options);
        $this->assertArrayHasKey($tableB->id, $options);
        $this->assertArrayNotHasKey($tableC->id, $options, 'Table from other zone must be absent.');

        // Labels must be flat table names with capacity.
        $this->assertStringContainsString('Stůl A', $options[$tableA->id]);
        $this->assertStringContainsString('(1-4)', $options[$tableA->id]);
    }

    public function test_bez_zony_sentinel_filters_zoneless_tables(): void
    {
        $tableNoZone = RestaurantTable::create([
            'name' => 'Stůl bez zóny',
            'restaurant_table_zone_id' => null,
            'capacity_min' => 1,
            'capacity_max' => 4,
            'is_active' => true,
            'sort_order' => 0,
            'priority' => 0,
        ]);

        $tableWithZone = $this->createTable('Stůl v zóně', 4);

        $get = $this->createGetMock([
            'reservation_date' => '2026-08-01',
            'reservation_time' => '18:00',
            'guest_count' => 2,
            'duration_minutes' => 90,
            '_table_zone_id' => '__none',
        ]);

        $options = $this->callProtectedStaticMethod(
            RestaurantReservationResource::class,
            'getAvailableTableChipOptions',
            [$get, null],
        );

        $this->assertIsArray($options);
        $this->assertArrayHasKey($tableNoZone->id, $options, 'Zoneless table should appear.');
        $this->assertArrayNotHasKey($tableWithZone->id, $options, 'Zoned table must not appear with __none.');
    }

    public function test_no_zone_selected_returns_empty_options(): void
    {
        $this->createTable('Stůl A', 4);

        $get = $this->createGetMock([
            'reservation_date' => '2026-08-01',
            'reservation_time' => '18:00',
            'guest_count' => 2,
            'duration_minutes' => 90,
            '_table_zone_id' => null,
        ]);

        $options = $this->callProtectedStaticMethod(
            RestaurantReservationResource::class,
            'getAvailableTableChipOptions',
            [$get, null],
        );

        $this->assertIsArray($options);
        $this->assertEmpty($options, 'Empty when no zone is selected.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Selected option labels API
    // ─────────────────────────────────────────────────────────────────────

    public function test_selected_option_labels_api_returns_configured_labels(): void
    {
        $picker = ChipPicker::make('test')
            ->selectedOptionLabels([1 => 'Label 1', 2 => 'Label 2']);

        $this->assertSame(
            [1 => 'Label 1', 2 => 'Label 2'],
            $picker->getSelectedOptionLabels(),
        );
    }

    public function test_selected_option_labels_api_defaults_to_empty_array(): void
    {
        $picker = ChipPicker::make('test');

        $this->assertSame([], $picker->getSelectedOptionLabels());
    }

    // ─────────────────────────────────────────────────────────────────────
    // Legacy zone attribute coexistence regression
    // ─────────────────────────────────────────────────────────────────────

    public function test_tables_with_legacy_zone_attribute_still_returned_in_flat_options(): void
    {
        // Tables with both a legacy 'zone' string and a valid zone FK
        // must still appear in the flat options when their zone is selected.
        $table = RestaurantTable::create([
            'name' => 'Stůl s legacy zónou',
            'restaurant_table_zone_id' => $this->zone->id,
            'zone' => 'Some legacy text',
            'capacity_min' => 1,
            'capacity_max' => 4,
            'is_active' => true,
            'sort_order' => 0,
            'priority' => 0,
        ]);

        $get = $this->createGetMock([
            'reservation_date' => '2026-08-01',
            'reservation_time' => '18:00',
            'guest_count' => 2,
            'duration_minutes' => 90,
            '_table_zone_id' => (string) $this->zone->id,
        ]);

        $options = $this->callProtectedStaticMethod(
            RestaurantReservationResource::class,
            'getAvailableTableChipOptions',
            [$get, null],
        );

        // Must appear in flat options (the legacy 'zone' column is ignored).
        $this->assertArrayHasKey($table->id, $options);
        $this->assertStringContainsString('Stůl s legacy zónou', $options[$table->id]);
    }

    public function test_edit_semantics_exclude_reservation_keeps_own_table_available_with_legacy_zone(): void
    {
        $table = RestaurantTable::create([
            'name' => 'Stůl edit legacy',
            'restaurant_table_zone_id' => $this->zone->id,
            'zone' => 'Legacy free-text zone',
            'capacity_min' => 1,
            'capacity_max' => 4,
            'is_active' => true,
            'sort_order' => 0,
            'priority' => 0,
        ]);

        $reservation = RestaurantReservation::create([
            'starts_at' => '2026-08-01 18:00:00',
            'ends_at' => '2026-08-01 19:30:00',
            'duration_minutes' => 90,
            'guest_count' => 2,
            'status' => RestaurantReservationStatus::Pending->value,
            'source' => RestaurantReservationSource::Admin->value,
            'customer_name' => 'Test Edit',
            'customer_phone' => '+420123456789',
        ]);

        $reservation->tables()->attach($table->id, [
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $get = $this->createGetMock([
            'reservation_date' => '2026-08-01',
            'reservation_time' => '18:00',
            'guest_count' => 2,
            'duration_minutes' => 90,
            '_table_zone_id' => (string) $this->zone->id,
        ]);

        $options = $this->callProtectedStaticMethod(
            RestaurantReservationResource::class,
            'getAvailableTableChipOptions',
            [$get, $reservation],
        );

        $this->assertIsArray($options, 'Options must remain flat (non-grouped).');
        $this->assertArrayHasKey($table->id, $options, 'Own table must remain available via excludeReservationId during edit.');
        $this->assertStringContainsString('Stůl edit legacy', $options[$table->id]);
        $this->assertStringContainsString('(1-4)', $options[$table->id]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Create page: zone change clears table IDs
    // ─────────────────────────────────────────────────────────────────────

    public function test_zone_change_clears_table_ids_on_create_page(): void
    {
        $table = $this->createTable('Stůl 1', 4);

        $component = Livewire::actingAs($this->user)
            ->test(CreateRestaurantReservation::class);

        // Set date/time/guests, select zone, pick a table.
        $component
            ->set('data.reservation_date', '2026-08-01')
            ->set('data.duration_minutes', 90)
            ->set('data.guest_count', 4)
            ->set('data.reservation_time', '18:00')
            ->set('data._table_zone_id', (string) $this->zone->id)
            ->set('data._table_ids', [$table->id]);

        // Confirm table is selected.
        $formData = $component->get('data');
        $this->assertNotEmpty($formData['_table_ids']);

        // Change zone — table IDs must be cleared.
        $zoneB = RestaurantTableZone::create(['name' => 'Terasa', 'is_active' => true]);
        $component->set('data._table_zone_id', (string) $zoneB->id);

        $formData = $component->get('data');
        $this->assertEmpty($formData['_table_ids'], 'Table IDs must be cleared on zone change.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Call a protected static method via reflection.
     *
     * @param  class-string  $class
     * @param  array<int, mixed>  $args
     */
    private function callProtectedStaticMethod(string $class, string $method, array $args = []): mixed
    {
        $ref = new \ReflectionMethod($class, $method);

        return $ref->invoke(null, ...$args);
    }

    /**
     * Create a mock Get callable that returns preconfigured values.
     *
     * Uses Mockery to create a mock that extends the real Get class without
     * calling its constructor.
     *
     * @param  array<string, mixed>  $values
     */
    private function createGetMock(array $values): Get
    {
        $mock = \Mockery::mock(Get::class);
        $mock->shouldReceive('__invoke')
            ->andReturnUsing(function (string|\Stringable $path) use ($values): mixed {
                return $values[(string) $path] ?? null;
            });

        return $mock;
    }
}
