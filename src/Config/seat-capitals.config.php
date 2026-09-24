<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

/*
 * Operator-tunable configuration. SeAT publishes this file to config/seat-capitals.php
 * with `php artisan vendor:publish --tag=seat-capitals`.
 */
return [

    /*
     * The SDE inventory groups (invGroups.groupID) that count as capital hulls.
     * The value is a human-readable reminder only; the key is what the plugin uses.
     * A hull whose group is not listed here can neither be applied for nor
     * appears in the report.
     */
    'capital_groups' => [
        30 => 'Titan',
        485 => 'Dreadnought',
        513 => 'Freighter',
        547 => 'Carrier',
        659 => 'Supercarrier',
        883 => 'Capital Industrial Ship',
        902 => 'Jump Freighter',
        1538 => 'Force Auxiliary',
        4594 => 'Lancer Dreadnought',
    ],

];
