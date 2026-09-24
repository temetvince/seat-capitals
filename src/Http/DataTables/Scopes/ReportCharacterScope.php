<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Http\DataTables\Scopes;

use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Web\Http\DataTables\Scopes\CharacterScope;
use Seat\Web\Models\User;
use temetvince\SeatCapitals\Services\AltResolver;
use Yajra\DataTables\Contracts\DataTableScope;

/**
 * Limits the report to characters the viewer may see, optionally with their alts.
 *
 * The visible set is whatever SeAT's own `CharacterScope` allows for the
 * `character.capitals` permission: the viewer's characters plus every
 * character matched by the role's affiliation filters. With `include_alts`
 * the set is widened through `AltResolver` to every character on the same
 * SeAT accounts, so out-of-corp alts of in-scope characters appear too.
 *
 * Admins see everything, so for them the query is left untouched.
 *
 * @package temetvince\SeatCapitals\Http\DataTables\Scopes
 */
class ReportCharacterScope implements DataTableScope
{
    public const ABILITY = 'character.capitals';

    public function __construct(
        private readonly bool $include_alts,
        private readonly AltResolver $alts,
    ) {
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Seat\Eveapi\Models\Assets\CharacterAsset>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\Seat\Eveapi\Models\Assets\CharacterAsset>
     */
    public function apply($query)
    {
        $viewer = auth()->user();

        if ($viewer instanceof User && $viewer->isAdmin()) {
            return $query;
        }

        /** @var \Illuminate\Database\Eloquent\Builder<CharacterInfo> $visible */
        $visible = (new CharacterScope(self::ABILITY))
            ->apply(CharacterInfo::query()->select('character_infos.character_id'));

        $character_ids = $visible->pluck('character_id')->map(fn ($id) => (int) $id)->all();

        if ($this->include_alts) {
            $character_ids = $this->alts->expand($character_ids);
        }

        return $query->whereIntegerInRaw('character_assets.character_id', $character_ids);
    }
}
