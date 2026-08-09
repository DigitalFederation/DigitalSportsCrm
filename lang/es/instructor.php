<?php

$internationalName = config('branding.international.name', 'International Federation');

return [
    'instructors_and_leaders_entities' => "Entidades de buceo de {$internationalName}",
    'information' => 'Información',
    'information_body' => 'En esta área puedes gestionar las entidades de buceo a las que estás asociado como instructor o guía de buceo.',

    // Pending invitations
    'pending_invitations' => 'Invitaciones pendientes',
    'pending_invitations_body' => 'Tienes las siguientes invitaciones pendientes para asociarte con una entidad. Al aceptar, se vinculará tu cuenta según tus licencias activas para el comité correspondiente.',

    // Associations
    'your_associations' => 'Tus asociaciones',
    'confirm_action' => '¿Estás seguro de que deseas confirmar?',

    // Actions
    'disassociate' => 'Desasociar',
    'view_entity' => 'Ver entidad',

    // Table headers
    'table' => [
        'entity' => 'Entidad',
        'member_number' => 'Número de socio',
        'email' => 'Correo electrónico',
        'type' => 'Tipo',
        'acceptance_date' => 'Fecha de aceptación',
        'status' => 'Estado',
    ],
];
