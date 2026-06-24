<?php

return [
    'title' => 'Professional Roles',
    'create' => 'Create Professional Role',
    'edit' => 'Edit Professional Role',
    'edit_title' => 'Edit Role',
    'information_title' => 'Information',
    'update' => 'Update Professional Role',

    // Fields
    'name' => 'Name',
    'code' => 'Code',
    'role_type' => 'Role Type',
    'committee' => 'Committee',
    'committee_all' => 'All',

    // Hints
    'code_hint' => 'Unique uppercase identifier (e.g., FINSWIMMINGCOACH)',
    'role_type_hint' => 'Category this role belongs to (affects event enrollment eligibility)',

    // Filters
    'filter_by_name' => 'Filter by name...',

    // Info boxes
    'info_title' => 'Professional Role Information',
    'info_body' => 'Professional roles define what activities a person can perform (e.g., Coach, Referee, Judge). The role type determines how the system treats this role for event enrollment and eligibility checks.',
    'edit_info_title' => 'Information',
    'edit_info_body' => 'Professional roles define the activities a person can perform within the federation. The role type determines eligibility for events: ATHLETE for competitors, COACH for team coaches, TECHNICAL_OFFICIAL for referees and judges, INSTRUCTOR for course instructors, DIVER for recreational/professional divers, STAFF for federation operational support, and FEDERATION_STAFF for federation administrative roles.',

    // Success messages
    'created_successfully' => 'Professional role created successfully.',
    'updated_successfully' => 'Professional role updated successfully.',
    'deleted_successfully' => 'Professional role deleted successfully.',
    'role_assigned_successfully' => 'Professional role assigned successfully.',
    'role_removed_successfully' => 'Professional role removed successfully.',

    // Error messages
    'cannot_delete_has_individuals' => 'Cannot delete this professional role because it is assigned to individuals.',
    'cannot_delete_has_certifications' => 'Cannot delete this professional role because it is linked to certifications.',
    'cannot_delete_has_licenses' => 'Cannot delete this professional role because it is linked to licenses.',
    'delete_failed' => 'Failed to delete professional role. Please try again.',

    // Role types
    'role_types' => [
        'ATHLETE' => 'Athlete',
        'COACH' => 'Coach',
        'TECHNICAL_OFFICIAL' => 'Technical Official',
        'INSTRUCTOR' => 'Instructor',
        'LEADER' => 'Leader',
        'DIVER' => 'Diver',
        'STAFF' => 'Staff',
        'DIVINGPROFESSIONAL' => 'Diving Professional',
        'FEDERATION_STAFF' => 'Federation Staff',
    ],

    // Actions
    'manage_roles' => 'Manage Roles',
];
