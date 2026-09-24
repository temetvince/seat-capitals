<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Notifications;

use Illuminate\Database\Eloquent\Model;
use temetvince\SeatCapitals\Models\CapitalApplication;

/**
 * The facts every channel shows about an application.
 *
 * Channels differ only in layout, so the field list and the review link live
 * here once. Names fall back to ids when a related record is missing, which
 * happens when a character or type was removed after the application was made.
 *
 * Notification text is English by design: webhook and mail notifications have
 * no viewer locale, and upstream SeAT notifications are English too.
 */
trait DescribesApplication
{
    /**
     * Label => value pairs describing the application and applicant.
     *
     * @return array<string, string>
     */
    protected function applicationFields(CapitalApplication $application): array
    {
        return [
            'Applicant' => $this->nameOf($application->user, 'name', $application->user_id),
            'Character' => $this->nameOf($application->character, 'name', $application->character_id),
            'Hull' => $this->nameOf($application->type, 'typeName', $application->type_id),
            'Justification' => $application->justification,
        ];
    }

    /**
     * A related record's display attribute, or its id when the record is missing.
     */
    private function nameOf(?Model $related, string $attribute, ?int $fallback_id): string
    {
        $value = $related?->getAttribute($attribute);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return (string) $fallback_id;
    }

    /**
     * Label => value pairs describing the outcome of a decided application.
     *
     * @return array<string, string>
     */
    protected function decisionFields(CapitalApplication $application): array
    {
        return [
            'Decision' => ucfirst($application->status->value),
            'Reviewer' => $this->nameOf($application->reviewer, 'name', $application->reviewer_user_id),
            'Note' => $application->decision_note ?? '-',
        ];
    }

    /**
     * The page on which reviewers act on the application.
     */
    protected function reviewLink(): string
    {
        return route('seat-capitals::review.index');
    }

    /**
     * The page on which the applicant follows their applications.
     */
    protected function applicationsLink(): string
    {
        return route('seat-capitals::applications.index');
    }
}
