<?php

$internationalName = config('branding.international.name', 'International Federation');

return [
    'instructors_and_leaders_entities' => "Entidades de Mergulho {$internationalName}",
    'information' => 'Informação',
    'information_body' => 'Nesta área pode gerir as entidades de mergulho às quais está associado como instrutor ou líder de mergulho.',

    // Pending invitations
    'pending_invitations' => 'Convites Pendentes',
    'pending_invitations_body' => 'Tem os seguintes convites pendentes para associação com uma entidade. Aceitar irá ligar a sua conta com base nas suas licenças ativas para o comité relevante.',

    // Associations
    'your_associations' => 'As Suas Associações',
    'confirm_action' => 'Tem certeza de que deseja confirmar?',

    // Actions
    'disassociate' => 'Desassociar',
    'view_entity' => 'Ver Entidade',

    // Table headers
    'table' => [
        'entity' => 'Entidade',
        'member_number' => 'N. Filiado',
        'email' => 'Email',
        'type' => 'Tipo',
        'acceptance_date' => 'Data Aceitação',
        'status' => 'Estado',
    ],
];
