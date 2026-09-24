<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals;

use Seat\Services\AbstractSeatPlugin;
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Observers\CapitalApplicationObserver;
use temetvince\SeatCapitals\Services\CapitalCatalog;

/**
 * Registers the plugin with SeAT.
 *
 * Laravel discovers this provider through the `extra.laravel.providers` entry
 * in composer.json. `register()` runs before any provider boots and only merges
 * configuration and binds services. `boot()` runs once every provider is
 * registered and wires the plugin's views, translations, migrations, routes
 * and model observer into the host.
 *
 * Names owned by this plugin: views and translations under `seat-capitals::`,
 * route names under `seat-capitals::`, permissions `capitals.apply`,
 * `capitals.review` and `character.capitals`, the config key `seat-capitals`,
 * notification alerts prefixed `seat_capitals_`, and database tables prefixed
 * `seat_capitals_`.
 *
 * @package temetvince\SeatCapitals
 */
class CapitalsServiceProvider extends AbstractSeatPlugin
{
    /**
     * The view and translation namespace this plugin loads into.
     */
    public const NAMESPACE = 'seat-capitals';

    /**
     * The permission scope of the plugin's own permissions.
     */
    public const PERMISSION_SCOPE = 'capitals';

    /**
     * The config key under which `Config/seat-capitals.config.php` is merged.
     */
    public const CONFIG = 'seat-capitals';

    /**
     * Bootstrap the plugin once every provider has been registered.
     *
     * Postcondition: the `seat-capitals::` view and translation namespaces
     * resolve, the plugin's migrations are known to the migrator, its routes
     * are registered unless the host serves cached routes, its config is
     * publishable, and application changes raise notifications.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', self::NAMESPACE);
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', self::NAMESPACE);
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if (! $this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }

        $this->publishes([
            __DIR__ . '/Config/seat-capitals.config.php' => config_path(self::CONFIG . '.php'),
        ], [self::NAMESPACE, 'config']);

        CapitalApplication::observe(CapitalApplicationObserver::class);
    }

    /**
     * Merge the plugin's configuration into the host and bind its services.
     *
     * Only configuration and bindings are touched here because other
     * providers may not have been registered yet.
     */
    public function register(): void
    {
        $this->registerPermissions(__DIR__ . '/Config/Permissions/capitals.php', self::PERMISSION_SCOPE);
        $this->registerPermissions(__DIR__ . '/Config/Permissions/character.php', 'character');

        $this->mergeConfigFrom(__DIR__ . '/Config/package.sidebar.php', 'package.sidebar');
        $this->mergeConfigFrom(__DIR__ . '/Config/seat-capitals.config.php', self::CONFIG);
        $this->mergeConfigFrom(__DIR__ . '/Config/notifications.alerts.php', 'notifications.alerts');

        $this->app->singleton(CapitalCatalog::class, function () {
            $groups = config(self::CONFIG . '.capital_groups', []);

            return new CapitalCatalog(is_iterable($groups) ? $groups : []);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'SeAT Capitals';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): ?string
    {
        return 'Capital build applications and a capital ship report for SeAT.';
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/temetvince/seat-capitals';
    }

    /**
     * {@inheritdoc}
     */
    public function getPackagistPackageName(): string
    {
        return 'seat-capitals';
    }

    /**
     * {@inheritdoc}
     */
    public function getPackagistVendorName(): string
    {
        return 'temetvince';
    }
}
