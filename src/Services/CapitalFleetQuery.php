<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use temetvince\SeatCapitals\Models\CapitalHull;

/**
 * Builds the query behind the capital ship report.
 *
 * The query selects every character asset whose type belongs to a capital
 * group and resolves the solar system each hull sits in, whatever ESI reported
 * as its location. The resolution order is:
 *
 * 1. the asset's own `location_id` when it is in space (`location_type = solar_system`);
 * 2. the station or structure the asset is docked in;
 * 3. the same two rules applied to the asset's container, for hulls ESI
 *    reports as inside another item;
 * 4. the `map_id` SeAT's location job derived from coordinates.
 *
 * Every joined table gets an alias so that `character_assets.*` stays
 * unambiguous and SeAT's `CharacterScope` can filter on
 * `character_assets.character_id`. The extra selected columns are
 * `type_name`, `group_name`, `system_id`, `system_name` and `system_security`.
 *
 * @package temetvince\SeatCapitals\Services
 */
final class CapitalFleetQuery
{
    /**
     * SQL that yields the solar system id of a `character_assets` row, or NULL.
     * Valid on MySQL and SQLite. Requires the joins made by `build()`.
     */
    public const SYSTEM_EXPRESSION = 'COALESCE('
        . "CASE WHEN character_assets.location_type = 'solar_system' THEN character_assets.location_id END, "
        . 'asset_station.system_id, '
        . 'asset_structure.solar_system_id, '
        . "CASE WHEN container.location_type = 'solar_system' THEN container.location_id END, "
        . 'container_station.system_id, '
        . 'container_structure.solar_system_id, '
        . 'NULLIF(character_assets.map_id, 0))';

    public function __construct(private readonly CapitalCatalog $catalog)
    {
    }

    /**
     * The report query, unfiltered by character; callers add character scoping.
     *
     * @param  array<int, int>  $system_ids  when non-empty, only hulls resolved to one of these systems are returned
     * @return Builder<CapitalHull>
     */
    public function build(array $system_ids = []): Builder
    {
        $query = CapitalHull::query()
            ->join('invTypes', 'invTypes.typeID', '=', 'character_assets.type_id')
            ->join('invGroups', 'invGroups.groupID', '=', 'invTypes.groupID')
            ->leftJoin('universe_stations as asset_station', 'asset_station.station_id', '=', 'character_assets.location_id')
            ->leftJoin('universe_structures as asset_structure', 'asset_structure.structure_id', '=', 'character_assets.location_id')
            ->leftJoin('character_assets as container', 'container.item_id', '=', 'character_assets.location_id')
            ->leftJoin('universe_stations as container_station', 'container_station.station_id', '=', 'container.location_id')
            ->leftJoin('universe_structures as container_structure', 'container_structure.structure_id', '=', 'container.location_id')
            ->leftJoin('solar_systems', 'solar_systems.system_id', '=', DB::raw(self::SYSTEM_EXPRESSION))
            ->whereIn('invGroups.groupID', $this->catalog->groupIds())
            ->select('character_assets.*')
            ->addSelect([
                'invTypes.typeName as type_name',
                'invGroups.groupName as group_name',
                'solar_systems.name as system_name',
                'solar_systems.security as system_security',
            ])
            ->selectRaw(self::SYSTEM_EXPRESSION . ' as system_id');

        if ($system_ids !== []) {
            $placeholders = implode(', ', array_fill(0, count($system_ids), '?'));
            $query->whereRaw(self::SYSTEM_EXPRESSION . ' IN (' . $placeholders . ')', array_values($system_ids));
        }

        return $query;
    }
}
