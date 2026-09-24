<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

/*
 * Permissions the plugin adds to SeAT's `character` scope.
 *
 * Living in the character scope makes SeAT's role editor offer the standard
 * character, corporation and alliance filters for the permission, so a role
 * can be limited to "characters in our corporation". The `gate` key replaces
 * SeAT's default character policy with one that also answers checks made
 * without a character, which is what the report page itself needs.
 */
return [
    'capitals' => [
        'label' => 'seat-capitals::capitals.permission_report_label',
        'description' => 'seat-capitals::capitals.permission_report_description',
        'gate' => \temetvince\SeatCapitals\Acl\ReportPolicy::class,
    ],
];
