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
use temetvince\SeatCapitals\Models\ApplicationStatus;

/**
 * Shape check for a reviewer's decision.
 *
 * @package temetvince\SeatCapitals\Http\Validation
 */
class Decision extends FormRequest
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
        $decisions = implode(',', array_map(
            fn (ApplicationStatus $status) => $status->value,
            ApplicationStatus::decisions()
        ));

        return [
            'decision' => 'required|in:' . $decisions,
            'note' => 'nullable|string|max:2000',
        ];
    }
}
