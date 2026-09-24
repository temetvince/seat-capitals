<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Acl;

use Illuminate\Auth\Access\Response as GateResponse;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Web\Acl\Policies\AbstractPolicy;
use Seat\Web\Acl\Policies\CharacterPolicy;
use Seat\Web\Acl\Response;
use Seat\Web\Models\Acl\Permission;
use Seat\Web\Models\User;

/**
 * Gate for the `character.capitals` permission.
 *
 * SeAT's own `CharacterPolicy` denies every check that carries no character,
 * which would lock the report page itself. This policy answers the bare check
 * ("may this user open the report at all?") by looking for the permission in
 * any of the user's roles, filters or not, and hands checks that do carry a
 * character to `CharacterPolicy` so affiliation filters apply unchanged.
 *
 * SeAT calls `before()` first; it grants admins and records the ability name.
 *
 * @package temetvince\SeatCapitals\Acl
 */
class ReportPolicy extends AbstractPolicy
{
    /**
     * Evaluate `character.capitals`.
     *
     * @param  CharacterInfo|int|null  $character  a character to test the viewer's filters against, or null for the bare check
     * @return GateResponse|bool
     */
    public function capitals(User $user, CharacterInfo|int|null $character = null): GateResponse|bool
    {
        if ($character !== null) {
            $delegate = new CharacterPolicy();
            $delegate->before($user, $this->ability);

            $verdict = $delegate->__call('capitals', [$user, $character]);

            return $verdict instanceof GateResponse ? $verdict : (bool) $verdict;
        }

        $granted = $this->permissionsFrom($user)
            ->contains(fn (Permission $permission) => $permission->getAttribute('title') === $this->ability);

        if ($granted) {
            return Response::allow();
        }

        return Response::deny(sprintf(
            'Request to %s was denied. The permission required is %s',
            request()->path(),
            $this->ability
        ));
    }
}
