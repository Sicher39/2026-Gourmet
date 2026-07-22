<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Non-destructive database test trait for the project's isolated in-memory
 * SQLite testing environment.
 *
 * Uses {@see RefreshDatabase} internally to retain Laravel's proven in-memory
 * PDO restoration and per-test transaction lifecycle, but overrides only
 * {@see migrateDatabases()} so that the database is created via {@code migrate}
 * rather than {@code migrate:fresh}.
 *
 * Before invoking the migration, every critical runtime property of the
 * isolated connection is asserted fail-closed via {@see RuntimeException}.
 * The base {@see TestCase} already verifies live PDO integrity before
 * any test body executes; this trait adds a second defensive layer at the
 * moment of schema migration.
 */
trait UsesIsolatedTestDatabase
{
    use RefreshDatabase;

    /**
     * Migrate the database using a non-destructive {@code migrate} command
     * instead of the parent trait's {@code migrate:fresh}.
     *
     * Fail-closed assertions confirm the runtime database configuration
     * exactly matches the expected isolated in-memory SQLite setup before
     * the migration is allowed to proceed.
     *
     *
     * @throws RuntimeException when any safety assertion fails
     */
    protected function migrateDatabases(): void
    {
        $default = config('database.default');

        // Fail-closed: default connection key must be exactly "testing_isolated"
        if ($default !== 'testing_isolated') {
            throw new RuntimeException(
                'UsesIsolatedTestDatabase: expected database.default to be "testing_isolated", got '
                .var_export($default, true)
            );
        }

        $connections = config('database.connections');
        $connectionKeys = array_keys($connections);

        // Fail-closed: only the "testing_isolated" connection key may exist at runtime
        if ($connectionKeys !== ['testing_isolated']) {
            throw new RuntimeException(
                'UsesIsolatedTestDatabase: expected only "testing_isolated" connection key, found: '
                .implode(', ', $connectionKeys)
            );
        }

        $config = $connections['testing_isolated'];

        // Fail-closed: driver must be sqlite
        if (($config['driver'] ?? null) !== 'sqlite') {
            throw new RuntimeException(
                'UsesIsolatedTestDatabase: driver must be "sqlite", got '
                .var_export($config['driver'] ?? null, true)
            );
        }

        // Fail-closed: database must be :memory:
        if (($config['database'] ?? null) !== ':memory:') {
            throw new RuntimeException(
                'UsesIsolatedTestDatabase: database must be ":memory:", got '
                .var_export($config['database'] ?? null, true)
            );
        }

        // Fail-closed: url must be null
        if (($config['url'] ?? null) !== null) {
            throw new RuntimeException(
                'UsesIsolatedTestDatabase: url must be null, got '
                .var_export($config['url'] ?? null, true)
            );
        }

        $this->artisan('migrate', [
            '--database' => $default,
            '--force' => true,
        ]);
    }
}
