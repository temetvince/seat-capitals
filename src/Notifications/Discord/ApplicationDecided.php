<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Notifications\Discord;

use Seat\Notifications\Notifications\AbstractDiscordNotification;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbed;
use Seat\Notifications\Services\Discord\Messages\DiscordMessage;
use temetvince\SeatCapitals\Models\ApplicationStatus;
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Notifications\DescribesApplication;

/**
 * Discord message for an approved or denied application.
 *
 * @package temetvince\SeatCapitals\Notifications\Discord
 */
class ApplicationDecided extends AbstractDiscordNotification
{
    use DescribesApplication;

    public function __construct(private readonly CapitalApplication $application)
    {
    }

    protected function populateMessage(DiscordMessage $message, mixed $notifiable): void
    {
        if ($this->application->status === ApplicationStatus::Approved) {
            $message->success();
        } else {
            $message->error();
        }

        $message
            ->content(sprintf('A capital build application has been %s.', $this->application->status->value))
            ->embed(function (DiscordEmbed $embed) {
                $embed->title(sprintf('Capital application #%d', $this->application->id), $this->applicationsLink());

                $fields = array_merge(
                    $this->applicationFields($this->application),
                    $this->decisionFields($this->application)
                );

                foreach ($fields as $label => $value) {
                    $embed->field($label, $value);
                }
            });
    }
}
