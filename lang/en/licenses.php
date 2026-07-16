<?php

$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalName = config('branding.international.name', 'International Federation');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    // Page titles
    'licenses' => 'Licenses',
    'my_licenses_description' => 'Here you can view all your licenses and purchase new member licenses',
    'view_my_licenses' => 'View My Licenses',
    'no_federation_association_description' => 'You are not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.',
    'no_international_license_access_description' => 'You are not associated with a federation that has international license agreements. Only members of federations with international agreements can purchase these licenses.',

    // Tab sections
    'basic_information' => 'Basic Information',
    'roles_permissions' => 'Roles & Permissions',
    'requirements' => 'Requirements',
    'pricing' => 'Pricing',
    'availability' => 'Availability',
    'advanced_settings' => 'Advanced Settings',

    // Document requirements sections
    'diving_professionals' => 'Diving Professionals',

    // Purchase page titles and headers
    'Purchase License' => 'Purchase License',
    'Manage Licenses' => 'Manage Licenses',
    'Manage Licenses for' => 'Manage Licenses for',
    'License Purchased Successfully!' => 'License Purchased Successfully!',
    'Purchase Successful!' => 'Purchase Successful!',
    'Purchase Successful' => 'Purchase Successful',
    'order_details' => 'Order Details',

    // Page descriptions
    'Select and purchase a license for yourself' => 'Select and purchase a license for yourself',
    'Purchase licenses for your entity or members' => 'Purchase licenses for your entity or members',

    // Information messages
    'Information' => 'Information',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. Please ensure your profile information is complete before proceeding.' => 'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. Please ensure your profile information is complete before proceeding.',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. For group purchases, you can select multiple members to receive the same license.' => 'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. For group purchases, you can select multiple members to receive the same license.',

    // Form labels and options
    'Select Federation' => 'Select Federation',
    'Select a federation...' => 'Select a federation...',
    'Select License' => 'Select License',
    'Purchase Type' => 'Purchase Type',
    'Individual License' => 'Individual License',
    'Group Purchase' => 'Group Purchase',
    'Select Member' => 'Select Member',
    'Select Members' => 'Select Members',
    'Select a member...' => 'Select a member...',

    // Purchase type descriptions
    'Purchase license for one specific member' => 'Purchase license for one specific member',
    'Purchase licenses for multiple members' => 'Purchase licenses for multiple members',

    // License information
    'License' => 'License',
    'License Code' => 'License Code',
    'License Holder' => 'License Holder',
    'License Information' => 'License Information',
    'per license' => 'per license',
    'license' => 'License',
    'start_date' => 'Start Date',
    'expiry_date' => 'Expiry Date',
    'status' => 'Status',

    // Purchase summary
    'Purchase Summary' => 'Purchase Summary',
    'Purchase Details' => 'Purchase Details',
    'Entity' => 'Entity',
    'Federation' => 'Federation',
    'Number of Members' => 'Number of Members',
    'Price per License' => 'Price per License',
    'Total' => 'Total',
    'Total Amount' => 'Total Amount',
    'Total Paid' => 'Total Paid',

    // Status and dates
    'Status' => 'Status',
    'Active' => 'Active',
    'Payment Confirmed' => 'Payment Confirmed',
    'Issue Date' => 'Issue Date',
    'Expiration Date' => 'Expiration Date',
    'Today' => 'Today',
    'Permanent' => 'Permanent',

    // International and codes
    'Pending Assignment' => 'Pending Assignment',
    'Order Number' => 'Order Number',

    // Success messages
    'Your license has been activated and is ready to use' => 'Your license has been activated and is ready to use',
    'Your license purchase has been completed successfully' => 'Your license purchase has been completed successfully',
    'All selected members have been automatically licensed' => 'All selected members have been automatically licensed',
    'Your entity license has been automatically activated' => 'Your entity license has been automatically activated',

    // Certificate information
    'Your License Certificate' => 'Your License Certificate',
    'Your license certificate is now available for download' => 'Your license certificate is now available for download',
    'License certificates are now available for download' => 'License certificates are now available for download',
    'A confirmation email has been sent to your registered email address' => 'A confirmation email has been sent to your registered email address',
    'You will receive email confirmation shortly' => 'You will receive email confirmation shortly',

    // Next steps and information
    'What happens next?' => 'What happens next?',
    'Important Information' => 'Important Information',
    'Remember to renew before expiration date' => 'Remember to renew before expiration date',

    // Action buttons
    'View My Licenses' => 'View My Licenses',
    'Download Invoice' => 'Download Invoice',
    'Download Certificate' => 'Download Certificate',
    'Back to Dashboard' => 'Back to Dashboard',

    // Error messages
    'no_license_purchase_found' => 'No license purchase found.',
    'entity_license_required_for_members' => 'Your entity must have an active entity license before you can purchase member licenses. Please purchase an entity license first.',
    'entity_sport_license_required' => 'Your entity must have an active entity license for this sport before you can purchase member licenses for it. Please purchase an entity license for this sport first.',
    'No licenses available' => 'No licenses available',
    'There are no licenses available for purchase in this federation at the moment.' => 'There are no licenses available for purchase in this federation at the moment.',
    'There are no licenses available for entity purchase at the moment.' => 'There are no licenses available for entity purchase at the moment.',
    'No Federation Association' => 'No Federation Association',
    'You are not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'You are not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.',
    'No federation' => 'No federation',

    // Dynamic messages with parameters
    'Purchase for' => 'Purchase for',
    'Purchase for €:amount' => 'Purchase for €:amount',
    'Purchase for :amount' => 'Purchase for :amount',
    'Request Free License' => 'Request Free License',
    ':count members selected' => ':count members selected',
    'This license certifies you for: :role' => 'This license certifies you for: :role',
    'Valid for sport: :sport' => 'Valid for sport: :sport',
    'members' => 'members',
    'Members' => 'Members',

    // Federation License Manager
    'Select which licenses this federation can offer to its member entities.' => 'Select which licenses this federation can offer to its member entities.',
    'Search Licenses' => 'Search Licenses',
    'Search by name or code...' => 'Search by name or code...',
    'Filter by Committee' => 'Filter by Committee',
    'All Committees' => 'All Committees',
    'selected' => 'selected',
    'International' => 'International',
    'No licenses found matching your filters.' => 'No licenses found matching your filters.',
    'No licenses available.' => 'No licenses available.',
    'license(s) selected' => 'license(s) selected',
    'Cancel' => 'Cancel',
    'Save Changes' => 'Save Changes',
    'Licenses updated successfully!' => 'Licenses updated successfully!',

    // Debug information messages
    'cannot_proceed_with_purchase' => 'Cannot proceed with purchase:',
    'entity_no_active_affiliation' => 'Entity does not have active affiliation',
    'no_license_selected' => 'No license selected',
    'price_not_calculated' => 'Price not calculated',
    'calculated_price' => 'calculated price',
    'no_members_selected' => 'No members selected',
    'no_members_for_entity' => 'No members found for this entity. Please ensure your entity has associated individuals.',
    'validation_plan' => 'Validation plan',

    // Affiliation messages
    'Active Affiliation Required' => 'Active Affiliation Required',
    'Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.' => 'Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.',
    'You must have an active affiliation (membership package) to purchase licenses. Please ensure your individual membership is active and paid before proceeding.' => 'You must have an active affiliation (membership package) to purchase licenses. Please ensure your individual membership is active and paid before proceeding.',

    // License validation error messages
    'already_has_license' => 'You already have an :status license of this type',
    'Your profile already has this Active License' => 'Your profile already has this Active License',
    'Your license is pending payment' => 'Your license is pending payment',
    'missing_required_documents_detailed' => 'Cannot request this license. The following required documents are missing: :documents. Please upload these documents in the Official Documents section before requesting this license.',
    'missing_required_certifications' => 'Cannot request this license. The following required certifications are missing: :certifications. Please obtain these certifications before requesting this license.',
    'members_missing_required_certifications' => 'The following members do not have the required certifications: :members',
    'license_requirements' => 'License Requirements',
    'required_certifications' => 'Required certifications',
    'required_documents' => 'Required documents',
    'member_missing_certifications' => 'Missing certifications: :certifications',
    'member_missing_documents' => 'Missing documents: :documents',
    'member_must_have_active_affiliation' => 'Member must have an active affiliation',
    'show_ineligible_members' => 'Show ineligible members',
    'hide_ineligible_members' => 'Hide ineligible members',
    'member_not_eligible' => 'This member does not meet requirements',
    'no_eligible_members' => 'No eligible members for this license',
    'some_members_ineligible' => ':eligible of :total members are eligible for this license',
    'entity' => 'entity',
    'individual' => 'individual',
    'license_cannot_be_purchased_by' => 'This license cannot be purchased by :type',
    'license_request_not_authorized' => 'License request not authorized: :reason',
    'license_parameter_null' => 'License parameter is null',
    'license_missing_properties' => 'License is missing required properties (id or license_code)',
    'cannot_determine_federation' => 'Cannot determine federation for license purchase',
    'license_price_not_configured' => 'License price not configured for this purchaser type',

    // License fields
    'license_type' => 'License Type',
    'license_number' => 'License Number',
    'valid_until' => 'Valid Until',
    'acceptance_date' => 'Acceptance Date',
    'issue_date' => 'Issue Date',
    'expiration_date' => 'Expiration Date',

    // Error messages for purchase flow
    'This license is not free' => 'This license is not free.',
    'This license cannot be purchased with this method' => 'This license cannot be purchased with this method.',

    // License status messages
    'Your profile already has this Active License' => 'Your profile already has this Active License',
    'Your license is pending payment' => 'Your license is pending payment',
    'Your license is pending admin validation' => 'Your license is pending admin validation',
    'Your license is pending technical director approval' => 'Your license is pending technical director approval',
    'Your license is being processed' => 'Your license is being processed',

    // New form translations
    'Search licenses' => 'Search Licenses',
    'Search licenses...' => 'Search licenses...',
    'licenses found' => 'licenses found',
    'Sport Committee' => 'Sport Committee',
    'All Sports' => 'All Sports',
    'Role' => 'Role',
    'Price' => 'Price',
    'Free' => 'Free',
    'Select' => 'Select',
    'Purchase' => 'Purchase',
    'Request' => 'Request',
    'Contact Support' => 'Contact Support',
    'Membership Required' => 'Membership Required',

    // Admin validation
    'license_pending_validation_requires_approval' => 'License is pending validation and requires admin approval.',
    'validate_and_approve' => 'Validate & Approve',
    'reject_validation' => 'Reject Validation',

    // Entity pending licenses
    'entity_has_pending_licenses' => 'Your entity has pending licenses awaiting payment',
    'invitations_available_after_payment' => 'Athlete and coach invitations will be available once license payment is completed',
    'complete_payment_to_enable_invitations' => 'Complete payment to activate your licenses and enable invitation features',
    'pending_licenses_for_sports' => 'Pending licenses for: :sports',
    'license_approved_successfully' => 'License approved successfully.',
    'error_approving_license' => 'Error approving license: ',
    'license_not_in_approvable_state' => 'License is not in a state that allows approval',
    'license_validation_rejected' => 'License validation rejected',
    'license_canceled' => 'License canceled',
    'cannot_activate_unpaid_license' => 'Cannot activate license: payment has not been completed. Please ensure the associated payment document is paid before activating.',

    // License state translations
    'statuses' => [
        'ActiveLicenseAttributedState' => 'Active',
        'PendingLicenseAttributedState' => 'Pending',
        'PendingTechnicalDirectorApprovalLicenseAttributedState' => 'Pending TD Approval',
        'PendingValidationLicenseAttributedState' => 'Pending Admin Validation',
        'CanceledLicenseAttributedState' => 'Canceled',
        'SuspendedLicenseAttributedState' => 'Suspended',
        'ExpiredLicenseAttributedState' => 'Expired',
        'ProvisionalLicenseAttributedState' => 'Provisional',
    ],

    // State translations for the states themselves
    'states' => [
        'pending' => 'Pending',
        'active' => 'Active',
        'expired' => 'Expired',
        'suspended' => 'Suspended',
        'canceled' => 'Canceled',
        'provisional' => 'Provisional',
        'waiting_approval' => 'Waiting Approval',
        'pending_validation' => 'Pending Validation',
        'pending_technical_director_approval' => 'Pending Technical Director Approval',
        'no_license' => 'No License',
    ],

    // International License Specific
    'Active Affiliation Required' => 'Active Affiliation Required',
    'You must have an active affiliation (membership package) to purchase international licenses. Please ensure your individual membership is active and paid before proceeding.' => 'You must have an active affiliation (membership package) to purchase international licenses. Please ensure your individual membership is active and paid before proceeding.',
    'Search international licenses' => 'Search international licenses',
    'Search international licenses...' => 'Search international licenses...',
    'international licenses found' => 'international licenses found',
    'International License' => 'International License',
    'No international licenses available' => 'No international licenses available',
    'No international licenses are currently available for your federation.' => 'No international licenses are currently available for your federation.',
    'No international licenses match your search criteria.' => 'No international licenses match your search criteria.',
    'Purchase International License' => 'Purchase International License',
    'International License Purchase Success' => 'International License Purchase Success',
    'Purchase Initiated Successfully' => 'Purchase Initiated Successfully',
    'Your international license purchase has been initiated. Please complete the payment to activate your license.' => 'Your international license purchase has been initiated. Please complete the payment to activate your license.',
    'International License Details' => 'International License Details',
    'View National Licenses' => 'View National Licenses',
    'Select and purchase an international license for yourself' => 'Select and purchase an international license for yourself',
    'No International License Access' => 'No International License Access',
    'Back to International Licenses' => 'Back to International Licenses',
    'View My International Licenses' => 'View My International Licenses',
    'Purchase International Licenses for Members' => 'Purchase International Licenses for Members',
    'Select members and purchase international licenses on their behalf' => 'Select members and purchase international licenses on their behalf',
    'Purchase International Entity License' => 'Purchase International Entity License',
    'Purchase an international license for your organization' => 'Purchase an international license for your organization',
    'Switch to International Entity License Purchase' => 'Switch to International Entity License Purchase',
    'Switch to International Member License Purchase' => 'Switch to International Member License Purchase',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing international licenses.' => 'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing international licenses.',

    // Table headers
    'licenses_title' => 'Licenses',
    'name' => 'Name',
    'license_name' => 'License Name',
    'year' => 'Year',
    'actions' => 'Actions',
    'sport_commission' => 'Sport Commission',
    'sport_categories' => 'Sport Categories',
    'not_active' => 'Not Active',
    'assign_individual_license' => 'Assign Individual License',
    'assign_entity_license' => 'Assign Entity License',

    // Separated license page titles
    'Sport Club Licenses' => 'Sport Club Licenses',
    'Sport Licenses' => 'Sport Licenses',
    'International Entity Licenses' => "{$internationalName} Entity Licenses",
    'International Professional Licenses' => "{$internationalName} Professional Licenses",
    'Scientific Entity Licenses' => 'Scientific Entity Licenses',
    'Scientific Professional Licenses' => 'Scientific Professional Licenses',
    'Primary Diving Services Licenses' => "{$primaryShortName} Diving Services Licenses",

    // Middleware error messages
    'entity_has_inactive_license' => 'Your entity has a :committee license, but it is not currently active. Please ensure your :committee license is active to access this feature.',
    'entity_needs_active_license' => 'Your entity needs an active :committee license to access this feature. Please contact your federation to obtain the necessary license.',

    // License states
    'state_active' => 'Active',
    'state_pending' => 'Pending',
    'state_canceled' => 'Canceled',
    'state_provisional' => 'Provisional',
    'state_suspended' => 'Suspended',
    'state_waiting_approval' => 'Waiting Approval',
    'state_expired' => 'Expired',
    'state_pending_validation' => 'Pending Validation',
    'state_pending_technical_director_approval' => 'Pending Technical Director Approval',

    // Payment status
    'payment_status' => 'Payment Status',
    'payment_status_paid' => 'Paid',
    'payment_status_pending_payment' => 'Pending Payment',
    'payment_status_no_document' => 'No Document',

    // Filter labels
    'filters' => [
        'first_name' => 'First Name',
        'surname' => 'Surname',
        'member_number' => 'Member Number',
        'sport' => 'Sport',
        'entity_name' => 'Entity',
    ],

    // Separated license purchase page titles and subtitles
    'Purchase Sport Club License' => 'Purchase Sport Club License',
    'Purchase a sport license for your club' => 'Purchase a sport license for your club',
    'Purchase Sport Licenses' => 'Purchase Sport Licenses',
    'Select members and purchase sport licenses on their behalf' => 'Select members and purchase sport licenses on their behalf',
    'Purchase International Entity License' => "Purchase {$internationalName} Entity License",
    'Purchase an international license for your entity' => "Purchase an {$internationalShortName} license for your entity",
    'Purchase International Professional Licenses' => "Purchase {$internationalName} Professional Licenses",
    'Select members and purchase international licenses on their behalf' => "Select members and purchase {$internationalShortName} licenses on their behalf",
    'Purchase Scientific Entity License' => 'Purchase Scientific Entity License',
    'Purchase a scientific license for your entity' => 'Purchase a scientific license for your entity',
    'Purchase Scientific Professional Licenses' => 'Purchase Scientific Professional Licenses',
    'Select members and purchase scientific licenses on their behalf' => 'Select members and purchase scientific licenses on their behalf',
    'Purchase Primary Diving Services Licenses' => "Purchase {$primaryShortName} Diving Services Licenses",
    'Select members and purchase primary diving licenses on their behalf' => "Select members and purchase {$primaryShortName} diving licenses on their behalf",

    // Generic, committee-label-driven fallbacks (used when a committee declares
    // no purchase title/subtitle of its own in config/committees.php).
    'Purchase :committee Entity License' => 'Purchase :committee Entity License',
    'Purchase a :committee license for your entity' => 'Purchase a :committee license for your entity',
    'Purchase :committee Licenses' => 'Purchase :committee Licenses',
    'Select members and purchase :committee licenses on their behalf' => 'Select members and purchase :committee licenses on their behalf',
    ':committee Entity Licenses' => ':committee Entity Licenses',
    ':committee Professional Licenses' => ':committee Professional Licenses',

    // Individual separated license purchase page titles
    'individual_sport_license_title' => 'Sport Professional Licenses',
    'individual_sport_license_subtitle' => 'Purchase licenses for referees and coaches',
    'individual_national_diving_license_title' => "{$primaryShortName} Diving Professional License",
    'individual_national_diving_license_subtitle' => "Purchase {$primaryShortName} diving professional license",
    'individual_cmas_diving_license_title' => "{$internationalShortName} Recreational Diving Professional License",
    'individual_cmas_diving_license_subtitle' => "Purchase {$internationalShortName} recreational diving professional license",
    'individual_scientific_license_title' => "{$internationalShortName} Scientific Diving Professional License",
    'individual_scientific_license_subtitle' => "Purchase {$internationalShortName} scientific diving professional license",

    // Individual separated licenses attributed page titles
    'individual_sport_licenses_title' => 'Sport Licenses',
    'individual_sport_licenses_subtitle' => 'Your sport licenses for athletes, coaches and technical officials',
    'individual_national_diving_licenses_title' => 'Professional Diving Licenses',
    'individual_national_diving_licenses_subtitle' => 'Your professional diving licenses',
    'individual_national_diving_licenses_info' => 'Here you can view and purchase new professional diving licenses',
    'individual_cmas_diving_licenses_title' => "{$internationalName} Licenses",
    'individual_cmas_diving_licenses_subtitle' => '',
    'individual_cmas_diving_licenses_info' => "Here you can view all your annual {$internationalName} professional diving licenses",
    'individual_scientific_licenses_title' => "{$internationalName} Licenses",
    'individual_scientific_licenses_subtitle' => '',
    'individual_scientific_licenses_info' => "Here you can view all your annual {$internationalName} professional diving licenses",

    // Other individual license translations
    'individual_licenses_info' => 'Here you can view all your licenses for athletes, coaches and technical officials',
    'sport' => 'Sport',
    'category' => 'Category',

    // Federation separated licenses attributed page titles
    'federation_sport_entity_licenses_title' => 'Sport Club Licenses',
    'federation_sport_entity_licenses_subtitle' => 'Sport licenses attributed to clubs',
    'federation_sport_individual_licenses_title' => 'Sport Individual Licenses',
    'federation_sport_individual_licenses_subtitle' => 'Sport licenses attributed to athletes and coaches',
    'federation_national_diving_entity_licenses_title' => "{$primaryShortName} Diving Center Licenses",
    'federation_national_diving_entity_licenses_subtitle' => "{$primaryShortName} diving licenses attributed to diving centers",
    'federation_national_diving_individual_licenses_title' => "{$primaryShortName} Diving Professional Licenses",
    'federation_national_diving_individual_licenses_subtitle' => "{$primaryShortName} diving licenses attributed to professionals",
    'federation_cmas_diving_entity_licenses_title' => 'International Diving Center Licenses',
    'federation_cmas_diving_entity_licenses_subtitle' => 'International diving licenses attributed to diving centers',
    'federation_cmas_diving_individual_licenses_title' => 'International Diving Professional Licenses',
    'federation_cmas_diving_individual_licenses_subtitle' => 'International diving licenses attributed to professionals',
    'federation_scientific_entity_licenses_title' => 'Scientific Diving Center Licenses',
    'federation_scientific_entity_licenses_subtitle' => 'Scientific diving licenses attributed to diving centers',
    'federation_scientific_individual_licenses_title' => 'Scientific Diving Professional Licenses',
    'federation_scientific_individual_licenses_subtitle' => 'Scientific diving licenses attributed to professionals',

    // Admin separated licenses attributed page titles
    'admin_sport_entity_licenses_title' => 'Sport Club Licenses',
    'admin_sport_entity_licenses_subtitle' => 'All sport licenses attributed to clubs',
    'admin_sport_individual_licenses_title' => 'Sport Individual Licenses',
    'admin_sport_individual_licenses_subtitle' => 'All sport licenses attributed to athletes and coaches',
    'admin_national_diving_entity_licenses_title' => "{$primaryShortName} Diving Center Licenses",
    'admin_national_diving_entity_licenses_subtitle' => "All {$primaryShortName} diving licenses attributed to diving centers",
    'admin_national_diving_individual_licenses_title' => "{$primaryShortName} Diving Professional Licenses",
    'admin_national_diving_individual_licenses_subtitle' => "All {$primaryShortName} diving licenses attributed to professionals",
    'admin_cmas_diving_entity_licenses_title' => 'International Entity Licenses',
    'admin_cmas_diving_entity_licenses_subtitle' => 'All international licenses attributed to entities',
    'admin_cmas_diving_individual_licenses_title' => 'Recreational Diving Professional Licenses',
    'admin_cmas_diving_individual_licenses_subtitle' => 'All licenses attributed to recreational diving professionals',
    'admin_scientific_entity_licenses_title' => 'Scientific Entity Licenses',
    'admin_scientific_entity_licenses_subtitle' => 'All scientific diving licenses attributed to entities',
    'admin_scientific_individual_licenses_title' => 'Scientific Diving Professional Licenses',
    'admin_scientific_individual_licenses_subtitle' => 'All scientific diving licenses attributed to professionals',

    // Committee names (for translation)
    'Technical Committee' => 'Technical Committee',
    'Scientific Committee' => 'Scientific Committee',

    // International license field
    'is_international_label' => "{$internationalName} License",
    'is_international_help' => "If you check this option, this license will only be available to {$internationalName} instructors/leaders and entities.",

    // International licenses page
    'international_licenses' => 'International Licenses',
    'cmas_international_licenses' => 'International Licenses',
    'international_licenses_description' => 'Your international licenses recognized worldwide',
    'view_national_licenses' => 'View National Licenses',
    'purchase_international_license' => 'Purchase International License',
    'license' => 'License',
    'federation' => 'Federation',
    'sport_category' => 'Sport/Category',
    'validity' => 'Validity',
    'international_code' => 'International Code',
    'active' => 'Active',
    'pending' => 'Pending',
    'cancelled' => 'Cancelled',
    'unknown' => 'Unknown',
    'view' => 'View',
    'documents' => 'Documents',
    'no_international_licenses' => 'No international licenses',
    'no_international_licenses_message' => 'You have not purchased any international licenses yet.',

    // License purchase success page
    'License Purchase Initiated!' => 'License Purchase Initiated!',
    'Your license purchase is being processed. You will receive a confirmation once payment is complete.' => 'Your license purchase is being processed. You will receive a confirmation once payment is complete.',
    'You can view and manage your license in the My Licenses section' => 'You can view and manage your license in the My Licenses section',
    'Payment Required' => 'Payment Required',
    'Your license is pending payment to be activated' => 'Your license is pending payment to be activated',
    'Please complete the payment to activate your license and download the certificate' => 'Please complete the payment to activate your license and download the certificate',
    'An invoice has been generated and is available for download' => 'An invoice has been generated and is available for download',
    'Pending Payment' => 'Pending Payment',
    'Complete Payment' => 'Complete Payment',
    'Payment integration coming soon' => 'Payment integration coming soon',

    // DIVINGSERVICES certification requirement
    'active_diving_certification_required' => 'Active Diving Certification Required',
    'active_diving_certification_required_description' => 'You must have an active diving professional certification to request a diving professional license.',

    // License detail page actions
    'pending_payment_message' => 'License is pending payment confirmation. It will be automatically activated once payment is processed.',
    'waiting_approval_message' => 'License is waiting for approval.',
    'provisional_message' => 'License is provisional and can be activated.',
    'manually_activate' => 'Manually Activate License',
    'cancel_license' => 'Cancel License',
    'suspend_license' => 'Suspend License',
    'reactivate_license' => 'Reactivate License',
    'approve_license' => 'Approve License',
    'reject_license' => 'Reject License',
    'activate_provisional' => 'Activate Provisional License',
    'confirm_manual_activate' => 'Are you sure you want to manually activate this license?',
    'confirm_cancel' => 'Are you sure you want to cancel this license?',
    'confirm_suspend' => 'Are you sure you want to suspend this license?',
    'confirm_reactivate' => 'Are you sure you want to reactivate this license?',
    'confirm_approve' => 'Are you sure you want to approve this license?',
    'confirm_reject' => 'Are you sure you want to reject this license?',
    'confirm_activate_provisional' => 'Are you sure you want to activate this provisional license?',
    'confirm_validate_approve' => 'Are you sure you want to validate and approve this license?',
    'confirm_reject_validation' => 'Are you sure you want to reject this license validation?',
];
