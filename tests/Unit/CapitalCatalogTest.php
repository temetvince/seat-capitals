<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests\Unit;

use PHPUnit\Framework\TestCase;
use temetvince\SeatCapitals\Services\CapitalCatalog;

/**
 * @package temetvince\SeatCapitals\Tests\Unit
 */
class CapitalCatalogTest extends TestCase
{
    public function testGroupIdsAreIntegerKeysInConfigurationOrderWithoutDuplicates(): void
    {
        $configured = (function (): iterable {
            yield '485' => 'Dreadnought';
            yield 30 => 'Titan';
            yield 485 => 'Dreadnought again';
            yield '547' => 'Carrier';
        })();

        $catalog = new CapitalCatalog($configured);

        $this->assertSame([485, 30, 547], $catalog->groupIds());
    }

    public function testEmptyConfigurationYieldsNoGroups(): void
    {
        $catalog = new CapitalCatalog([]);

        $this->assertSame([], $catalog->groupIds());
        $this->assertFalse($catalog->isCapitalType(19720));
        $this->assertCount(0, $catalog->types());
    }
}
