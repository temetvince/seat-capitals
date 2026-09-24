<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Http\DataTables\Scopes;

use Yajra\DataTables\Contracts\DataTableScope;

/**
 * Limits the applications table to one applicant's rows.
 *
 * @package temetvince\SeatCapitals\Http\DataTables\Scopes
 */
class OwnApplicationsScope implements DataTableScope
{
    public function __construct(private readonly int $user_id)
    {
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\temetvince\SeatCapitals\Models\CapitalApplication>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\temetvince\SeatCapitals\Models\CapitalApplication>
     */
    public function apply($query)
    {
        return $query->where('seat_capitals_applications.user_id', $this->user_id);
    }
}
