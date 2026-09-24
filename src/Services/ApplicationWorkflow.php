<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Services;

use Psr\Log\LoggerInterface;
use Seat\Web\Models\User;
use temetvince\SeatCapitals\Exceptions\WorkflowException;
use temetvince\SeatCapitals\Models\ApplicationStatus;
use temetvince\SeatCapitals\Models\CapitalApplication;

/**
 * The only writer of `CapitalApplication` rows.
 *
 * Enforces the rules of the application lifecycle: who may apply for which
 * character and hull, who may withdraw, and which transitions a reviewer may
 * make. Every violation throws `WorkflowException` with a reason code; nothing
 * is written when it throws.
 *
 * Every successful transition is logged at `info` and, for decisions, also
 * recorded in SeAT's security log through the `security.log` event.
 *
 * @package temetvince\SeatCapitals\Services
 */
final class ApplicationWorkflow
{
    public function __construct(
        private readonly CapitalCatalog $catalog,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Create a pending application.
     *
     * Preconditions: `$character_id` is linked to `$applicant` in SeAT,
     * `$type_id` is a capital hull per the catalog, and no pending application
     * exists for the same character and hull.
     *
     * @param  User  $applicant  the signed-in SeAT user
     * @param  int  $character_id  one of the applicant's characters
     * @param  int  $type_id  the hull's `invTypes.typeID`
     * @param  string  $justification  free text, already validated as non-empty
     * @return CapitalApplication the persisted application, status `Pending`
     *
     * @throws WorkflowException when a precondition fails
     */
    public function submit(User $applicant, int $character_id, int $type_id, string $justification): CapitalApplication
    {
        if (! in_array($character_id, $applicant->associatedCharacterIds(), true)) {
            throw WorkflowException::characterNotOwned($character_id);
        }

        if (! $this->catalog->isCapitalType($type_id)) {
            throw WorkflowException::typeNotCapital($type_id);
        }

        $duplicate = CapitalApplication::pending()
            ->where('character_id', $character_id)
            ->where('type_id', $type_id)
            ->exists();

        if ($duplicate) {
            throw WorkflowException::duplicatePending($character_id, $type_id);
        }

        $application = CapitalApplication::create([
            'user_id' => $applicant->id,
            'character_id' => $character_id,
            'type_id' => $type_id,
            'justification' => $justification,
            'status' => ApplicationStatus::Pending,
        ]);

        $this->logger->info('[SeAT Capitals] Application submitted.', [
            'application_id' => $application->id,
            'user_id' => $applicant->id,
            'character_id' => $character_id,
            'type_id' => $type_id,
        ]);

        return $application;
    }

    /**
     * Let the applicant retract a pending application.
     *
     * Preconditions: `$actor` is the applicant and the application is pending.
     *
     * @throws WorkflowException when a precondition fails
     */
    public function withdraw(CapitalApplication $application, User $actor): void
    {
        if ($application->user_id !== $actor->id) {
            throw WorkflowException::notApplicant($application->id);
        }

        $this->transition($application, ApplicationStatus::Withdrawn);

        $this->logger->info('[SeAT Capitals] Application withdrawn.', [
            'application_id' => $application->id,
            'user_id' => $actor->id,
        ]);
    }

    /**
     * Record a reviewer's decision.
     *
     * Preconditions: `$decision` is `Approved` or `Denied` and the application
     * is pending. Postcondition: the reviewer, note and decision time are set.
     *
     * @param  User  $reviewer  the signed-in reviewer; authorisation is the caller's job
     * @param  string|null  $note  optional text shown to the applicant
     *
     * @throws WorkflowException when a precondition fails
     */
    public function decide(CapitalApplication $application, User $reviewer, ApplicationStatus $decision, ?string $note): void
    {
        if (! $decision->isDecision()) {
            throw WorkflowException::notADecision($decision->value);
        }

        $application->reviewer_user_id = $reviewer->id;
        $application->decision_note = $note !== null && trim($note) !== '' ? trim($note) : null;
        $application->decided_at = now();

        $this->transition($application, $decision);

        $this->logger->info('[SeAT Capitals] Application decided.', [
            'application_id' => $application->id,
            'reviewer_user_id' => $reviewer->id,
            'decision' => $decision->value,
        ]);

        $hull = $application->type?->getAttribute('typeName');

        event('security.log', [
            sprintf(
                '%s %s capital application #%d (%s for character %d).',
                (string) $reviewer->getAttribute('name'),
                $decision->value,
                $application->id,
                is_string($hull) ? $hull : (string) $application->type_id,
                $application->character_id
            ),
            'seat-capitals',
        ]);
    }

    /**
     * Move a pending application to a final status and persist it.
     *
     * @throws WorkflowException when the application is not pending
     */
    private function transition(CapitalApplication $application, ApplicationStatus $target): void
    {
        if (! $application->isPending()) {
            throw WorkflowException::notPending($application->id);
        }

        $application->status = $target;
        $application->save();
    }
}
