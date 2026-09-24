<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Observers;

use Seat\Notifications\Models\NotificationGroup;
use Seat\Notifications\Traits\NotificationDispatchTool;
use temetvince\SeatCapitals\Models\CapitalApplication;

/**
 * Raises SeAT notifications when an application is created or decided.
 *
 * Follows upstream's observer pattern: every notification group subscribed to
 * the alert receives the notification on each of its integrations. A withdrawn
 * application raises nothing.
 *
 * @package temetvince\SeatCapitals\Observers
 */
class CapitalApplicationObserver
{
    use NotificationDispatchTool;

    public const ALERT_CREATED = 'seat_capitals_application_created';

    public const ALERT_DECIDED = 'seat_capitals_application_decided';

    public function created(CapitalApplication $application): void
    {
        $this->notify(self::ALERT_CREATED, $application);
    }

    public function updated(CapitalApplication $application): void
    {
        if (! $application->wasChanged('status') || ! $application->status->isDecision()) {
            return;
        }

        $this->notify(self::ALERT_DECIDED, $application);
    }

    private function notify(string $alert, CapitalApplication $application): void
    {
        logger()->debug(sprintf('[SeAT Capitals][%d] Queuing %s notifications.', $application->id, $alert));

        $groups = NotificationGroup::with('alerts')
            ->whereHas('alerts', function ($query) use ($alert) {
                $query->where('alert', $alert);
            })
            ->get();

        $this->dispatchNotifications($alert, $groups, function (string $notification_class) use ($application) {
            return new $notification_class($application);
        });
    }
}
