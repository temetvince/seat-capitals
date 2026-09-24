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
use temetvince\SeatCapitals\Models\ReportScope;

/**
 * @package temetvince\SeatCapitals\Tests\Unit
 */
class ReportScopeTest extends TestCase
{
    public function testKnownValuesParseAndUnknownInputFallsBackToFiltered(): void
    {
        $this->assertSame(ReportScope::Filtered, ReportScope::fromInput('filtered'));
        $this->assertSame(ReportScope::Alts, ReportScope::fromInput('alts'));
        $this->assertSame(ReportScope::All, ReportScope::fromInput('all'));

        $this->assertSame(ReportScope::Filtered, ReportScope::fromInput('everything'));
        $this->assertSame(ReportScope::Filtered, ReportScope::fromInput(null));
        $this->assertSame(ReportScope::Filtered, ReportScope::fromInput(['all']));
    }

    public function testOnlyAltsIncludesAltsAndOnlyAllIsUnrestricted(): void
    {
        $this->assertFalse(ReportScope::Filtered->includesAlts());
        $this->assertTrue(ReportScope::Alts->includesAlts());
        $this->assertFalse(ReportScope::All->includesAlts());

        $this->assertFalse(ReportScope::Filtered->isUnrestricted());
        $this->assertFalse(ReportScope::Alts->isUnrestricted());
        $this->assertTrue(ReportScope::All->isUnrestricted());
    }

    public function testLabelKeyFollowsTheValue(): void
    {
        $this->assertSame('seat-capitals::capitals.scope_alts', ReportScope::Alts->labelKey());
    }
}
