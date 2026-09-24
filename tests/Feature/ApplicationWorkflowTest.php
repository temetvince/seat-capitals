<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests\Feature;

use Seat\Web\Models\User;
use temetvince\SeatCapitals\Exceptions\WorkflowException;
use temetvince\SeatCapitals\Models\ApplicationStatus;
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Services\ApplicationWorkflow;
use temetvince\SeatCapitals\Tests\Fixtures;
use temetvince\SeatCapitals\Tests\TestCase;

/**
 * @package temetvince\SeatCapitals\Tests\Feature
 */
class ApplicationWorkflowTest extends TestCase
{
    use Fixtures;

    private const REVELATION = 19720;

    private const RIFTER = 587;

    private User $alice;

    private User $bob;

    private ApplicationWorkflow $workflow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeGroup(485, 'Dreadnought');
        $this->makeGroup(25, 'Frigate');
        $this->makeType(self::REVELATION, 485, 'Revelation');
        $this->makeType(self::RIFTER, 25, 'Rifter');

        $this->alice = $this->makeUser(10, 'Alice', false, 1001);
        $this->linkCharacter(1001, 'Alice Main', 10);
        $this->linkCharacter(1002, 'Alice Alt', 10);

        $this->bob = $this->makeUser(20, 'Bob', false, 2001);
        $this->linkCharacter(2001, 'Bob Main', 20);

        $this->workflow = $this->app->make(ApplicationWorkflow::class);
    }

    public function testSubmitCreatesAPendingApplication(): void
    {
        $application = $this->workflow->submit($this->alice, 1002, self::REVELATION, 'Need a dread for the home hole.');

        $this->assertSame(ApplicationStatus::Pending, $application->status);
        $this->assertSame(10, $application->user_id);
        $this->assertSame(1002, $application->character_id);
        $this->assertSame(self::REVELATION, $application->type_id);
        $this->assertNull($application->reviewer_user_id);
        $this->assertNull($application->decided_at);
        $this->assertSame(1, CapitalApplication::count());
    }

    public function testSubmitRejectsACharacterOfAnotherUser(): void
    {
        $this->assertReason(WorkflowException::CHARACTER_NOT_OWNED, function () {
            $this->workflow->submit($this->alice, 2001, self::REVELATION, 'Not mine.');
        });

        $this->assertSame(0, CapitalApplication::count());
    }

    public function testSubmitRejectsANonCapitalHull(): void
    {
        $this->assertReason(WorkflowException::TYPE_NOT_CAPITAL, function () {
            $this->workflow->submit($this->alice, 1001, self::RIFTER, 'A frigate.');
        });
    }

    public function testSubmitRejectsADuplicateWhileOneIsPending(): void
    {
        $first = $this->workflow->submit($this->alice, 1001, self::REVELATION, 'First.');

        $this->assertReason(WorkflowException::DUPLICATE_PENDING, function () {
            $this->workflow->submit($this->alice, 1001, self::REVELATION, 'Second.');
        });

        $this->workflow->withdraw($first, $this->alice);

        $again = $this->workflow->submit($this->alice, 1001, self::REVELATION, 'Third.');
        $this->assertSame(ApplicationStatus::Pending, $again->status);
    }

    public function testOnlyTheApplicantMayWithdraw(): void
    {
        $application = $this->workflow->submit($this->alice, 1001, self::REVELATION, 'Mine.');

        $this->assertReason(WorkflowException::NOT_APPLICANT, function () use ($application) {
            $this->workflow->withdraw($application, $this->bob);
        });

        $this->workflow->withdraw($application, $this->alice);

        $this->assertSame(ApplicationStatus::Withdrawn, $application->fresh()->status);
    }

    public function testDecideRecordsReviewerNoteAndTime(): void
    {
        $application = $this->workflow->submit($this->alice, 1001, self::REVELATION, 'Mine.');

        $this->workflow->decide($application, $this->bob, ApplicationStatus::Approved, '  Go ahead.  ');

        $stored = $application->fresh();
        $this->assertSame(ApplicationStatus::Approved, $stored->status);
        $this->assertSame(20, $stored->reviewer_user_id);
        $this->assertSame('Go ahead.', $stored->decision_note);
        $this->assertNotNull($stored->decided_at);
    }

    public function testDecideStoresABlankNoteAsNull(): void
    {
        $application = $this->workflow->submit($this->alice, 1001, self::REVELATION, 'Mine.');

        $this->workflow->decide($application, $this->bob, ApplicationStatus::Denied, '   ');

        $this->assertSame(ApplicationStatus::Denied, $application->fresh()->status);
        $this->assertNull($application->fresh()->decision_note);
    }

    public function testDecideRejectsStatusesThatAreNotDecisions(): void
    {
        $application = $this->workflow->submit($this->alice, 1001, self::REVELATION, 'Mine.');

        $this->assertReason(WorkflowException::NOT_A_DECISION, function () use ($application) {
            $this->workflow->decide($application, $this->bob, ApplicationStatus::Withdrawn, null);
        });

        $this->assertSame(ApplicationStatus::Pending, $application->fresh()->status);
    }

    public function testDecidedApplicationsCannotBeDecidedOrWithdrawnAgain(): void
    {
        $application = $this->workflow->submit($this->alice, 1001, self::REVELATION, 'Mine.');
        $this->workflow->decide($application, $this->bob, ApplicationStatus::Denied, null);

        $this->assertReason(WorkflowException::NOT_PENDING, function () use ($application) {
            $this->workflow->decide($application, $this->bob, ApplicationStatus::Approved, null);
        });

        $this->assertReason(WorkflowException::NOT_PENDING, function () use ($application) {
            $this->workflow->withdraw($application, $this->alice);
        });

        $this->assertSame(ApplicationStatus::Denied, $application->fresh()->status);
    }

    /**
     * Assert that the callback throws a `WorkflowException` carrying the given reason.
     */
    private function assertReason(string $reason, callable $callback): void
    {
        try {
            $callback();
        } catch (WorkflowException $exception) {
            $this->assertSame($reason, $exception->reason);

            return;
        }

        $this->fail(sprintf('Expected a WorkflowException with reason "%s".', $reason));
    }
}
