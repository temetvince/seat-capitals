<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

/*
 * Permissions registered under the `capitals` scope.
 *
 * Each key becomes the permission `capitals.<key>`. Labels and descriptions
 * are translation keys resolved by SeAT's role editor. Permission keys are
 * stored in users' roles, so renaming one strips it from every role.
 *
 * The report permission lives in the `character` scope instead (see
 * Permissions/character.php) so that SeAT offers affiliation filters for it.
 */
return [
    'apply' => [
        'label' => 'seat-capitals::capitals.permission_apply_label',
        'description' => 'seat-capitals::capitals.permission_apply_description',
    ],
    'review' => [
        'label' => 'seat-capitals::capitals.permission_review_label',
        'description' => 'seat-capitals::capitals.permission_review_description',
    ],
    'report_all' => [
        'label' => 'seat-capitals::capitals.permission_report_all_label',
        'description' => 'seat-capitals::capitals.permission_report_all_description',
    ],
];
