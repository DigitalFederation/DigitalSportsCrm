<?php

return [
    'title' => 'Route Permission Management',
    'route_permissions' => 'Route Permissions',
    'scan_routes' => 'Scan Routes',
    'assign_permissions' => 'Assign Permissions',
    'bulk_assign' => 'Bulk Assign',
    'routes_to_assign' => 'Routes to Assign Permissions',
    'route_details' => 'Route Details',
    'permission_mapping' => 'Permission Mapping',

    // Route fields
    'route_name' => 'Route Name',
    'uri' => 'URI Pattern',
    'methods' => 'HTTP Methods',
    'controller' => 'Controller',
    'middleware' => 'Middleware',
    'current_permission' => 'Current Permission',
    'assigned_permission' => 'Assigned Permission',
    'suggested_permissions' => 'Suggested Permissions',
    'module' => 'Module',
    'prefix' => 'Prefix',
    'parameters' => 'Parameters',
    'status' => 'Status',
    'uncategorized' => 'Uncategorized',

    // Filters
    'filter_by_module' => 'Filter by Module',
    'all_modules' => 'All Modules',
    'all_permissions' => 'All Permissions',
    'filter_by_prefix' => 'Filter by Prefix',
    'filter_by_permission' => 'Filter by Permission Status',
    'has_permission' => 'Has Permission',
    'no_permission' => 'No Permission',
    'all_routes' => 'All Routes',
    'search_routes' => 'Search routes...',

    // Statistics
    'total_routes' => 'Total Routes',
    'routes_with_permissions' => 'Routes with Permissions',
    'routes_without_permissions' => 'Routes without Permissions',
    'percentage_protected' => 'Protection Coverage',
    'dynamic_mappings' => 'Dynamic Mappings',
    'active_mappings' => 'Active Mappings',

    // Actions
    'scan' => 'Scan',
    'assign' => 'Assign',
    'assign_permission' => 'Assign Permission',
    'edit_permission_assignment' => 'Edit Permission Assignment',
    'current_permissions' => 'Current Permissions',
    'no_permissions_assigned' => 'No permissions assigned',
    'add_permission' => 'Add Permission',
    'confirm_remove_permission' => 'Are you sure you want to remove this permission?',
    'remove' => 'Remove',
    'activate' => 'Activate',
    'deactivate' => 'Deactivate',
    'select_all' => 'Select All',
    'deselect_all' => 'Deselect All',
    'apply_suggestions' => 'Apply Suggestions',
    'create_permission' => 'Create Permission',
    'export_mappings' => 'Export Mappings',

    // Route groups
    'grouped_by_module' => 'Routes Grouped by Module',
    'module_statistics' => 'Module Statistics',
    'route_group' => 'Route Group',

    // Permission assignment
    'select_permission' => 'Select Permission',
    'no_permission_assigned' => 'No Permission Assigned',
    'permission_exists' => 'Permission Exists',
    'permission_not_exists' => 'Permission Does Not Exist',
    'create_and_assign' => 'Create & Assign',
    'active' => 'Active',
    'inactive' => 'Inactive',

    // Bulk operations
    'bulk_operations' => 'Bulk Operations',
    'selected_routes' => 'Selected Routes',
    'bulk_assign_permissions' => 'Bulk Assign Permissions',
    'apply_to_selected' => 'Apply to Selected',
    'preview_changes' => 'Preview Changes',

    // Impact preview
    'impact_preview' => 'Impact Preview',
    'new_mappings' => 'New Mappings',
    'updated_mappings' => 'Updated Mappings',
    'removed_mappings' => 'Removed Mappings',
    'affected_routes' => 'Affected Routes',
    'affected_permissions' => 'Affected Permissions',

    // Messages
    'messages' => [
        'no_routes_selected' => 'No routes selected. Please select at least one route.',
        'permission_updated' => 'Route permission updated successfully.',
        'permission_assigned' => 'Permission assigned successfully.',
        'permission_removed' => 'Route permission removed successfully.',
        'bulk_assign_success' => 'Bulk assignment completed: :created created, :updated updated.',
        'scan_complete' => 'Route scan completed. Found :count routes.',
        'export_success' => 'Route permissions exported successfully.',
        'no_routes_found' => 'No routes found matching the criteria.',
        'confirm_remove' => 'Are you sure you want to remove this permission mapping?',
        'try_adjusting_filters' => 'Try adjusting your filters or search criteria.',
        'routes_selected' => 'routes selected',
        'assigning' => 'Assigning...',
        'assigned' => 'Assigned',
    ],

    // Errors
    'errors' => [
        'bulk_assign_failed' => 'Bulk assignment failed: :error',
        'assignment_failed' => 'Assignment failed: :error',
        'permission_update_failed' => 'Failed to update route permission: :error',
        'scan_failed' => 'Route scan failed: :error',
        'export_failed' => 'Export failed: :error',
    ],

    // Help text
    'help' => [
        'route_scanning' => 'Scanning analyzes all registered routes in the application to identify which ones have permissions and which need them.',
        'permission_suggestions' => 'Suggestions are based on route naming patterns and common CRUD operations.',
        'dynamic_mappings' => 'Dynamic mappings allow you to assign permissions to routes without modifying code.',
        'bulk_assignment' => 'Select multiple routes and assign the same permission to all of them at once.',
        'protection_coverage' => 'Shows the percentage of routes that have permission protection.',
    ],
];
