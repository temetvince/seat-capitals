<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

/*
 * The plugin's group in SeAT's left sidebar.
 *
 * SeAT merges this into `package.sidebar` at the top level only, so the group
 * key must be unique across every installed package. Labels are translation
 * keys; SeAT passes them through `trans()` when it renders the menu. The
 * group carries no permission of its own: each entry is shown to users
 * holding its permission, so the group appears whenever at least one entry
 * does.
 */
return [
    'seat-capitals' => [
        'name' => 'seat-capitals',
        'label' => 'seat-capitals::capitals.title',
        'icon' => 'fas fa-space-shuttle',
        'route_segment' => 'capitals',
        'entries' => [
            [
                'name' => 'applications',
                'label' => 'seat-capitals::capitals.menu_applications',
                'permission' => 'capitals.apply',
                'icon' => 'fas fa-file-signature',
                'route' => 'seat-capitals::applications.index',
            ],
            [
                'name' => 'review',
                'label' => 'seat-capitals::capitals.menu_review',
                'permission' => 'capitals.review',
                'icon' => 'fas fa-gavel',
                'route' => 'seat-capitals::review.index',
            ],
            [
                'name' => 'report',
                'label' => 'seat-capitals::capitals.menu_report',
                'permission' => 'character.capitals',
                'icon' => 'fas fa-chart-bar',
                'route' => 'seat-capitals::report.index',
            ],
            [
                'name' => 'settings',
                'label' => 'seat-capitals::capitals.menu_settings',
                'permission' => 'capitals.review',
                'icon' => 'fas fa-cog',
                'route' => 'seat-capitals::settings.index',
            ],
        ],
    ],
];
