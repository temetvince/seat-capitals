<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

return [
    'title' => 'Capitals',

    // Sidebar
    'menu_applications' => 'My applications',
    'menu_review' => 'Review applications',
    'menu_report' => 'Capital report',
    'menu_settings' => 'Settings',

    // Permissions
    'permission_apply_label' => 'Apply for a capital',
    'permission_apply_description' => 'Submit, follow and withdraw capital build applications for own characters.',
    'permission_review_label' => 'Review capital applications',
    'permission_review_description' => 'See every capital build application, approve or deny it, and manage the plugin settings.',
    'permission_report_label' => 'Capital ship report',
    'permission_report_description' => 'See which capital hulls the characters in scope own and where they are. Use the filters to limit the scope to a corporation or alliance.',

    // Statuses
    'status_pending' => 'Pending',
    'status_approved' => 'Approved',
    'status_denied' => 'Denied',
    'status_withdrawn' => 'Withdrawn',

    // Application form
    'apply_heading' => 'Apply to build a capital',
    'apply_intro' => 'Tell the reviewers which hull you want to build, for which character, and why. You will be notified once a decision is made.',
    'field_character' => 'Character',
    'field_hull' => 'Hull',
    'field_justification' => 'Justification',
    'field_justification_help' => 'What the hull is for and how it will be used. Up to 2000 characters.',
    'field_note' => 'Note to the applicant',
    'field_note_help' => 'Optional. Shown to the applicant with the decision.',
    'button_submit' => 'Submit application',
    'button_withdraw' => 'Withdraw',
    'button_approve' => 'Approve',
    'button_deny' => 'Deny',
    'button_cancel' => 'Cancel',
    'button_refresh' => 'Refresh',
    'button_save' => 'Save',
    'my_applications' => 'My applications',

    // Review
    'review_heading' => 'Applications',
    'review_intro' => 'Pending applications carry approve and deny actions. Decided applications stay listed for reference.',
    'approve_title' => 'Approve this application?',
    'deny_title' => 'Deny this application?',

    // Report
    'report_heading' => 'Capital hulls',
    'report_filters' => 'Filters',
    'filter_systems' => 'Systems',
    'filter_systems_placeholder' => 'Search for a system or wormhole',
    'filter_systems_help' => 'Leave empty to list hulls everywhere. Pre-filled with the configured home systems.',
    'filter_include_alts' => 'Include alts',
    'filter_include_alts_help' => 'Also list hulls owned by other characters on the same SeAT accounts, even outside the corporation.',
    'assembled' => 'Assembled',
    'packaged' => 'Packaged',

    // Columns
    'column_character' => 'Character',
    'column_main' => 'Main',
    'column_hull' => 'Hull',
    'column_class' => 'Class',
    'column_ship_name' => 'Ship name',
    'column_state' => 'State',
    'column_system' => 'System',
    'column_status' => 'Status',
    'column_justification' => 'Justification',
    'column_submitted' => 'Submitted',
    'column_reviewer' => 'Reviewer',
    'column_note' => 'Note',

    // Settings
    'settings_heading' => 'Home systems',
    'settings_home_systems' => 'Home systems',
    'settings_home_systems_help' => 'The report filter starts with these systems selected. Leave empty to open the report unfiltered.',
    'settings_saved' => 'Settings saved.',

    // Flash messages
    'submitted' => 'Your application has been submitted.',
    'withdrawn' => 'Your application has been withdrawn.',
    'decided_approved' => 'The application has been approved.',
    'decided_denied' => 'The application has been denied.',

    // Workflow errors, keyed by WorkflowException reason
    'error_character_not_owned' => 'That character is not linked to your account.',
    'error_type_not_capital' => 'That hull is not a capital ship.',
    'error_duplicate_pending' => 'You already have a pending application for that hull on that character.',
    'error_not_pending' => 'That application has already been decided or withdrawn.',
    'error_not_applicant' => 'Only the applicant can withdraw an application.',
    'error_not_a_decision' => 'That is not a valid decision.',

    // Notification alerts, as listed in SeAT's notification groups
    'alert_application_created' => 'Capitals: new build application',
    'alert_application_decided' => 'Capitals: application approved or denied',
];
