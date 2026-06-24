<?php

return [
    // Page titles
    'title' => 'Eligibility Diagnostic Center',
    'subtitle' => 'Diagnose why individuals may not appear in enrollment lists',

    // Tab titles
    'tab_individual_profile' => 'Individual Profile',
    'tab_event_enrollment' => 'Event Enrollment',
    'tab_license_availability' => 'License Availability',

    // Individual Profile Tab
    'individual_profile_title' => 'Individual Profile Diagnostic',
    'individual_profile_description' => 'Search for an individual to see their complete eligibility profile and understand why they can or cannot be enrolled for different roles.',
    'search_placeholder' => 'Search by international code, name, or email...',
    'no_individual_selected' => 'No Individual Selected',
    'search_to_start' => 'Search for an individual to view their eligibility profile.',
    'quick_status' => 'Quick Status',

    // Role labels
    'role_athlete' => 'Athlete',
    'role_coach' => 'Coach',
    'role_referee' => 'Referee',
    'role_official' => 'Official',

    // Sections
    'federation_memberships' => 'Federation Memberships',
    'entity_memberships' => 'Entity Memberships',
    'professional_roles' => 'Professional Roles',
    'certifications' => 'Certifications (Referee Check)',
    'active_licenses' => 'Active Licenses',

    // Table headers
    'federation' => 'Federation',
    'entity' => 'Entity',
    'type' => 'Type',
    'status' => 'Status',
    'since' => 'Since',
    'sports' => 'Sports',
    'role' => 'Role',
    'source' => 'Source',
    'certification' => 'Certification',
    'grants_role' => 'Grants Role',
    'action_needed' => 'Action Needed',
    'license' => 'License',
    'expires' => 'Expires',

    // Federation types
    'local' => 'Local',
    'main' => 'Main',
    'modalidade' => 'Sport',

    // Empty states
    'no_federation_memberships' => 'No federation memberships found.',
    'no_entity_memberships' => 'No entity memberships found.',
    'no_professional_roles' => 'No professional roles assigned.',
    'no_certifications' => 'No certifications attributed.',
    'no_active_licenses' => 'No active licenses.',
    'unknown_federation' => 'Unknown Federation',
    'unknown_entity' => 'Unknown Entity',
    'unknown_license' => 'Unknown License',
    'unknown_certification' => 'Unknown Certification',

    // Sources
    'source_direct_assignment' => 'Direct Assignment',
    'source_entity_assignment' => 'Entity Assignment',

    // Certification action
    'action_activate_certification' => 'ACTIVATE to enable role',

    // Quick status reasons
    'not_checked' => 'Not checked',
    'reason_no_active_federation' => 'No active federation membership',
    'reason_no_active_entity' => 'No active entity membership',
    'reason_not_registered_athlete' => 'Not registered as athlete',
    'reason_registered_athlete' => 'Registered as athlete',
    'reason_no_coach_role' => 'No COACH professional role',
    'reason_has_coach_role' => 'Has COACH role assigned',
    'reason_cert_pending_activation' => 'Certification exists but is PENDING activation',
    'reason_no_referee_cert' => 'No referee certification attributed',
    'reason_no_referee_role' => 'No REFEREE professional role (check certification)',
    'reason_has_referee_role' => 'Has REFEREE role assigned',
    'reason_no_active_membership' => 'No active membership',
    'reason_active_member' => 'Active member',

    // Event Enrollment Tab
    'event_enrollment_title' => 'Event Enrollment Diagnostic',
    'event_enrollment_description' => 'Select an event and individual to diagnose why they may not appear in the enrollment list for a specific role.',
    'select_event' => 'Select Event',
    'select_event_placeholder' => '-- Select an event --',
    'select_competition' => 'Select Competition (optional)',
    'all_competitions' => '-- All competitions --',
    'select_role' => 'Role to Diagnose',
    'search_individual' => 'Search Individual',
    'run_diagnostic' => 'Run Diagnostic',
    'selected' => 'Selected',
    'select_event_first' => 'Select an Event First',
    'select_event_to_start' => 'Choose an event from the dropdown to begin the diagnostic.',

    // Diagnostic results
    'eligible_as_role' => 'ELIGIBLE as :role',
    'not_eligible_as_role' => 'NOT ELIGIBLE as :role',
    'passed' => 'PASSED',
    'failed' => 'FAILED',
    'suggestions' => 'Suggested Actions',

    // Check labels
    'check_federation_membership' => 'Federation Membership',
    'check_entity_membership' => 'Entity Membership',
    'check_athlete_registration' => 'Athlete Registration',
    'check_coach_role' => 'Coach Professional Role',
    'check_referee_role' => 'Referee Professional Role',
    'check_referee_cert_exists' => 'Referee Certification Exists',
    'check_referee_cert_active' => 'Certification is Active',
    'check_required_certs' => 'Required Certifications',
    'check_required_licenses' => 'Required Licenses',
    'check_active_membership' => 'Active Membership',
    'check_not_enrolled' => 'Not Already Enrolled',

    // Check messages - Passed
    'check_federation_membership_passed' => 'Active member of :federation',
    'check_federation_membership_athlete_passed' => 'Has active federation membership',
    'check_federation_membership_coach_passed' => 'Has active federation membership',
    'check_entity_membership_passed' => 'Active member of: :entities',
    'check_entity_membership_passed_coach' => 'Has active entity membership',
    'check_athlete_registration_passed' => 'Registered as athlete for :sport',
    'check_coach_role_passed' => 'Has COACH professional role assigned',
    'check_referee_role_passed' => 'Has REFEREE professional role assigned',
    'check_referee_cert_exists_passed' => 'Has referee certification(s): :certs',
    'check_referee_cert_active_passed' => 'Has at least one active referee certification',
    'check_required_certs_passed' => 'Has all required certifications',
    'check_required_licenses_passed' => 'Has all required licenses',
    'check_active_membership_passed' => 'Has active membership (can be enrolled as official)',
    'check_not_enrolled_passed' => 'Not yet enrolled in this event',

    // Check messages - Failed
    'check_federation_membership_failed' => 'No active federation membership found',
    'check_entity_membership_failed' => 'No active entity membership found',
    'check_athlete_registration_failed' => 'Not registered as athlete in any entity',
    'check_athlete_wrong_sport' => 'Registered for :registered but event requires :required',
    'check_coach_role_failed' => 'Does not have COACH professional role assigned',
    'check_referee_role_failed' => 'Does not have REFEREE professional role assigned',
    'check_referee_role_cert_pending' => 'Certification ":cert" exists but is PENDING - REFEREE role not yet assigned',
    'check_referee_cert_exists_failed' => 'No referee-type certification attributed',
    'check_referee_cert_no_certs' => 'No referee certifications to check',
    'check_referee_cert_pending' => 'Referee certification(s) exist but are PENDING: :certs',
    'check_referee_cert_inactive' => 'No active referee certifications found',
    'check_required_certs_failed' => 'Missing required certification(s): :certs',
    'check_required_licenses_failed' => 'Missing required license(s): :licenses',
    'check_active_membership_failed' => 'No active membership in any federation or entity',
    'check_already_enrolled' => 'Already enrolled in this event for this role',

    // Suggestions
    'suggestion_activate_membership' => 'Activate federation/entity membership',
    'suggestion_join_entity' => 'Join an entity as a member',
    'suggestion_register_as_athlete' => 'Register as athlete at Entity > Athletes',
    'suggestion_register_for_sport' => 'Register as athlete for the correct sport',
    'suggestion_assign_coach_role' => 'Assign COACH role at Entity > Coaches',
    'suggestion_attribute_referee_cert' => 'Attribute a referee certification at Federation > Certifications',
    'suggestion_activate_certification' => 'ACTIVATE the pending certification to grant REFEREE role',
    'suggestion_check_cert_status' => 'Check certification status - may be expired or cancelled',
    'suggestion_obtain_required_cert' => 'Obtain and activate the required certification(s)',
    'suggestion_obtain_required_license' => 'Obtain and activate the required license(s)',

    // Membership details
    'member_of_federations' => 'Federation(s): :federations',
    'member_of_entities' => 'Entity(ies): :entities',

    // License Availability Tab
    'license_availability_title' => 'License Availability Diagnostic',
    'license_availability_description' => 'Diagnose why certain licenses may not appear in the purchase list.',
    'coming_soon' => 'Coming soon...',
];
