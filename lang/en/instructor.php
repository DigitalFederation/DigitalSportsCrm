<?php

$internationalName = config('branding.international.name', 'International Federation');

return [
    'instructors_and_leaders_entities' => "{$internationalName} Diving Entities",
    'information' => 'Information',
    'information_body' => 'In this area you can manage the diving entities you are associated with as an instructor or dive leader.',

    // Pending invitations
    'pending_invitations' => 'Pending Invitations',
    'pending_invitations_body' => 'You have the following pending invitations to associate with an entity. Accepting will link your account based on your active licenses for the relevant committee.',

    // Associations
    'your_associations' => 'Your Associations',
    'confirm_action' => 'Are you sure you want to confirm?',

    // Actions
    'disassociate' => 'Disassociate',
    'view_entity' => 'View Entity',

    // Table headers
    'table' => [
        'entity' => 'Entity',
        'member_number' => 'Member Number',
        'email' => 'Email',
        'type' => 'Type',
        'acceptance_date' => 'Acceptance Date',
        'status' => 'Status',
    ],
];
