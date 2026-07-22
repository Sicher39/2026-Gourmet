<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UsesIsolatedTestDatabase;
use Tests\TestCase;

/**
 * Source-level regression assertions for the non-destructive database trait.
 *
 * Verifies that {@see UsesIsolatedTestDatabase} correctly overrides
 * {@see RefreshDatabase::migrateDatabases()} and that the built-in
 * {@see RefreshDatabase} is only used through the custom trait.
 */
class UsesIsolatedTestDatabaseTraitTest extends TestCase
{
    /**
     * The custom trait must declare its own migrateDatabases() method,
     * overriding the one inherited from RefreshDatabase.
     */
    public function test_custom_trait_overrides_migrate_databases(): void
    {
        $trait = new \ReflectionClass(UsesIsolatedTestDatabase::class);

        $this->assertTrue(
            $trait->hasMethod('migrateDatabases'),
            'UsesIsolatedTestDatabase must declare migrateDatabases()'
        );

        $method = $trait->getMethod('migrateDatabases');

        $this->assertSame(
            UsesIsolatedTestDatabase::class,
            $method->getDeclaringClass()->getName(),
            'migrateDatabases() must be declared on UsesIsolatedTestDatabase, '
            .'not inherited from RefreshDatabase'
        );
    }

    /**
     * The custom trait must internally use the built-in RefreshDatabase,
     * preserving its transaction lifecycle and in-memory PDO management.
     */
    public function test_custom_trait_uses_builtin_refresh_database(): void
    {
        $traits = class_uses(UsesIsolatedTestDatabase::class);

        $this->assertContains(
            RefreshDatabase::class,
            $traits,
            'UsesIsolatedTestDatabase must use RefreshDatabase internally'
        );
    }
}
