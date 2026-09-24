<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Notifications\Mail;

use Illuminate\Notifications\Messages\MailMessage;
use Seat\Notifications\Notifications\AbstractMailNotification;
use temetvince\SeatCapitals\Models\ApplicationStatus;
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Notifications\DescribesApplication;

/**
 * Mail for an approved or denied application.
 *
 * @package temetvince\SeatCapitals\Notifications\Mail
 */
class ApplicationDecided extends AbstractMailNotification
{
    use DescribesApplication;

    public function __construct(private readonly CapitalApplication $application)
    {
    }

    protected function populateMessage(MailMessage $message, mixed $notifiable): void
    {
        $outcome = $this->application->status === ApplicationStatus::Approved ? 'approved' : 'denied';

        $message
            ->subject(sprintf('Capital application #%d %s', $this->application->id, $outcome))
            ->greeting('Heads up!')
            ->line(sprintf('A capital build application has been %s.', $outcome));

        $fields = array_merge(
            $this->applicationFields($this->application),
            $this->decisionFields($this->application)
        );

        foreach ($fields as $label => $value) {
            $message->line(sprintf('%s: %s', $label, $value));
        }

        $message->action('Open SeAT', $this->applicationsLink());
    }
}
