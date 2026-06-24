<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'entity_created' => [
        'subject' => 'Welcome to :app',
        'greeting' => 'Greetings, :name!',
        'line1' => 'An account has been created for your entity.',
        'line2' => 'To manage your entity profile and explore the functionalities of our platform, please set your password.',
        'action' => 'Set your password',
        'line3' => 'Once your password is set, you will have full access to your dashboard.',
        'line4' => 'We look forward to your active participation.',
        'line5' => 'Thank you for being a part of :app. If you have any questions, feel free to contact us.',
        'salutation' => 'Warm regards, The :app Team',
    ],

    'welcome_email' => [
        'title' => 'Welcome Email',
        'user_email' => 'User email',
        'sent_status' => 'Sent',
        'not_sent_status' => 'Not sent',
        'send_button' => 'Send Welcome Email',
        'resend_button' => 'Resend Welcome Email',
        'confirm_send' => 'Are you sure you want to send the welcome email?',
        'description' => 'This email contains a link for the user to set their password and activate their account.',
        'sent' => 'Welcome email sent successfully.',
        'failed' => 'Failed to send welcome email.',
        'no_user' => 'No user account associated.',
    ],

    // Payment notifications
    'payment_made' => 'A payment of :value has been made.',

    // Event notifications
    'event_enrollment_confirmed' => 'Your event enrollment has been confirmed.',
    'event_registration_confirmed' => 'Your event registration has been confirmed.',

    // Request notifications
    'request_approved' => 'Your request to join :federation has been approved.',
    'federation_request_approved' => "Your request to join {$portalName} has been approved.",
    'association_request_accepted' => 'Association request accepted successfully.',
    'error_accepting_request' => 'Error accepting the request.',
    'request_join_accepted' => ':name\'s request to join has been accepted.',
    'request_rejected' => 'Individual request rejected successfully.',
    'error_rejecting_request' => 'Failed to reject the individual request.',
    'request_deleted' => 'Individual request deleted successfully.',

    // Document notifications
    'document_created' => [
        'subject' => 'Document Creation Notification',
        'greeting' => 'Notification',
        'line' => "The document :invoice is available on {$portalName}. Click the button below to access {$portalName}, where you can check the document status in the Payments menu.",
        'action' => 'Open Document',
    ],

    'admin_license_attributed' => [
        'subject' => 'New License requested',
        'greeting' => 'Notification',
        'line_intro' => 'A new license has been requested.',
        'line_license' => '**License Name:** :name',
        'line_holder' => '**Holder Name:** :holder',
        'line_federation' => '**Federation Name:** :federation',
        'action' => 'View Details',
    ],

    'membership_create' => [
        'intro' => 'A new membership has been assigned. It will become active after payment is confirmed.',
        'action' => 'Open membership',
        'outro' => 'Thank you for using our application!',
        'database' => 'A new membership was assigned. It will become active after payment is confirmed.',
    ],

    'entity_approval' => [
        'subject' => 'Entity Approval Required',
        'greeting' => 'Hello :name,',
        'line_intro' => 'There is a new Entity pending your approval.',
        'line_entity' => 'Entity Name: :entity',
        'action' => 'View Entity',
        'line_review' => 'Please review the entity details and proceed with the approval process.',
        'salutation_regards' => 'Best Regards,',
        'salutation_team' => ':app Team',
        'database' => 'A new entity requires your approval.',
    ],

    'entity_member_accepted' => [
        'subject' => 'New member accepted: :name',
        'greeting' => 'Hello!',
        'line_accepted' => ':name has accepted the invitation to be a member of :entity.',
        'line_active' => 'This member is now active in your entity.',
        'action' => 'View members',
        'salutation' => 'Regards,<br>The :app Team',
        'database' => ':name has accepted the invitation to be a member.',
    ],

    'entity_member_invitation' => [
        'subject' => 'Invitation to be a member of :entity',
        'greeting' => 'Hello!',
        'line_invited' => ':inviter has invited you to be a member of their entity.',
        'line_instructions' => 'To accept this invitation, log into the platform and navigate to \'Entities\' in the side menu.',
        'action' => 'View invitation',
        'line_ignore' => 'If you were not expecting this invitation, you can ignore this email.',
        'salutation' => 'Regards,<br>The :app Team',
        'database' => 'The entity :entity has invited you to be a member.',
    ],

    'entity_request' => [
        'database_title' => 'New Entity Request',
        'database_message' => 'You have a new request from :name to join.',
    ],

    'export_ready' => [
        'line_intro' => 'Your export is ready for download. Check email for the link.',
        'action' => 'Download Export',
        'database' => 'Your export is ready for download.',
    ],

    'federation_join_request' => [
        'database' => ':name has requested to join the Federation.',
    ],

    'individual_request_license' => [
        'line' => 'There is a new :type license to approve.',
        'database' => 'There is a new :type license to approve.',
    ],

    'instructor_new_certification' => [
        'line' => 'There is a new certification to approve.',
        'action' => 'Open',
        'database' => 'There is a new certification to approve.',
    ],

    'invite_individual_professional' => [
        'subject' => 'Invitation to be :role',
        'greeting' => 'Hello :name!',
        'line_invited' => 'You have been invited to be :role of :entity.',
        'action' => 'Check the invite',
        'line_thanks' => 'Thank you for considering our invitation!',
        'salutation' => 'Regards, :app',
        'database' => 'You have been invited to be :role of :entity.',
    ],

    'membership_activation' => [
        'line_activated' => 'Membership :name was activated successfully.',
        'action' => 'Open membership',
        'salutation' => $primaryShortName,
        'database' => 'Membership :name was activated successfully.',
    ],

    'membership_expiration' => [
        'line_expires' => 'Your membership :name will expire on :date.',
        'action' => 'Open membership',
        'outro' => 'Thank you for using our application!',
    ],

    'official_document_activated' => [
        'database' => 'Document :name was approved.',
    ],

    'official_document_created' => [
        'database' => 'Official Document :name has been sent.',
    ],

    'official_document_deleted' => [
        'database' => 'Document :name has been deleted.',
    ],

    'report_generated' => [
        'line_ready' => 'Your report is ready.',
        'action' => 'Download the report',
        'line_auth' => 'You need to be authenticated to download the report.',
        'database' => 'Your report download is ready. Click here to download.',
    ],
];
