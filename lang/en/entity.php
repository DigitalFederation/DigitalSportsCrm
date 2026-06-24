<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'members_list' => 'Members List',
    'member_list' => 'Member List',
    'entities' => 'Collective Entities',
    'entity_detail' => 'Entity Detail',
    'entities_to_approve' => 'Entities to Approve',
    'create_entity' => 'Create Entity',
    'create_entity_account' => 'Create a Entity account',
    'edit_entity_record' => 'Edit Entity record',

    // Actions
    'create_individual' => 'Create Individual',
    'individuals_to_approve' => 'Individuals to Approve',
    'invite_member' => 'Invite Member',
    'submit_request' => 'Submit Request',
    'approve_entity' => 'Approve entity',
    'accept_request' => 'Accept this request?',
    'view_all' => 'View All',
    'see_all_instructors' => 'See all instructors',
    'open_url' => 'Open url',

    // Table headers
    'gender' => 'Gender',
    'id_number' => 'ID Number',
    'national_affiliation' => "{$primaryShortName} Affiliation",
    'table_name' => 'Name',
    'table_country' => 'Country',
    'table_national_fed_nr' => 'National Fed. Nr',
    'table_cmas_zone' => 'International Zone',
    'table_sub_region' => 'Sub-region',
    'table_actions' => 'Actions',
    'table_nationality' => 'Nationality',
    'table_email' => 'Email',
    'table_requested' => 'Requested',
    'table_federation' => 'Organization',
    'table_type' => 'Type',
    'table_status' => 'Member Status',
    'table_national_number' => 'National Number',
    'table_number' => 'Number',
    'table_date' => 'Date',
    'table_total' => 'Total',
    'table_zone_or_association' => 'Zone or Territorial Association',

    // Form labels
    'name' => 'Entity Name',
    'given_name' => 'Given Name',
    'family_name' => 'Family Name',
    'nationality' => 'Nationality',
    'federation' => 'Federation',
    'birthdate' => 'Birthdate',
    'member_number' => 'Member Number',
    'affiliation_status' => 'Affiliation Status',
    'affiliation_active' => 'Active',
    'affiliation_inactive' => 'Inactive',
    'valid_member_code' => 'Valid member code',

    // Form sections
    'information' => 'Information',
    'entity_logo' => 'Entity Logo',
    'club_school_center_name' => 'Club/School/Center Name',
    'legal_name' => 'Fiscal Registration Name',
    'responsible_person_name' => 'Responsible person name',
    'nif' => 'Tax ID (NIF)',
    'national_fed_nr' => 'National Fed. Nr.',
    'affiliate_nr' => 'Affiliate Nr.',
    'hq_location' => 'HQ Location',
    'district' => 'District',
    'zones' => 'Zones',
    'no_zones_assigned' => 'No zones assigned',
    'address' => 'Address',
    'location' => 'Location',
    'zip_code' => 'Zip Code',
    'country' => 'Country',
    'select_option' => '-- Select an option --',
    'public_contacts' => 'Public Contacts',
    'contact_email' => 'Contact Email',
    'website' => 'Website',
    'phone_number' => 'Phone Number',
    'social_media_links' => 'Social Media Links',
    'facebook_url' => 'Facebook URL',
    'x_url' => 'X URL',
    'instagram_url' => 'Instagram URL',
    'linkedin_url' => 'LinkedIn URL',

    // Terms and policies
    'terms_policies' => 'Terms & Policies',
    'terms_confirm' => 'I confirm that the entity accepts the',
    'terms_of_service' => 'Terms of Service',
    'and' => 'and',
    'privacy_policy' => 'Privacy Policy',
    'data_sharing_confirm' => 'I confirm that the entity consents to the sharing of data with authorized third parties as described in the',
    'data_sharing_policy' => 'Data Sharing Policy',
    'save_record' => 'Save record',

    // User login section
    'user_login_information' => 'User login information',
    'user_login_info_description' => 'After choosing the user email address, an email will be sent in order for the person to register their own credentials.',
    'user_login_email' => 'User login email',
    'confirm_user_login_email' => 'Confirm user login email',
    'confirm_email_address' => 'Confirm the email address',
    'email_credential_hint' => 'Email credential for the user to login',
    'entity_creation_info' => 'When an Entity record is created, a user is also automatically assigned to this record. An email will be sent to the chosen email address in order for the user to register their own credentials. After that the person can login into the platform.',

    // Modal content
    'member_invitation_form' => 'Member Invitation Form',
    'member_request' => 'Member Invitation',
    'member_request_description' => 'You can use this form to invite members using their Personal ID or Member Number. You should request one of these from the member before sending this invitation.',
    'or_separator' => 'OR',

    // Zone assignment
    'zone_auto_assigned' => 'The zone will be automatically assigned based on your association.',
    'zone_will_be' => 'Zone',
    'zone_edit_restricted' => "Only {$primaryShortName} or Admin can edit this field.",

    // Entity approval
    'approval_national_federation_message' => 'You are about to approve this entity. A member number will be automatically assigned.',
    'approval_association_message' => 'You are about to approve this entity for your association.',
    'member_number_auto_generated' => 'The member number will be automatically generated upon approval.',
    'member_number_primary_federation_only' => "Note: Only {$primaryShortName} can assign the national federation number. The entity will be approved for your association without a member number.",

    // Show page
    'tax_identification_number' => 'Tax Identification Number',
    'hq_address_city_postal' => 'HQ Address, City, Postal Code',
    'individuals' => 'Individuals',
    'diving_certifications' => 'Diving Certifications',
    'scientific_certifications' => 'Scientific Certifications',
    'diving_licenses' => 'Diving Service Provider Licenses',
    'scientific_licenses' => 'Scientific Licenses',
    'sport_licenses' => 'Sport Licenses',
    'instructors' => 'Instructors',
    'active' => 'active',
    'no_instructors_yet' => 'No instructors yet',
    'federations' => 'Federation(s)',
    'associations' => 'Associations',
    'federation_and_associations' => 'Federation & Associations',
    'no_individuals_yet' => 'No individuals yet',
    'local_federation' => 'Association',
    'main_federation' => 'Main Federation',
    'no_federation_memberships' => 'No federation memberships found',
    'no_association_memberships' => 'No association memberships found',
    'table_association' => 'Association',
    'association_type_territorial' => 'Territorial',
    'association_type_nacional' => 'Nacional',
    'association_type_modalidade' => 'Modalidade',

    // Documents
    'documents_invoices' => 'Documents & Invoices',
    'view' => 'View',
    'no_documents_found' => 'No Documents Found',
    'no_documents_description' => 'No documents or invoices have been generated for this entity yet.',
    'showing_last_documents' => 'Showing last :count documents',

    // Messages
    'invalid_cmas_code' => 'The international code is invalid. Please confirm the information provided.',
    'invalid_member_number' => 'The member number is invalid. Please confirm the information provided.',
    'member_must_have_federation' => 'This member must have a federation relationship (active or pending) and must not be already part of your entity.',
    'invitation_sent_success' => 'The member invitation was sent with success. Please allow time for the member to review your request.',
    'error_creating_record' => 'Error creating this record: :error',

    // Entity Profile Tabs
    'no_certifications_message' => 'This entity has no certifications attributed yet.',
    'no_licenses_message' => 'This entity has no licenses attributed yet.',

    // Federation membership
    'designation' => 'Designation',
    'member_approved' => 'Approved Member',
    'member_pending_approval' => 'Pending Approval',
    'federation_membership_info' => 'This table shows your membership status in the Federation and Associations.',

    // Entity Dashboard
    'dashboard' => [
        'entity_profile' => 'Entity Profile',
        'members_to_approve' => 'Members to Approve',
        'no_pending_members' => 'No pending member requests',
        'entity_affiliations' => 'Entity Affiliations',
        'no_affiliations' => 'No affiliations found',
        'no_sport_licenses' => 'No sport licenses',
        'no_diving_licenses' => 'No diving licenses',
        'no_entity_found' => 'Entity Not Found',
        'no_entity_associated' => 'No entity is associated with your account.',
    ],

    // Error messages
    'committee_not_found' => 'Required committee for :type entity type is not configured. Please contact support.',

    // Map
    'get_directions' => 'Get Directions',

    // International Portal
    'cmas_portal_access' => 'International Portal Access',
    'has_cmas_portal_account' => 'Has International Portal Account',
    'cmas_portal_description' => 'Check this box if the entity has an account on the International Portal',

    // Public Page Management
    'public_page' => [
        'title' => 'Public Page Management',
        'subtitle' => 'Manage your organization\'s public profile and content',
        'view_public_page' => 'View Public Page',
        'tabs' => [
            'general' => 'General Settings',
            'featured_locations' => 'Featured Locations',
            'courses' => 'Diving Courses',
        ],
        'background_image' => 'Profile Background Image',
        'current_background' => 'Current Background',
        'current_image' => 'Current image',
        'confirm_remove_background' => 'Are you sure you want to remove the background image?',
        'background_removed' => 'Background image removed successfully.',
        'upload_file' => 'Upload a file',
        'or_drag_drop' => 'or drag and drop',
        'image_requirements' => 'PNG, JPG, WEBP up to 2MB',
        'preview' => 'Preview',
        'public_description' => 'Public Description',
        'description_help' => 'This description will be displayed on your public profile page.',
        'save_settings' => 'Save Settings',
        'settings_saved' => 'Settings saved successfully.',
        'featured_locations' => [
            'title' => 'Featured Diving Locations',
            'description' => 'Select the diving locations you want to highlight on your public profile.',
            'select_locations' => 'Select Locations',
            'no_locations_selected' => 'No diving locations selected.',
            'selected_preview' => 'Selected Locations Preview',
            'save_locations' => 'Save Featured Locations',
            'locations_saved' => 'Featured locations updated successfully.',
            'create_new' => 'Create New Location',
        ],
    ],
];
