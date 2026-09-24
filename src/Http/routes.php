<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

use Illuminate\Support\Facades\Route;
use temetvince\SeatCapitals\Http\Controllers\ApplicationsController;
use temetvince\SeatCapitals\Http\Controllers\ReportController;
use temetvince\SeatCapitals\Http\Controllers\ReviewController;
use temetvince\SeatCapitals\Http\Controllers\SettingsController;

/*
 * Every route lives under `/capitals` and requires a signed-in user. Each
 * area is gated by its own permission. Route names carry the `seat-capitals::`
 * prefix so they never collide with upstream's `seatcore::` names.
 *
 * DataTables fetch their rows by posting to the page's own GET route with an
 * `X-HTTP-Method-Override: GET` header, so no separate data routes exist.
 */
Route::group([
    'prefix' => 'capitals',
    'middleware' => ['web', 'auth'],
], function (): void {

    Route::group(['middleware' => 'can:capitals.apply'], function (): void {
        Route::get('/', [ApplicationsController::class, 'index'])
            ->name('seat-capitals::index');
        Route::get('/applications', [ApplicationsController::class, 'index'])
            ->name('seat-capitals::applications.index');
        Route::post('/applications', [ApplicationsController::class, 'store'])
            ->name('seat-capitals::applications.store');
        Route::post('/applications/{application}/withdraw', [ApplicationsController::class, 'withdraw'])
            ->name('seat-capitals::applications.withdraw');
    });

    Route::group(['middleware' => 'can:capitals.review'], function (): void {
        Route::get('/review', [ReviewController::class, 'index'])
            ->name('seat-capitals::review.index');
        Route::post('/review/{application}/decide', [ReviewController::class, 'decide'])
            ->name('seat-capitals::review.decide');
        Route::get('/settings', [SettingsController::class, 'index'])
            ->name('seat-capitals::settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])
            ->name('seat-capitals::settings.update');
    });

    Route::group(['middleware' => 'can:character.capitals'], function (): void {
        Route::get('/report', [ReportController::class, 'index'])
            ->name('seat-capitals::report.index');
    });
});
