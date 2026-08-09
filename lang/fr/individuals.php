<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    'title' => 'Membres individuels',
    'name' => 'Nom',
    'surname' => 'Nom de famille',
    'given_name' => 'Prénom',
    'family_name' => 'Nom de famille',
    'nationality' => 'Nationalité',
    'member_number' => 'Numéro de membre',
    'id_number' => 'Numéro d\'identification',
    'affiliation_status' => 'Statut d\'affiliation',
    'federation_portal' => $portalName,
    'cmas_portal' => "Portail {$internationalShortName}",
    'active' => 'Actif',
    'inactive' => 'Inactif',
    'yes' => 'Oui',
    'no' => 'Non',
    'gender' => 'Sexe',
    'male' => 'Homme',
    'female' => 'Femme',
    'other' => 'Autre',
    'federation_portal_access' => "Accès à {$portalName}",
    'has_federation_portal_account' => "Possède un compte {$portalName}",
    'federation_portal_description' => "Cochez cette case si la personne possède un compte sur {$portalName}",
    'cmas_portal_access' => "Accès au portail {$internationalShortName}",
    'has_cmas_portal_account' => "Possède un compte sur le portail {$internationalShortName}",
    'cmas_portal_description' => "Cochez cette case si la personne possède un compte sur le portail {$internationalShortName}",
    'national_fed_nr' => 'N° féd. nationale',
    'birthdate' => 'Date de naissance',
    'instructors_leaders' => 'Moniteurs et guides',
    'coachs' => 'Entraîneurs',
    'referees_judges' => 'Arbitres/Juges',
    'create_individual' => 'Créer une personne',
    'individuals_to_approve' => 'Personnes à approuver',
    'latest_entries' => 'Dernières entrées',

    // Federation membership
    'federation_and_organizations' => 'Fédérations et organisations',
    'federation_id' => 'ID',
    'federation_name' => 'Nom',
    'membership_status' => 'Statut d\'adhésion',
    'confirm_disassociate_federation' => 'Êtes-vous sûr de vouloir vous dissocier de cette fédération ?',
    'cannot_disassociate_main_federation' => 'Vous ne pouvez pas vous dissocier de la fédération principale.',
    'member_number_already_taken' => 'Ce numéro de membre est déjà attribué à une autre personne.',
    'federation_edit_only' => '*Seule la fédération nationale peut modifier ces informations',
];
