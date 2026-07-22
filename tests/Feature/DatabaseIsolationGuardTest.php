<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseIsolationGuardTest extends TestCase
{
    /**
     * The guard in TestCase::enforceIsolatedDatabaseConfig() already runs
     * fail-closed assertions before this test body executes. These assertions
     * double-check the final resolved state and confirm that non-isolated
     * connection configs are absent.
     */
    public function test_default_connection_is_testing_isolated(): void
    {
        $this->assertSame(
            'testing_isolated',
            config('database.default'),
            'Default connection must be testing_isolated'
        );
    }

    public function test_isolated_connection_driver_is_sqlite(): void
    {
        $this->assertSame(
            'sqlite',
            config('database.connections.testing_isolated.driver'),
            'Isolated connection driver must be sqlite'
        );
    }

    public function test_isolated_connection_database_is_in_memory(): void
    {
        $this->assertSame(
            ':memory:',
            config('database.connections.testing_isolated.database'),
            'Isolated connection database must be :memory:'
        );
    }

    public function test_live_pdo_driver_is_sqlite(): void
    {
        $pdo = DB::connection()->getPdo();

        $this->assertSame(
            'sqlite',
            $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME),
            'Live PDO driver must be sqlite'
        );
    }

    public function test_live_database_is_in_memory(): void
    {
        $rows = DB::select('PRAGMA database_list');

        $this->assertNotEmpty($rows, 'PRAGMA database_list must return at least one row');

        foreach ($rows as $row) {
            $this->assertSame(
                '',
                $row->file ?? '',
                'Every SQLite database file must be empty string (in-memory)'
            );
        }
    }

    public function test_pgsql_connection_config_is_unavailable(): void
    {
        $this->assertNull(
            config('database.connections.pgsql'),
            'pgsql connection config must be null — only testing_isolated is allowed'
        );
    }

    public function test_only_testing_isolated_connection_key_exists(): void
    {
        $this->assertSame(
            ['testing_isolated'],
            array_keys(config('database.connections')),
            'Only the testing_isolated connection key may exist'
        );
    }
}
