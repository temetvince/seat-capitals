<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Routing\Controller as BaseController;
use Seat\Web\Models\User;

/**
 * Base for the plugin's controllers.
 *
 * Every plugin route sits behind the `auth` middleware, so the signed-in user
 * is always a SeAT `User`; `user()` makes that guarantee explicit and typed.
 *
 * @package temetvince\SeatCapitals\Http\Controllers
 */
abstract class Controller extends BaseController
{
    /**
     * The signed-in SeAT user.
     *
     * @throws AuthenticationException when no SeAT user is signed in, which the `auth` middleware prevents
     */
    protected function user(): User
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            throw new AuthenticationException();
        }

        return $user;
    }
}
