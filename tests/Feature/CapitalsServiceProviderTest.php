<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Factory;
use temetvince\SeatCapitals\Acl\ReportPolicy;
use temetvince\SeatCapitals\CapitalsServiceProvider;
use temetvince\SeatCapitals\Services\CapitalCatalog;
use temetvince\SeatCapitals\Tests\TestCase;

/**
 * Verifies that the provider registers everything SeAT expects from a plugin.
 *
 * @package temetvince\SeatCapitals\Tests\Feature
 */
class CapitalsServiceProviderTest extends TestCase
{
    public function testPluginPermissionsAreRegisteredUnderTheCapitalsScope(): void
    {
        $permissions = config('seat.permissions.capitals');

        $this->assertIsArray($permissions);
        $this->assertSame(['apply', 'review'], array_keys($permissions));

        foreach ($permissions as $permission) {
            $this->assertNotSame($permission['label'], trans($permission['label']), 'permission label must translate');
            $this->assertNotSame($permission['description'], trans($permission['description']), 'permission description must translate');
        }
    }

    public function testReportPermissionJoinsTheCharacterScopeWithACustomGate(): void
    {
        $permission = config('seat.permissions.character.capitals');

        $this->assertIsArray($permission);
        $this->assertSame(ReportPolicy::class, $permission['gate']);
        $this->assertNotSame($permission['label'], trans($permission['label']));
    }

    public function testSidebarGroupIsMergedUnderItsOwnKey(): void
    {
        $group = config('package.sidebar.seat-capitals');

        $this->assertIsArray($group);
        $this->assertArrayNotHasKey('permission', $group, 'the group itself must stay visible to every entry holder');
        $this->assertCount(4, $group['entries']);

        foreach ($group['entries'] as $entry) {
            $this->assertTrue(Route::has($entry['route']), "Sidebar entry route {$entry['route']} is not registered.");
            $this->assertContains($entry['permission'], ['capitals.apply', 'capitals.review', 'character.capitals']);
            $this->assertNotSame($entry['label'], trans($entry['label']));
        }
    }

    public function testRoutesAreGatedByTheirAreaPermission(): void
    {
        $expected = [
            'seat-capitals::index' => 'can:capitals.apply',
            'seat-capitals::applications.index' => 'can:capitals.apply',
            'seat-capitals::applications.store' => 'can:capitals.apply',
            'seat-capitals::applications.withdraw' => 'can:capitals.apply',
            'seat-capitals::review.index' => 'can:capitals.review',
            'seat-capitals::review.decide' => 'can:capitals.review',
            'seat-capitals::settings.index' => 'can:capitals.review',
            'seat-capitals::settings.update' => 'can:capitals.review',
            'seat-capitals::report.index' => 'can:character.capitals',
        ];

        foreach ($expected as $name => $gate) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Route {$name} is not registered.");
            $this->assertStringStartsWith('capitals', $route->uri());
            $this->assertContains('auth', $route->gatherMiddleware(), "Route {$name} must require a signed-in user.");
            $this->assertContains($gate, $route->gatherMiddleware(), "Route {$name} must be gated by {$gate}.");
        }
    }

    public function testNotificationAlertsAreMergedWithAllThreeChannels(): void
    {
        foreach (['seat_capitals_application_created', 'seat_capitals_application_decided'] as $alert) {
            $definition = config('notifications.alerts.' . $alert);

            $this->assertIsArray($definition, "Alert {$alert} is not registered.");
            $this->assertSame(['discord', 'slack', 'mail'], array_keys($definition['handlers']));
            $this->assertNotSame($definition['label'], trans($definition['label']));

            foreach ($definition['handlers'] as $handler) {
                $this->assertTrue(class_exists($handler), "Handler {$handler} does not exist.");
            }
        }
    }

    public function testCatalogIsBoundFromTheMergedConfig(): void
    {
        $catalog = $this->app->make(CapitalCatalog::class);

        $this->assertSame($catalog, $this->app->make(CapitalCatalog::class), 'the catalog is a singleton');
        $this->assertContains(485, $catalog->groupIds(), 'dreadnoughts are capitals by default');
        $this->assertContains(1538, $catalog->groupIds(), 'force auxiliaries are capitals by default');
    }

    public function testViewsResolveThroughThePluginNamespace(): void
    {
        $factory = $this->app->make(Factory::class);

        foreach (['applications.index', 'review.index', 'report.index', 'settings.index', 'partials.status', 'partials.actions'] as $view) {
            $this->assertFileExists($factory->getFinder()->find('seat-capitals::' . $view));
        }
    }

    public function testPluginMetadataMatchesComposerJson(): void
    {
        $provider = $this->app->getProvider(CapitalsServiceProvider::class);

        $this->assertInstanceOf(CapitalsServiceProvider::class, $provider);
        $this->assertSame('temetvince/seat-capitals', $provider->getPackagistAlias());
        $this->assertSame('SeAT Capitals', $provider->getName());
    }
}
