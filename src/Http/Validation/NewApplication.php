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
 * Shape check for a submitted application. Ownership and hull rules are the workflow's job.
 *
 * @package temetvince\SeatCapitals\Http\Validation
 */
class NewApplication extends FormRequest
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
            'character_id' => 'required|integer',
            'type_id' => 'required|integer',
            'justification' => 'required|string|max:2000',
        ];
    }
}
