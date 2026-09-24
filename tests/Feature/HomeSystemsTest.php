<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests\Feature;

use temetvince\SeatCapitals\Settings\HomeSystems;
use temetvince\SeatCapitals\Tests\Fixtures;
use temetvince\SeatCapitals\Tests\TestCase;

/**
 * @package temetvince\SeatCapitals\Tests\Feature
 */
class HomeSystemsTest extends TestCase
{
    use Fixtures;

    private HomeSystems $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeSystem(30000142, 'Jita', 0.95);
        $this->makeSystem(31000001, 'J123456', -0.99);

        $this->settings = new HomeSystems();
    }

    public function testNothingIsConfiguredByDefault(): void
    {
        $this->assertSame([], $this->settings->ids());
        $this->assertCount(0, $this->settings->systems());
    }

    public function testStoreKeepsUniqueNumericIdsAndSystemsComeBackByName(): void
    {
        $this->settings->store(['31000001', 30000142, 30000142, 'not-a-system', 42]);

        $this->assertSame([31000001, 30000142, 42], $this->settings->ids());
        $this->assertSame(['J123456', 'Jita'], $this->settings->systems()->pluck('name')->all());
    }

    public function testStoreReplacesThePreviousList(): void
    {
        $this->settings->store([30000142]);
        $this->settings->store([]);

        $this->assertSame([], $this->settings->ids());
    }
}
