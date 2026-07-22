<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Bootstrap — Pre-Laravel Environment Hardening
|--------------------------------------------------------------------------
|
| This file is loaded by PHPUnit before the Laravel application bootstrap.
| It must never instantiate the application or connect to any database.
|
| Responsibilities:
|  1. Load Composer autoloader via a robust absolute path.
|  2. Refuse to run if a cached application config exists (bootstrap/cache/config.php
|     would bypass our phpunit.xml environment overrides and silently connect
|     to a production database).
|  3. Force safe environment values into every source that Laravel reads
|     (putenv, $_ENV, $_SERVER) so that no code path can accidentally pick up
|     a stale or inherited production value.
|
| After this file completes, phpunit.xml <env> elements and Laravel's own
| .env loading will still run, but the forced values below act as a fail-safe
| floor that cannot be weakened.
*/

// ─────────────────────────────────────────────────────────────────────────
// 1. Composer autoloader (absolute path, workspace-relative)
// ─────────────────────────────────────────────────────────────────────────

$autoloadPath = dirname(__DIR__).'/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    throw new RuntimeException(
        'Test bootstrap: Composer autoloader not found at '.$autoloadPath
    );
}

require_once $autoloadPath;

// ─────────────────────────────────────────────────────────────────────────
// 2. Block cached application config
// ─────────────────────────────────────────────────────────────────────────

$cachedConfigPath = dirname(__DIR__).'/bootstrap/cache/config.php';

if (file_exists($cachedConfigPath)) {
    throw new RuntimeException(
        'Test bootstrap: Cached application config exists at '.$cachedConfigPath.'. '
        .'Cached config bypasses phpunit.xml <env> overrides and may silently '
        .'connect to a production database. Run "php artisan config:clear" first.'
    );
}

// ─────────────────────────────────────────────────────────────────────────
// 3. Force safe environment values
// ─────────────────────────────────────────────────────────────────────────
//
// These are written to every layer that Laravel reads during boot, ensuring
// no code path can inherit a dangerous value even if phpunit.xml <env>
// elements are removed or misconfigured in the future.

$forced = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
];

foreach ($forced as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
