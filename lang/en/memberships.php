<?php

return [
    // Subscription Creation
    'subscription_created_successfully' => 'Subscription created successfully. Please proceed with payment.',
    'subscription_created_pending_payment' => 'Subscription created successfully. Please proceed with payment.',
    'insurance_subscription_created_pending_payment' => 'Insurance subscription created successfully! Please complete the payment to activate your insurance coverage.',
    'subscription_created_free' => 'Subscription created successfully.',
    'subscription_creation_error' => 'An error occurred while processing your subscription. Please try again.',
    'subscription_already_pending' => 'You already have a pending subscription for this package.',
    'subscription_already_pending_payment' => 'You already have a pending subscription for this package. Please complete the payment to activate it.',

    // Document Generation
    'affiliation_description' => 'Affiliation: :name - :federation',
    'insurance_description' => 'Insurance: :name',
    'subscription_document_notes' => 'Subscription to package: :package',
    'bulk_subscription_document_note' => 'Bulk subscription for :count members - Package: :package',

    // Document Observer
    'activating_subscription_after_payment' => 'Activating member subscription after payment',
    'subscription_activated' => 'Member subscription activated',

    // Payment Flow
    'payment_required' => 'Payment required to complete subscription',
    'proceed_to_payment' => 'Please proceed to payment to activate your subscription',

    // Validation Messages
    'package_selection_required' => 'A membership package selection is required.',
    'package_selection_invalid' => 'The selected membership package is not valid.',
    'invalid_member_type' => 'Invalid member type for subscription.',
    'no_validation_affiliation_for_insurance' => 'An active validation affiliation is required to subscribe to insurance-only packages.',
    'no_active_affiliation_found' => 'No active affiliation found. A validation affiliation is required.',
    'duplicate_affiliation_plans' => 'You already have an active subscription to the following affiliation plans: :plans',
    'all_affiliation_plans_already_active' => 'You already have an active subscription to all affiliation plans in this package: :plans',
    'duplicate_insurance_plans' => 'You already have an active or pending subscription to the following insurance plans: :plans',
    'insufficient_privileges_for_request_type' => 'Insufficient privileges for this type of request.',
    'validation_plan_required_for_non_validation_packages' => 'Individual must have an active validation plan to subscribe to this membership package.',

    // Renewal
    'subscription_renewed_successfully' => 'Membership subscription renewed successfully.',

    // Individual Profile Messages
    'complete_profile_before_managing_subscriptions' => 'Please complete your individual profile before managing subscriptions.',

    // Affiliation Plan Business Scenarios
    'business_scenarios' => [
        'direct_individual' => [
            'label' => 'Direct Individual Subscription',
            'description' => 'Individuals subscribe directly to this plan themselves',
            'example' => 'Example: Personal annual membership, student rates',
        ],
        'entity_for_individuals' => [
            'label' => 'Entity subscribes for Individuals',
            'description' => 'Entities (clubs, schools) subscribe to this plan FOR their individual members',
            'example' => 'Example: Club pays for athlete memberships, diving center pays for student certifications',
        ],
        'direct_entity' => [
            'label' => 'Direct Entity Subscription',
            'description' => 'Entities subscribe to this plan for themselves (institutional membership)',
            'example' => 'Example: Club institutional membership, diving center certification',
        ],
        'flexible' => [
            'label' => 'Flexible Plan',
            'description' => 'Can be used by both individuals and entities with different pricing',
            'example' => 'Example: Premium plan with individual and institutional rates',
        ],
    ],

    // Form Labels
    'choose_business_scenario' => 'Choose Business Scenario',
    'business_scenario_help' => 'Select what type of subscription plan you want to create. This determines who can subscribe and how pricing works.',
    'plan_name' => 'Plan Name',
    'plan_name_help' => 'Choose a clear, descriptive name',
    'select_federation' => 'Select federation...',
    'pricing' => 'Pricing',
    'fee_individual_member' => 'Fee charged per individual member',
    'fee_individual_subscription' => 'Fee when subscribed by individuals',
    'fee_entity_institution' => 'Fee charged to the entity (institution)',
    'fee_entity_subscription' => 'Fee when subscribed by entities',
    'free_plan_option' => 'This is a free plan (set fees to €0)',
    'immediate_availability' => 'Leave empty for immediate availability',
    'no_expiration' => 'Leave empty for no expiration',
    'description_help' => 'Provide detailed information about what this plan includes, requirements, benefits, etc.',
    'pdf_documents' => 'PDF Documents',
    'upload_documents_help' => 'Upload terms, conditions, or other relevant documents. Max 10MB each.',
    'current_attachments' => 'Current Attachments',
    'uncheck_remove_files' => 'Uncheck to remove files',
    'plan_summary' => 'Plan Summary',
    'usage' => 'Usage',
    'create_plan_help' => 'Create a new affiliation plan by choosing the business scenario that best describes how this plan should work. The form will guide you through the appropriate settings.',
    'edit_plan_help' => 'Edit the details of this affiliation plan. The business scenario determines the plan structure and pricing options.',
    'complete_profile_before_selecting_subscription' => 'Please complete your individual profile before selecting a subscription.',
    'complete_profile_before_purchasing_subscription' => 'Please complete your individual profile before purchasing a subscription.',
    'complete_profile_before_viewing_history' => 'Please complete your individual profile before viewing subscription history.',
    'please_login_to_continue' => 'Please login to continue.',
    'profile_issue_contact_support' => 'There was an issue with your profile. Please contact support.',
    'subscription_not_eligible_for_renewal' => 'This subscription is not eligible for renewal.',
    'renewal_error_try_again' => 'An error occurred while renewing your subscription. Please try again.',
    'duplicate_affiliation_plans_error' => 'You already have an active subscription for one or more affiliation plans in this package.',

    // Official Document Requirements
    'missing_official_documents' => 'You cannot subscribe to this package because it requires official documents that you haven\'t uploaded or that are not active.',
    'insurance_requires_document' => 'Required: :document for :insurance.',

    // Validation Plan
    'validation_plan' => 'Validation Plan',
    'validation_plan_help' => 'Enable advanced privileges for subscribers of this plan',
    'validation_plan_enables' => 'Validation plans enable',
    'insurance_requests' => 'Request insurance policies',
    'license_requests' => 'Request licenses and certifications',
    'entity_member_licenses' => 'For entities: Request licenses for their members',

    // Validation Plan Error Messages
    'insurance_subscription_not_authorized' => 'Insurance subscription not authorized: :reason',
    'license_request_not_authorized' => 'License request not authorized: :reason',
    'entity_member_insurance_not_authorized' => 'Entity member insurance assignment not authorized: :reason',
    'entity_member_license_not_authorized' => 'Entity member license request not authorized: :reason',

    // Validation Plan Privilege Messages
    'validation_plan_no_insurance_privileges' => 'Your current membership plan does not include insurance request privileges',
    'validation_plan_no_license_privileges' => 'Your current membership plan does not include license request privileges',
    'validation_plan_no_entity_member_licenses' => 'Your current membership plan does not allow requesting licenses for entity members',
    'validation_plan_no_entity_member_subscriptions' => 'Your current membership plan does not allow subscribing members to packages',

    // Validation Plan UI Messages
    'validation_plan_required' => 'Validation Plan Required',
    'access_restricted' => 'Access Restricted',
    'contact_federation_validation_plan' => 'Please contact your federation to upgrade your validation plan to enable member subscription features.',
    'validation_plan_required_message' => 'A validation plan is required to subscribe members to packages.',
    'no_active_affiliation_found' => 'No active affiliation found',
    'entity_member_subscriptions_not_authorized' => 'You cannot subscribe members to packages. :reason',
    'invalid_member_type' => 'Invalid member type',
    'insufficient_privileges_for_request_type' => 'Insufficient privileges for this request type',

    // Subscription page
    'affiliations' => 'Affiliations',
    'active_affiliations' => 'Active Affiliations',
    'included_plans' => 'Included Plans',
    'affiliation_plans' => 'Affiliation Plans',

    // Member subscriptions
    'member_subscriptions' => [
        'created_successfully' => 'Member subscription created successfully.',
        'renewed_successfully' => 'Member subscription renewed successfully.',
        'delete' => 'Delete',
        'deleted_successfully' => 'Member subscription deleted successfully.',
        'delete_failed' => 'Failed to delete member subscription. Please try again.',
        'confirm_delete_title' => 'Delete Member Subscription',
        'confirm_delete_warning' => 'This action will permanently delete the member subscription and all related affiliations and insurances. This action cannot be undone.',
        'will_delete_related' => 'This will delete :affiliations affiliation(s) and :insurances insurance(s)',
        'delete_confirm' => 'Delete Subscription',
        'change_status' => 'Change Status',
        'change_status_title' => 'Change Subscription Status',
        'change_status_warning' => 'This will only change the subscription status. Payment documents, affiliations, and insurances will NOT be affected.',
        'new_status' => 'New Status',
        'update_status' => 'Update Status',
        'status_updated_successfully' => 'Member subscription status updated successfully.',
        'status_update_failed' => 'Failed to update member subscription status.',
        'pending_payment' => 'Pending Payment',
    ],

    // Notifications
    'subscription_activated_notification' => 'Your subscription to :package has been activated and is valid until :date.',

    // Membership states
    'states' => [
        'active' => 'Active',
        'pending' => 'Pending',
        'expired' => 'Expired',
        'canceled' => 'Canceled',
    ],

    // Member subscription states
    'subscription_states' => [
        'active' => 'Active',
        'pending' => 'Pending',
        'pending_payment' => 'Pending Payment',
        'expired' => 'Expired',
    ],

    // Table headers
    'title' => 'Memberships',
    'name' => 'Name',
    'plans' => 'Plans',
    'status' => 'Status',
    'expiration_date' => 'Expiration date',
    'organizations_membership_association' => 'Organizations Membership Association',
];
