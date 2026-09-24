<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests\Feature;

use temetvince\SeatCapitals\Services\CapitalFleetQuery;
use temetvince\SeatCapitals\Tests\Fixtures;
use temetvince\SeatCapitals\Tests\TestCase;

/**
 * @package temetvince\SeatCapitals\Tests\Feature
 */
class CapitalFleetQueryTest extends TestCase
{
    use Fixtures;

    private const HOME = 31000001;

    private const JITA = 30000142;

    private const STATION = 60003760;

    private const STRUCTURE = 1030000000001;

    private CapitalFleetQuery $fleet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeGroup(485, 'Dreadnought');
        $this->makeGroup(547, 'Carrier');
        $this->makeGroup(25, 'Frigate');
        $this->makeType(19720, 485, 'Revelation');
        $this->makeType(23757, 547, 'Archon');
        $this->makeType(587, 25, 'Rifter');
        $this->makeType(17366, 25, 'Station Container');

        $this->makeSystem(self::HOME, 'J123456', -0.99);
        $this->makeSystem(self::JITA, 'Jita', 0.95);
        $this->makeStation(self::STATION, 'Jita IV - Moon 4', self::JITA);
        $this->makeStructure(self::STRUCTURE, 'Home Fortizar', self::HOME);

        $this->makeUser(10, 'Alice');
        $this->linkCharacter(1001, 'Alice Main', 10);

        // 1: in space at home
        $this->makeAsset(1, 1001, 19720, 'solar_system', self::HOME, ['location_flag' => 'AutoFit', 'name' => 'Sunrise']);
        // 2: docked in an NPC station
        $this->makeAsset(2, 1001, 23757, 'station', self::STATION);
        // 3: in a structure hangar at home
        $this->makeAsset(3, 1001, 19720, 'other', self::STRUCTURE);
        // 4: inside container 5, which sits in the home structure
        $this->makeAsset(5, 1001, 17366, 'other', self::STRUCTURE);
        $this->makeAsset(4, 1001, 23757, 'item', 5);
        // 6: a frigate, never reported
        $this->makeAsset(6, 1001, 587, 'other', self::STRUCTURE);
        // 7: unknown location, but the location job derived a map id
        $this->makeAsset(7, 1001, 19720, 'other', 999, ['map_id' => self::JITA]);
        // 8: unknown location and no map id
        $this->makeAsset(8, 1001, 19720, 'other', 999);
        // 9: packaged hull in the station
        $this->makeAsset(9, 1001, 19720, 'station', self::STATION, ['is_singleton' => false]);

        $this->fleet = $this->app->make(CapitalFleetQuery::class);
    }

    public function testEveryCapitalHullIsResolvedToItsSolarSystem(): void
    {
        $rows = $this->fleet->build()->get()->keyBy('item_id');

        $this->assertSame([1, 2, 3, 4, 7, 8, 9], $rows->keys()->sort()->values()->all());

        $this->assertSame(self::HOME, (int) $rows[1]->system_id);
        $this->assertSame(self::JITA, (int) $rows[2]->system_id);
        $this->assertSame(self::HOME, (int) $rows[3]->system_id);
        $this->assertSame(self::HOME, (int) $rows[4]->system_id);
        $this->assertSame(self::JITA, (int) $rows[7]->system_id);
        $this->assertNull($rows[8]->system_id);
        $this->assertSame(self::JITA, (int) $rows[9]->system_id);
    }

    public function testExtraColumnsCarryNamesForDisplay(): void
    {
        $row = $this->fleet->build()->where('character_assets.item_id', 1)->firstOrFail();

        $this->assertSame('Revelation', $row->type_name);
        $this->assertSame('Dreadnought', $row->group_name);
        $this->assertSame('J123456', $row->system_name);
        $this->assertEqualsWithDelta(-0.99, (float) $row->system_security, 0.001);
        $this->assertSame('Sunrise', $row->name);
        $this->assertSame(1001, (int) $row->character_id);
    }

    public function testSystemFilterKeepsOnlyHullsResolvedToThoseSystems(): void
    {
        $home = $this->fleet->build([self::HOME])->pluck('item_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $this->assertSame([1, 3, 4], $home);

        $both = $this->fleet->build([self::HOME, self::JITA])->pluck('item_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $this->assertSame([1, 2, 3, 4, 7, 9], $both);

        $this->assertCount(0, $this->fleet->build([1])->get());
    }
}
