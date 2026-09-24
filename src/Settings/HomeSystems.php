<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Settings;

use Illuminate\Database\Eloquent\Collection;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Services\Settings\Seat;

/**
 * The instance-wide list of home systems that pre-fills the report filter.
 *
 * Stored as one SeAT global setting holding a JSON array of solar system ids.
 * An empty list means the report opens unfiltered.
 *
 * @package temetvince\SeatCapitals\Settings
 */
final class HomeSystems
{
    /**
     * The SeAT global setting name.
     */
    public const SETTING = 'seat_capitals_home_systems';

    /**
     * The stored system ids, in stored order, without duplicates.
     *
     * @return array<int, int>
     */
    public function ids(): array
    {
        $stored = Seat::get(self::SETTING);

        if (! is_array($stored)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $stored)));
    }

    /**
     * The stored systems, ordered by name. Ids unknown to the SDE are skipped.
     *
     * @return Collection<int, SolarSystem>
     */
    public function systems(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return new Collection();
        }

        return SolarSystem::whereIn('system_id', $ids)->orderBy('name')->get();
    }

    /**
     * Replace the stored list.
     *
     * @param  array<int, int|string>  $system_ids  duplicates and non-numeric entries are dropped
     */
    public function store(array $system_ids): void
    {
        $clean = array_values(array_unique(array_map(
            'intval',
            array_filter($system_ids, 'is_numeric')
        )));

        Seat::set(self::SETTING, $clean);
    }
}
