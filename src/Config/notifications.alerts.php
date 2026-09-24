<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

/*
 * Alerts merged into eveseat/notifications' `notifications.alerts` config.
 *
 * Each key is an alert a notification group can subscribe to; `handlers` maps
 * a channel to the notification class that renders it. Keys carry the plugin
 * prefix so they never collide with upstream alerts.
 */
return [
    'seat_capitals_application_created' => [
        'label' => 'seat-capitals::capitals.alert_application_created',
        'handlers' => [
            'discord' => \temetvince\SeatCapitals\Notifications\Discord\ApplicationCreated::class,
            'slack' => \temetvince\SeatCapitals\Notifications\Slack\ApplicationCreated::class,
            'mail' => \temetvince\SeatCapitals\Notifications\Mail\ApplicationCreated::class,
        ],
    ],
    'seat_capitals_application_decided' => [
        'label' => 'seat-capitals::capitals.alert_application_decided',
        'handlers' => [
            'discord' => \temetvince\SeatCapitals\Notifications\Discord\ApplicationDecided::class,
            'slack' => \temetvince\SeatCapitals\Notifications\Slack\ApplicationDecided::class,
            'mail' => \temetvince\SeatCapitals\Notifications\Mail\ApplicationDecided::class,
        ],
    ],
];
