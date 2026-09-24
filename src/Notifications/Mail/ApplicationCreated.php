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
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Notifications\DescribesApplication;

/**
 * Mail for a newly submitted application.
 *
 * @package temetvince\SeatCapitals\Notifications\Mail
 */
class ApplicationCreated extends AbstractMailNotification
{
    use DescribesApplication;

    public function __construct(private readonly CapitalApplication $application)
    {
    }

    protected function populateMessage(MailMessage $message, mixed $notifiable): void
    {
        $message
            ->subject(sprintf('Capital application #%d submitted', $this->application->id))
            ->greeting('Heads up!')
            ->line('A new capital build application is waiting for review.');

        foreach ($this->applicationFields($this->application) as $label => $value) {
            $message->line(sprintf('%s: %s', $label, $value));
        }

        $message->action('Review it on SeAT', $this->reviewLink());
    }
}
