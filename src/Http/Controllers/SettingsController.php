<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use temetvince\SeatCapitals\Http\Validation\HomeSystemsUpdate;
use temetvince\SeatCapitals\Settings\HomeSystems;

/**
 * Plugin settings: the home systems that pre-fill the report filter.
 *
 * Gated by `capitals.review` in the route group.
 *
 * @package temetvince\SeatCapitals\Http\Controllers
 */
class SettingsController extends Controller
{
    public function index(HomeSystems $home_systems): View
    {
        return view('seat-capitals::settings.index', [
            'home_systems' => $home_systems->systems(),
        ]);
    }

    public function update(HomeSystemsUpdate $request, HomeSystems $home_systems): RedirectResponse
    {
        $systems = $request->input('systems', []);

        $home_systems->store(is_array($systems) ? $systems : []);

        return redirect()->route('seat-capitals::settings.index')
            ->with('success', trans('seat-capitals::capitals.settings_saved'));
    }
}
