<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Notifications\Slack;

use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Seat\Notifications\Notifications\AbstractSlackNotification;
use temetvince\SeatCapitals\Models\ApplicationStatus;
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Notifications\DescribesApplication;

/**
 * Slack message for an approved or denied application.
 *
 * @package temetvince\SeatCapitals\Notifications\Slack
 */
class ApplicationDecided extends AbstractSlackNotification
{
    use DescribesApplication;

    public function __construct(private readonly CapitalApplication $application)
    {
    }

    protected function populateMessage(SlackMessage $message, mixed $notifiable): void
    {
        if ($this->application->status === ApplicationStatus::Approved) {
            $message->success();
        } else {
            $message->error();
        }

        $message
            ->content(sprintf('A capital build application has been %s.', $this->application->status->value))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(sprintf('Capital application #%d', $this->application->id), $this->applicationsLink())
                    ->fields(array_merge(
                        $this->applicationFields($this->application),
                        $this->decisionFields($this->application)
                    ));
            });
    }
}
