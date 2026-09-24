<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests\Feature;

use temetvince\SeatCapitals\Services\AltResolver;
use temetvince\SeatCapitals\Tests\Fixtures;
use temetvince\SeatCapitals\Tests\TestCase;

/**
 * @package temetvince\SeatCapitals\Tests\Feature
 */
class AltResolverTest extends TestCase
{
    use Fixtures;

    private AltResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeUser(10, 'Alice');
        $this->linkCharacter(1001, 'Alice Main', 10, 98000001);
        $this->linkCharacter(1002, 'Alice Alt', 10, 98000002);

        $this->makeUser(20, 'Bob');
        $this->linkCharacter(2001, 'Bob Main', 20, 98000001);

        $this->resolver = new AltResolver();
    }

    public function testAltsOnTheSameAccountAreAdded(): void
    {
        $this->assertSame([1001, 1002], $this->sorted($this->resolver->expand([1001])));
    }

    public function testOtherAccountsAreNotMixedIn(): void
    {
        $this->assertSame([1001, 1002, 2001], $this->sorted($this->resolver->expand([1001, 2001])));
        $this->assertSame([2001], $this->sorted($this->resolver->expand([2001])));
    }

    public function testUnknownCharactersPassThrough(): void
    {
        $this->assertSame([], $this->resolver->expand([]));
        $this->assertSame([999], $this->resolver->expand([999, 999]));
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function sorted(array $ids): array
    {
        sort($ids);

        return $ids;
    }
}
