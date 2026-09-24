<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Models;

/**
 * Lifecycle of a capital build application.
 *
 * An application starts `Pending`. The applicant may move it to `Withdrawn`,
 * a reviewer may move it to `Approved` or `Denied`. Every state other than
 * `Pending` is final: no transition leaves it.
 */
enum ApplicationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
    case Withdrawn = 'withdrawn';

    /**
     * Whether a reviewer may set this status as the outcome of a review.
     */
    public function isDecision(): bool
    {
        return $this === self::Approved || $this === self::Denied;
    }

    /**
     * Whether no further transition may leave this status.
     */
    public function isFinal(): bool
    {
        return $this !== self::Pending;
    }

    /**
     * The translation key of the status label shown to users.
     */
    public function labelKey(): string
    {
        return 'seat-capitals::capitals.status_' . $this->value;
    }

    /**
     * The statuses a reviewer may choose from.
     *
     * @return array<int, self>
     */
    public static function decisions(): array
    {
        return [self::Approved, self::Denied];
    }
}
