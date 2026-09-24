<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Exceptions;

use RuntimeException;

/**
 * A rule of the application workflow was violated.
 *
 * The message is developer text. The `reason` code identifies the rule so a
 * controller can translate it for the user with the key
 * `seat-capitals::capitals.error_<reason>`.
 *
 * @package temetvince\SeatCapitals\Exceptions
 */
final class WorkflowException extends RuntimeException
{
    public const CHARACTER_NOT_OWNED = 'character_not_owned';

    public const TYPE_NOT_CAPITAL = 'type_not_capital';

    public const DUPLICATE_PENDING = 'duplicate_pending';

    public const NOT_PENDING = 'not_pending';

    public const NOT_APPLICANT = 'not_applicant';

    public const NOT_A_DECISION = 'not_a_decision';

    private function __construct(
        public readonly string $reason,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function characterNotOwned(int $character_id): self
    {
        return new self(self::CHARACTER_NOT_OWNED, sprintf('Character %d is not linked to the applicant.', $character_id));
    }

    public static function typeNotCapital(int $type_id): self
    {
        return new self(self::TYPE_NOT_CAPITAL, sprintf('Type %d is not a capital hull.', $type_id));
    }

    public static function duplicatePending(int $character_id, int $type_id): self
    {
        return new self(
            self::DUPLICATE_PENDING,
            sprintf('Character %d already has a pending application for type %d.', $character_id, $type_id)
        );
    }

    public static function notPending(int $application_id): self
    {
        return new self(self::NOT_PENDING, sprintf('Application %d is no longer pending.', $application_id));
    }

    public static function notApplicant(int $application_id): self
    {
        return new self(self::NOT_APPLICANT, sprintf('Application %d belongs to another user.', $application_id));
    }

    public static function notADecision(string $status): self
    {
        return new self(self::NOT_A_DECISION, sprintf('Status "%s" is not a reviewer decision.', $status));
    }
}
