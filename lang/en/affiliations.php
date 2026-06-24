<?php

return [
    // Page titles and headers
    'title' => 'Affiliations',
    'info_title' => 'Affiliations Management',
    'info_body' => 'View and manage all affiliations across the system. Monitor member affiliations, their status, and associated federations.',

    // Filter labels
    'member_type' => 'Member Type',
    'status' => 'Status',
    'federation' => 'Federation',
    'member_name' => 'Member Name',
    'start_date' => 'Start Date',
    'end_date' => 'End Date',

    // Table headers
    'table' => [
        'member' => 'Member',
        'type' => 'Type',
        'federation' => 'Federation',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'fee' => 'Fee',
        'status' => 'Status',
    ],

    // Status labels
    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'expired' => 'Expired',
        'pending_payment' => 'Pending Payment',
    ],

    // Actions
    'view_member' => 'View Member',
    'delete' => 'Delete',
    'member' => 'Member',

    // Data placeholders
    'member_not_found' => 'Member not found',
    'no_federation' => 'No federation',
    'no_date' => 'No date',
    'no_fee' => 'No fee',
    'via_entity' => 'via entity',

    // Messages
    'no_affiliations_found' => 'No affiliations found matching your criteria.',
    'affiliations_empty' => 'No affiliations have been created yet.',
    'status_updated_successfully' => 'Affiliation status updated successfully.',
    'status_update_failed' => 'Failed to update affiliation status. Please try again.',
    'deleted_successfully' => 'Affiliation deleted successfully.',
    'delete_failed' => 'Failed to delete affiliation. Please try again.',

    // Delete confirmation
    'confirm_delete_title' => 'Delete Affiliation',
    'confirm_delete_message' => 'Are you sure you want to delete this affiliation? This action cannot be undone.',
    'delete_confirm' => 'Delete Affiliation',

    // Status change confirmation
    'confirm_status_change' => 'Are you sure you want to change the status of this affiliation?',

    // Individual profile table
    'active_affiliations' => 'Active Affiliations',
    'affiliation_count' => '{0} No affiliations|{1} :count affiliation|[2,*] :count affiliations',
    'no_active_affiliations' => 'No active affiliations',
    'plan' => 'Plan',
    'period' => 'Period',
    'privileges' => 'Privileges',
    'standard_plan' => 'Standard Plan',
    'until' => 'until',
    'active' => 'Active',
    'expired' => 'Expired',
    'validation_plan' => 'Validation Plan',
    'insurance_requests' => 'Insurance Requests',
    'license_requests' => 'License Requests',
    'standard' => 'Standard',
];
