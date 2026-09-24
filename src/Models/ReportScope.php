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
 * Which characters the capital report covers.
 *
 * `Filtered` is what the viewer's `character.capitals` role filters allow.
 * `Alts` widens that to every character on the same SeAT accounts. `All`
 * ignores the filters and covers every character SeAT knows; it is only
 * honoured for viewers holding `capitals.report_all`, which the controller
 * enforces before the scope is applied.
 */
enum ReportScope: string
{
    case Filtered = 'filtered';
    case Alts = 'alts';
    case All = 'all';

    /**
     * The scope a request asked for, falling back to `Filtered` for anything unknown.
     */
    public static function fromInput(mixed $value): self
    {
        if (is_string($value)) {
            return self::tryFrom($value) ?? self::Filtered;
        }

        return self::Filtered;
    }

    /**
     * Whether same-account characters outside the role filters are included.
     */
    public function includesAlts(): bool
    {
        return $this === self::Alts;
    }

    /**
     * Whether the role filters are ignored entirely.
     */
    public function isUnrestricted(): bool
    {
        return $this === self::All;
    }

    /**
     * The translation key of the option label shown to users.
     */
    public function labelKey(): string
    {
        return 'seat-capitals::capitals.scope_' . $this->value;
    }
}
