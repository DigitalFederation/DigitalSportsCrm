<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    'title' => 'Individual Members',
    'name' => 'Name',
    'surname' => 'Surname',
    'given_name' => 'Given Name',
    'family_name' => 'Family Name',
    'nationality' => 'Nationality',
    'member_number' => 'Member Number',
    'id_number' => 'ID Number',
    'affiliation_status' => 'Affiliation Status',
    'federation_portal' => $portalName,
    'cmas_portal' => "{$internationalShortName} Portal",
    'active' => 'Active',
    'inactive' => 'Inactive',
    'yes' => 'Yes',
    'no' => 'No',
    'gender' => 'Gender',
    'male' => 'Male',
    'female' => 'Female',
    'other' => 'Other',
    'federation_portal_access' => "{$portalName} Access",
    'has_federation_portal_account' => "Has {$portalName} Account",
    'federation_portal_description' => "Check this box if the individual has an account on {$portalName}",
    'cmas_portal_access' => "{$internationalShortName} Portal Access",
    'has_cmas_portal_account' => "Has {$internationalShortName} Portal Account",
    'cmas_portal_description' => "Check this box if the individual has an account on the {$internationalShortName} Portal",
    'national_fed_nr' => 'National Fed. Nr',
    'birthdate' => 'Birthdate',
    'instructors_leaders' => 'Instructor & Leaders',
    'coachs' => 'Coachs',
    'referees_judges' => 'Referees/Judges',
    'create_individual' => 'Create Individual',
    'individuals_to_approve' => 'Individuals to Approve',
    'latest_entries' => 'Latest entries',

    // Federation membership
    'federation_and_organizations' => 'Federations and Organizations',
    'federation_id' => 'ID',
    'federation_name' => 'Name',
    'membership_status' => 'Membership Status',
    'confirm_disassociate_federation' => 'Are you sure you want to disassociate from this federation?',
    'cannot_disassociate_main_federation' => 'You cannot disassociate from the main federation.',
    'member_number_already_taken' => 'This Member Number is already assigned to another individual.',
    'federation_edit_only' => '*Only the National Federation can edit this information',
];
