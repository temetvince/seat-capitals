<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Services;

use Seat\Eveapi\Models\RefreshToken;

/**
 * Widens a set of characters to every character on the same SeAT accounts.
 *
 * SeAT links characters to a user through their refresh tokens, so two
 * characters are alts of each other when their tokens share a `user_id`.
 * Only characters with a live token count; a character whose token was
 * removed is not an alt anyone can see.
 *
 * @package temetvince\SeatCapitals\Services
 */
final class AltResolver
{
    /**
     * Every character registered under the same SeAT user as any of the given characters.
     *
     * Postcondition: the result is a superset of the input restricted to
     * characters with a live token, without duplicates, in no particular order.
     *
     * @param  array<int, int>  $character_ids
     * @return array<int, int>
     */
    public function expand(array $character_ids): array
    {
        if ($character_ids === []) {
            return [];
        }

        $user_ids = RefreshToken::whereIn('character_id', $character_ids)
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($user_ids->isEmpty()) {
            return array_values(array_unique($character_ids));
        }

        $alts = RefreshToken::whereIn('user_id', $user_ids)
            ->pluck('character_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($character_ids, $alts)));
    }
}
