<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests;

use Orchestra\Testbench\TestCase as Testbench;
use Seat\Services\ServicesServiceProvider;
use temetvince\SeatCapitals\CapitalsServiceProvider;

/**
 * Base class for tests that need the Laravel container.
 *
 * Boots only `eveseat/services` and this plugin on an in-memory SQLite
 * database holding the plugin's own table plus SQLite stand-ins for the
 * upstream tables it reads (see `tests/database/migrations`). Nothing here
 * reaches Redis, ESI or the network; the cache is the array store and logs
 * are discarded.
 *
 * Framework-free classes are tested with plain PHPUnit under `tests/Unit`
 * and do not extend this class.
 *
 * @package temetvince\SeatCapitals\Tests
 */
abstract class TestCase extends Testbench
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
    }

    /**
     * Providers to boot for every test.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServicesServiceProvider::class,
            CapitalsServiceProvider::class,
        ];
    }

    /**
     * Point the default connection at an in-memory SQLite database and keep
     * every other service local to the process.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('logging.default', 'null');
    }
}
