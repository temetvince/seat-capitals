<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Services;

use Illuminate\Database\Eloquent\Collection;
use Seat\Eveapi\Models\Sde\InvType;

/**
 * Knows which SDE inventory groups count as capital hulls.
 *
 * The group list comes from the `seat-capitals.capital_groups` config key and is
 * fixed for the lifetime of the instance. Every other part of the plugin asks
 * this class instead of hard-coding group ids.
 *
 * @package temetvince\SeatCapitals\Services
 */
final class CapitalCatalog
{
    /**
     * @var array<int, int>
     */
    private array $group_ids;

    /**
     * @param  iterable<int|string, mixed>  $groups  the configured groups, keyed by `invGroups.groupID`
     */
    public function __construct(iterable $groups)
    {
        $ids = [];

        foreach ($groups as $group_id => $label) {
            $ids[] = (int) $group_id;
        }

        $this->group_ids = array_values(array_unique($ids));
    }

    /**
     * The configured capital group ids, in configuration order without duplicates.
     *
     * @return array<int, int>
     */
    public function groupIds(): array
    {
        return $this->group_ids;
    }

    /**
     * Whether the given SDE type belongs to one of the capital groups.
     *
     * @param  int  $type_id  an `invTypes.typeID`; an unknown id yields false
     */
    public function isCapitalType(int $type_id): bool
    {
        if ($this->group_ids === []) {
            return false;
        }

        return InvType::where('typeID', $type_id)
            ->whereIn('groupID', $this->group_ids)
            ->exists();
    }

    /**
     * Every published capital hull, with its group loaded, ordered by group then name.
     *
     * @return Collection<int, InvType>
     */
    public function types(): Collection
    {
        if ($this->group_ids === []) {
            return new Collection();
        }

        return InvType::with('group')
            ->whereIn('groupID', $this->group_ids)
            ->where('published', true)
            ->orderBy('groupID')
            ->orderBy('typeName')
            ->get();
    }
}
