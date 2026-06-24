<?php

return [
    'title' => 'Permission Management',
    'permission' => 'Permission',
    'permissions' => 'Permissions',
    'create_permission' => 'Create Permission',
    'edit_permission' => 'Edit Permission',
    'delete_permission' => 'Delete Permission',
    'permission_details' => 'Permission Details',
    'bulk_create' => 'Bulk Create Permissions',
    'import_permissions' => 'Import Permissions',
    'export_permissions' => 'Export Permissions',

    // Form fields
    'name' => 'Permission Name',
    'name_help' => 'Use lowercase with hyphens (e.g., manage-users)',
    'display_name' => 'Display Name',
    'description' => 'Description',
    'category' => 'Category',
    'guard' => 'Guard',
    'guard_name' => 'Guard Name',
    'roles_using' => 'Roles Using This Permission',
    'routes_using' => 'Routes Using This Permission',
    'created_by' => 'Created By',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Categories
    'uncategorized' => 'Uncategorized',
    'all_categories' => 'All Categories',
    'select_category' => 'Select Category',
    'new_category' => 'New Category',

    // Filters
    'filter_by_category' => 'Filter by Category',
    'filter_by_usage' => 'Filter by Usage',
    'has_roles' => 'Has Roles',
    'no_roles' => 'No Roles',
    'search_permissions' => 'Search permissions...',

    // Statistics
    'total_permissions' => 'Total Permissions',
    'system_permissions' => 'System Permissions',
    'custom_permissions' => 'Custom Permissions',
    'permissions_with_roles' => 'Permissions with Roles',
    'unused_permissions' => 'Unused Permissions',
    'permissions_with_routes' => 'Permissions with Routes',

    // Actions
    'actions' => 'Actions',
    'view' => 'View',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'cancel' => 'Cancel',
    'save' => 'Save',
    'create' => 'Create',
    'back_to_list' => 'Back to Permissions',
    'confirm_delete' => 'Confirm Delete',

    // Bulk operations
    'bulk_operations' => 'Bulk Operations',
    'add_permission_line' => 'Add Permission',
    'remove_line' => 'Remove',
    'default_category' => 'Default Category',
    'default_guard' => 'Default Guard',
    'apply_defaults' => 'Apply Defaults',

    // Import/Export
    'select_file' => 'Select CSV File',
    'download_template' => 'Download Template',
    'import' => 'Import',
    'export' => 'Export',

    // Status
    'system_permission' => 'System Permission',
    'protected' => 'Protected',
    'deletable' => 'Deletable',
    'in_use' => 'In Use',
    'not_used' => 'Not Used',

    // Impact analysis
    'deletion_impact' => 'Deletion Impact',
    'affected_roles' => 'Affected Roles',
    'affected_users' => 'Affected Users',
    'affected_routes' => 'Affected Routes',
    'roles_list' => 'Roles with this permission',

    // Messages
    'messages' => [
        'permission_created_successfully' => 'Permission created successfully.',
        'permission_updated_successfully' => 'Permission updated successfully.',
        'permission_deleted_successfully' => 'Permission deleted successfully.',
        'bulk_create_success' => ':count permissions created successfully.',
        'bulk_create_partial' => ':created permissions created, :failed failed.',
        'import_success' => 'Import completed: :created created, :skipped skipped.',
        'no_permissions_found' => 'No permissions found.',
        'no_permissions_added' => 'No permissions added yet.',
        'confirm_delete_message' => 'Are you sure you want to delete this permission? This action cannot be undone.',
    ],

    // Errors
    'errors' => [
        'permission_already_exists' => 'A permission with this name already exists.',
        'cannot_modify_system_permission' => 'System permissions cannot be modified.',
        'cannot_delete_system_permission' => 'System permissions cannot be deleted.',
        'permission_used_by_protected_roles' => 'This permission is used by protected roles: :roles',
        'permission_used_in_routes' => 'This permission is used in :count route(s).',
        'permission_creation_failed' => 'Failed to create permission: :error',
        'permission_update_failed' => 'Failed to update permission: :error',
        'permission_deletion_failed' => 'Failed to delete permission: :error',
        'bulk_create_failed' => 'Bulk creation failed: :error',
        'import_failed' => 'Import failed: :error',
        'invalid_permission_name' => 'Permission name must be lowercase with hyphens only.',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Permission name is required.',
        'name_unique' => 'This permission name already exists.',
        'name_format' => 'Permission name must be lowercase with hyphens (e.g., manage-users).',
        'description_too_long' => 'Description cannot exceed 1000 characters.',
        'category_too_long' => 'Category cannot exceed 100 characters.',
    ],

    // Help text
    'help' => [
        'naming_convention' => 'Use lowercase letters with hyphens between words (e.g., manage-users, view-reports)',
        'categories' => 'Categories help organize permissions. Common categories: Users, Roles, Content, Settings, Reports',
        'system_permissions' => 'System permissions are core permissions that cannot be modified or deleted.',
        'bulk_create' => 'Create multiple permissions at once. Each line will create a new permission.',
        'import_format' => 'CSV format: name, category, description, guard_name',
        'guard_cannot_be_changed' => 'The guard cannot be changed after creation for security reasons.',
    ],
];
