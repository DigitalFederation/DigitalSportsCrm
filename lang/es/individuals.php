<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    'title' => 'Miembros individuales',
    'name' => 'Nombre',
    'surname' => 'Apellido',
    'given_name' => 'Nombre de pila',
    'family_name' => 'Apellido',
    'nationality' => 'Nacionalidad',
    'member_number' => 'Número de socio',
    'id_number' => 'Número de identificación',
    'affiliation_status' => 'Estado de la afiliación',
    'federation_portal' => $portalName,
    'cmas_portal' => "Portal de {$internationalShortName}",
    'active' => 'Activo',
    'inactive' => 'Inactivo',
    'yes' => 'Sí',
    'no' => 'No',
    'gender' => 'Sexo',
    'male' => 'Hombre',
    'female' => 'Mujer',
    'other' => 'Otro',
    'federation_portal_access' => "Acceso a {$portalName}",
    'has_federation_portal_account' => "Tiene cuenta en {$portalName}",
    'federation_portal_description' => "Marca esta casilla si la persona tiene una cuenta en {$portalName}",
    'cmas_portal_access' => "Acceso al portal de {$internationalShortName}",
    'has_cmas_portal_account' => "Tiene cuenta en el portal de {$internationalShortName}",
    'cmas_portal_description' => "Marca esta casilla si la persona tiene una cuenta en el portal de {$internationalShortName}",
    'national_fed_nr' => 'N.º de fed. nacional',
    'birthdate' => 'Fecha de nacimiento',
    'instructors_leaders' => 'Instructores y guías',
    'coachs' => 'Entrenadores',
    'referees_judges' => 'Árbitros/Jueces',
    'create_individual' => 'Crear persona',
    'individuals_to_approve' => 'Personas por aprobar',
    'latest_entries' => 'Últimas entradas',

    // Federation membership
    'federation_and_organizations' => 'Federaciones y organizaciones',
    'federation_id' => 'ID',
    'federation_name' => 'Nombre',
    'membership_status' => 'Estado de la membresía',
    'confirm_disassociate_federation' => '¿Estás seguro de que deseas desasociarte de esta federación?',
    'cannot_disassociate_main_federation' => 'No puedes desasociarte de la federación principal.',
    'member_number_already_taken' => 'Este número de socio ya está asignado a otra persona.',
    'federation_edit_only' => '*Solo la Federación Nacional puede editar esta información',
];
