<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refresh the application instance, then enforce fail-closed database isolation
     * before any traits (such as RefreshDatabase) can access the database.
     *
     * The guard replaces the application's database configuration with a single
     * isolated SQLite :memory: connection, purges any stale resolved connections,
     * and asserts every critical property before allowing the test to proceed.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $this->enforceIsolatedDatabaseConfig();
    }

    /**
     * Fail-closed database safety guard for the testing environment.
     *
     * 1. Validates APP_ENV is "testing".
     * 2. Replaces the entire database.connections config with a single
     *    "testing_isolated" SQLite :memory: entry.
     * 3. Sets database.default to "testing_isolated".
     * 4. Purges every previously resolved connection from the DB manager.
     * 5. Asserts config integrity (default, driver, database, url, connection keys).
     * 6. Resolves a live PDO connection and asserts it is SQLite in-memory.
     *
     * @throws RuntimeException on any mismatch
     */
    protected function enforceIsolatedDatabaseConfig(): void
    {
        $app = $this->app;

        // --- Fail-closed: application environment must be testing ---
        $env = $app['config']->get('app.env');
        if ($env !== 'testing') {
            throw new RuntimeException(
                'Database safety guard: expected APP_ENV=testing, got '.var_export($env, true)
            );
        }

        // --- Override config: only the isolated connection is allowed ---
        $app['config']->set('database.default', 'testing_isolated');
        $app['config']->set('database.connections', [
            'testing_isolated' => [
                'driver' => 'sqlite',
                'url' => null,
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        // --- Purge every previously resolved connection from the DB manager ---
        $db = $app['db'];

        foreach ($db->getConnections() as $name => $_connection) {
            $db->purge($name);
        }

        // Ensure the DatabaseManager's default connection pointer is updated
        $db->setDefaultConnection('testing_isolated');

        // --- Fail-closed: config integrity assertions ---

        $default = $app['config']->get('database.default');
        if ($default !== 'testing_isolated') {
            throw new RuntimeException(
                "Database safety guard: default connection must be 'testing_isolated', got ".var_export($default, true)
            );
        }

        $connections = $app['config']->get('database.connections');
        $connectionKeys = array_keys($connections);
        if ($connectionKeys !== ['testing_isolated']) {
            throw new RuntimeException(
                'Database safety guard: only connection allowed is testing_isolated, found: '
                .implode(', ', $connectionKeys)
            );
        }

        $config = $connections['testing_isolated'];

        if (($config['driver'] ?? null) !== 'sqlite') {
            throw new RuntimeException(
                'Database safety guard: driver must be sqlite, got '
                .var_export($config['driver'] ?? null, true)
            );
        }

        if (($config['database'] ?? null) !== ':memory:') {
            throw new RuntimeException(
                'Database safety guard: database must be :memory:, got '
                .var_export($config['database'] ?? null, true)
            );
        }

        if (($config['url'] ?? null) !== null) {
            throw new RuntimeException(
                'Database safety guard: url must be null, got '
                .var_export($config['url'] ?? null, true)
            );
        }

        // --- Fail-closed: live PDO connection integrity ---
        $pdo = $db->connection()->getPdo();

        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if ($driverName !== 'sqlite') {
            throw new RuntimeException(
                "Database safety guard: PDO driver must be sqlite, got {$driverName}"
            );
        }

        // Verify in-memory via SQLite PRAGMA — does not touch any application table
        $rows = $db->select('PRAGMA database_list');

        if (empty($rows)) {
            throw new RuntimeException(
                'Database safety guard: PRAGMA database_list returned no rows — connection may be invalid'
            );
        }

        foreach ($rows as $row) {
            if (($row->file ?? '') !== '') {
                throw new RuntimeException(
                    'Database safety guard: attached database has non-empty file path: '
                    .var_export($row->file, true)
                );
            }
        }
    }
}
