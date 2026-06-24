<?php

return [
    'title' => 'Integration Issues',
    'subtitle' => 'Consolidated view of Moloni and Easypay integration errors',

    // Statistics
    'total_errors' => 'Total Errors',
    'errors_today' => 'Errors Today',
    'last_30_days' => 'Last 30 days',
    'last' => 'Last',

    // Error types
    'moloni_error_types' => 'Moloni Error Types',
    'easypay_error_types' => 'Easypay Error Types',

    // Filters
    'integration' => 'Integration',
    'from_date' => 'From Date',
    'to_date' => 'To Date',

    // Table
    'recent_errors' => 'Recent Errors',
    'showing_count' => 'Showing :count errors',
    'type' => 'Type',
    'error_message' => 'Error Message',
    'reference' => 'Reference',
    'date' => 'Date',
    'retry' => 'Retry',

    // Empty state
    'no_errors' => 'No Integration Errors',
    'no_errors_description' => 'All integrations are working correctly in the selected period.',

    // Navigation
    'moloni_settings' => 'Moloni Settings',
    'webhook_logs' => 'Webhook Logs',

    // Troubleshooting
    'troubleshooting_title' => 'Common Troubleshooting Tips',
    'troubleshooting_moloni_auth' => 'Moloni authentication errors: Check if the Moloni connection is still active in Moloni Settings.',
    'troubleshooting_moloni_config' => 'Moloni invoice errors: Verify that the document set, tax, and other settings are properly configured.',
    'troubleshooting_easypay_webhook' => 'Easypay webhook errors: Check if the transaction exists and the payment status is correct.',
    'troubleshooting_easypay_transaction' => 'Easypay transaction errors: Verify the document status and payment configuration.',
];
