<?php

$internationalName = config('branding.international.name', 'International Federation');

return [
    'instructors_and_leaders_entities' => "{$internationalName} Tauchorganisationen",
    'information' => 'Informationen',
    'information_body' => 'In diesem Bereich können Sie die Tauchorganisationen verwalten, mit denen Sie als Tauchlehrer oder Tauchgruppenleiter verbunden sind.',

    // Pending invitations
    'pending_invitations' => 'Ausstehende Einladungen',
    'pending_invitations_body' => 'Sie haben die folgenden ausstehenden Einladungen zur Verbindung mit einer Organisation. Bei Annahme wird Ihr Konto auf Grundlage Ihrer aktiven Lizenzen für das betreffende Gremium verknüpft.',

    // Associations
    'your_associations' => 'Ihre Verbindungen',
    'confirm_action' => 'Sind Sie sicher, dass Sie bestätigen möchten?',

    // Actions
    'disassociate' => 'Verbindung aufheben',
    'view_entity' => 'Organisation anzeigen',

    // Table headers
    'table' => [
        'entity' => 'Organisation',
        'member_number' => 'Mitgliedsnummer',
        'email' => 'E-Mail',
        'type' => 'Typ',
        'acceptance_date' => 'Annahmedatum',
        'status' => 'Status',
    ],
];
