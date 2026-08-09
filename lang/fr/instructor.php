<?php

$internationalName = config('branding.international.name', 'International Federation');

return [
    'instructors_and_leaders_entities' => "Entités de plongée {$internationalName}",
    'information' => 'Informations',
    'information_body' => 'Dans cet espace, vous pouvez gérer les entités de plongée auxquelles vous êtes associé en tant que moniteur ou guide de palanquée.',

    // Pending invitations
    'pending_invitations' => 'Invitations en attente',
    'pending_invitations_body' => 'Vous avez les invitations en attente suivantes pour vous associer à une entité. En acceptant, votre compte sera lié en fonction de vos licences actives pour le comité concerné.',

    // Associations
    'your_associations' => 'Vos associations',
    'confirm_action' => 'Êtes-vous sûr de vouloir confirmer ?',

    // Actions
    'disassociate' => 'Dissocier',
    'view_entity' => 'Voir l\'entité',

    // Table headers
    'table' => [
        'entity' => 'Entité',
        'member_number' => 'Numéro de membre',
        'email' => 'E-mail',
        'type' => 'Type',
        'acceptance_date' => 'Date d\'acceptation',
        'status' => 'Statut',
    ],
];
