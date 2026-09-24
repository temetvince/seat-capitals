<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests\Feature;

use temetvince\SeatCapitals\Http\DataTables\Scopes\ReportCharacterScope;
use temetvince\SeatCapitals\Models\ReportScope;
use temetvince\SeatCapitals\Services\AltResolver;
use temetvince\SeatCapitals\Services\CapitalFleetQuery;
use temetvince\SeatCapitals\Tests\Fixtures;
use temetvince\SeatCapitals\Tests\TestCase;

/**
 * Covers the paths of the report scope that need no SeAT role tables:
 * the unrestricted scope and the administrator bypass. The filtered paths
 * delegate to upstream's `CharacterScope`, which upstream tests.
 *
 * @package temetvince\SeatCapitals\Tests\Feature
 */
class ReportCharacterScopeTest extends TestCase
{
    use Fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeGroup(485, 'Dreadnought');
        $this->makeType(19720, 485, 'Revelation');
        $this->makeSystem(31000001, 'J123456', -0.99);

        $this->makeUser(10, 'Alice');
        $this->linkCharacter(1001, 'Alice Main', 10);
        $this->makeUser(20, 'Bob');
        $this->linkCharacter(2001, 'Bob Main', 20);
        $this->makeUser(30, 'Root', true);
        $this->linkCharacter(3001, 'Root Main', 30);

        $this->makeAsset(1, 1001, 19720, 'solar_system', 31000001);
        $this->makeAsset(2, 2001, 19720, 'solar_system', 31000001);
    }

    public function testUnrestrictedScopeLeavesTheQueryUntouchedForAnyViewer(): void
    {
        $this->actingAs($this->makeViewer(10));

        $this->assertSame([1, 2], $this->itemsFor(ReportScope::All));
    }

    public function testAdministratorsSeeEverythingWhateverTheScope(): void
    {
        $this->actingAs($this->makeViewer(30));

        $this->assertSame([1, 2], $this->itemsFor(ReportScope::Filtered));
        $this->assertSame([1, 2], $this->itemsFor(ReportScope::Alts));
    }

    /**
     * @return array<int, int> item ids the scoped report query returns, ascending
     */
    private function itemsFor(ReportScope $scope): array
    {
        $query = $this->app->make(CapitalFleetQuery::class)->build();

        $scoped = (new ReportCharacterScope($scope, new AltResolver()))->apply($query);

        return $scoped->pluck('item_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    private function makeViewer(int $user_id): \Seat\Web\Models\User
    {
        return \Seat\Web\Models\User::findOrFail($user_id);
    }
}
