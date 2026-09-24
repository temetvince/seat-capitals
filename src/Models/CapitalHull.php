<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Models;

use Seat\Eveapi\Models\Assets\CharacterAsset;

/**
 * A read model over `character_assets` for the capital ship report.
 *
 * Rows come only from `CapitalFleetQuery`, which joins the type, group and
 * resolved solar system onto each asset. The extra columns exist on rows of
 * this model and on nothing else, which is why the report does not read plain
 * `CharacterAsset` rows. Never save an instance.
 *
 * @property int $item_id
 * @property int $character_id
 * @property int $type_id
 * @property string|null $name the pilot-given ship name
 * @property bool $is_singleton true for an assembled hull, false for a packaged one
 * @property string $type_name the hull's `invTypes.typeName`
 * @property string $group_name the hull's `invGroups.groupName`
 * @property int|null $system_id the resolved solar system, null when unresolvable
 * @property string|null $system_name
 * @property float|null $system_security
 *
 * @package temetvince\SeatCapitals\Models
 */
class CapitalHull extends CharacterAsset
{
    protected $table = 'character_assets';
}
