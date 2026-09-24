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
use temetvince\SeatCapitals\Models\ReportScope;
use temetvince\SeatCapitals\Services\AltResolver;
use Yajra\DataTables\Contracts\DataTableScope;

/**
 * Limits the report to the characters a `ReportScope` covers for the viewer.
 *
 * For `Filtered` and `Alts` the visible set is whatever SeAT's own
 * `CharacterScope` allows for the `character.capitals` permission: the
 * viewer's characters plus every character matched by the role's affiliation
 * filters, widened through `AltResolver` for `Alts`. For `All` the query is
 * left untouched, so the caller must have checked `capitals.report_all`
 * before choosing that scope. Admins see everything whatever the scope.
 *
 * @package temetvince\SeatCapitals\Http\DataTables\Scopes
 */
class ReportCharacterScope implements DataTableScope
{
    public const ABILITY = 'character.capitals';

    public function __construct(
        private readonly ReportScope $scope,
        private readonly AltResolver $alts,
    ) {
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\temetvince\SeatCapitals\Models\CapitalHull>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\temetvince\SeatCapitals\Models\CapitalHull>
     */
    public function apply($query)
    {
        $viewer = auth()->user();

        if ($viewer instanceof User && $viewer->isAdmin()) {
            return $query;
        }

        if ($this->scope->isUnrestricted()) {
            return $query;
        }

        /** @var \Illuminate\Database\Eloquent\Builder<CharacterInfo> $visible */
        $visible = (new CharacterScope(self::ABILITY))
            ->apply(CharacterInfo::query()->select('character_infos.character_id'));

        $character_ids = $visible->pluck('character_id')->map(fn ($id) => (int) $id)->all();

        if ($this->scope->includesAlts()) {
            $character_ids = $this->alts->expand($character_ids);
        }

        return $query->whereIntegerInRaw('character_assets.character_id', $character_ids);
    }
}
