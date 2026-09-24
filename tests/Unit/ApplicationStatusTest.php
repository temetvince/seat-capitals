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
use temetvince\SeatCapitals\Models\ApplicationStatus;

/**
 * @package temetvince\SeatCapitals\Tests\Unit
 */
class ApplicationStatusTest extends TestCase
{
    public function testOnlyPendingIsNotFinal(): void
    {
        $this->assertFalse(ApplicationStatus::Pending->isFinal());
        $this->assertTrue(ApplicationStatus::Approved->isFinal());
        $this->assertTrue(ApplicationStatus::Denied->isFinal());
        $this->assertTrue(ApplicationStatus::Withdrawn->isFinal());
    }

    public function testOnlyApprovedAndDeniedAreDecisions(): void
    {
        $this->assertTrue(ApplicationStatus::Approved->isDecision());
        $this->assertTrue(ApplicationStatus::Denied->isDecision());
        $this->assertFalse(ApplicationStatus::Pending->isDecision());
        $this->assertFalse(ApplicationStatus::Withdrawn->isDecision());

        $this->assertSame(
            [ApplicationStatus::Approved, ApplicationStatus::Denied],
            ApplicationStatus::decisions()
        );
    }

    public function testLabelKeyFollowsTheStatusValue(): void
    {
        $this->assertSame('seat-capitals::capitals.status_pending', ApplicationStatus::Pending->labelKey());
        $this->assertSame('seat-capitals::capitals.status_withdrawn', ApplicationStatus::Withdrawn->labelKey());
    }
}
