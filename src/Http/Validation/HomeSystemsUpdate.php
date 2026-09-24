<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Http\Validation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shape check for the home systems settings form.
 *
 * @package temetvince\SeatCapitals\Http\Validation
 */
class HomeSystemsUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'systems' => 'nullable|array',
            'systems.*' => 'integer',
        ];
    }
}
