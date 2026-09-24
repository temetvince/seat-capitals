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
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Notifications\DescribesApplication;

/**
 * Discord message for a newly submitted application.
 *
 * @package temetvince\SeatCapitals\Notifications\Discord
 */
class ApplicationCreated extends AbstractDiscordNotification
{
    use DescribesApplication;

    public function __construct(private readonly CapitalApplication $application)
    {
    }

    protected function populateMessage(DiscordMessage $message, mixed $notifiable): void
    {
        $message
            ->info()
            ->content('A new capital build application is waiting for review.')
            ->embed(function (DiscordEmbed $embed) {
                $embed->title(sprintf('Capital application #%d', $this->application->id), $this->reviewLink());

                foreach ($this->applicationFields($this->application) as $label => $value) {
                    $embed->field($label, $value);
                }
            });
    }
}
