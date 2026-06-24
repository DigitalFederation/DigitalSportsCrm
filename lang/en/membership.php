<?php

return [
    // Validation messages
    'package_required' => 'A membership package must be selected',
    'invalid_package' => 'The selected membership package is invalid',
    'individuals_required' => 'At least one individual must be selected',
    'min_one_individual' => 'Select at least one individual',

    // Success messages
    'subscriptions_created' => 'Subscriptions Created',
    'success_count' => ':count subscriptions were successfully created',
    'payment_required_count' => ':count require payment documents',
    'free_subscriptions_count' => ':count are free and active',

    // Error messages
    'some_subscriptions_failed' => 'Some Subscriptions Failed',
    'failed_count' => ':count subscriptions could not be created. Please check the logs for details',
    'error' => 'Error',
    'unexpected_error' => 'An unexpected error occurred while processing subscriptions',
    'unauthorized_action' => 'Unauthorized action',

    // Action buttons
    'retry_failed' => 'Retry Failed',
    'retry_failed_title' => 'Retry Failed Subscriptions',
    'retry_failed_description' => 'Would you like to retry creating the failed subscriptions?',
    'yes_retry' => 'Yes, retry',
    'no_cancel' => 'No, cancel',
    'try_again' => 'Try Again',
    // Headers and titles
    'select_package' => 'Select one of the plans to assign to the selected members of your entity.',
    'select_insurance_package' => 'Select one of the insurance plans to assign to the selected members of your entity.',
    'select_members' => 'Select Members',
    'entity_member_memberships_title' => 'Entity Member Membership Plans',
    'entity_member_insurances_title' => 'Entity Member Insurance Plans',
    'selected' => 'Selected',

    // Search and filters
    'search_placeholder' => 'Search members by name or ID...',
    'filter' => [
        'all_status' => 'All Status',
        'active_subscription' => 'Active Subscription',
        'no_subscription' => 'No Subscription',
    ],

    // Table headers
    'table' => [
        'name' => 'Name',
        'id' => 'ID',
        'status' => 'Status',
    ],

    // Status labels
    'status' => [
        'active' => 'Active',
        'no_subscription' => 'No Subscription',
    ],

    // Messages
    'no_members_found' => 'No members found matching your criteria.',

    // Selection tray
    'selected_members' => 'Selected Members',
    'click_to_view' => 'click to view',
    'clear_all' => 'Clear all',
    'remove_selection' => 'Remove from selection',
    'total_selected' => ':count member(s) selected',
    'estimated_total' => 'Estimated total',

    // Actions
    'actions' => [
        'cancel' => 'Cancel',
        'subscribe_selected' => 'Subscribe Selected Members (:count)',
        'confirm' => 'Confirm',
    ],

    // Modal
    'modal' => [
        'confirm_title' => 'Confirm Subscription',
        'confirm_message' => 'You are about to subscribe the selected members to the following package:',
        'price' => 'Price',
        'subscription_count' => 'This action will create new subscriptions for :count members.',
    ],
];
