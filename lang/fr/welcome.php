<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$country = $brand['country'];

return [
    // Hero Section
    'federation_portal' => 'Portail subaquatique',
    'your_gateway' => $brand['name'],
    'hero_description' => $brand['description'],

    // Navigation
    'home' => 'Accueil',
    'search_certifications' => 'Valider les certifications',
    'community_map' => 'Carte des entités',
    'events' => 'Événements',
    'diving_professionals' => 'Professionnels de la plongée',
    'diving' => 'Plongée',
    'coach_registry' => 'Entraîneurs',
    'underwater_sports' => 'Sports subaquatiques',
    'club_registry' => 'Clubs',
    'technical_official_registry' => 'Officiels techniques',
    'recreational_scientific_diving' => 'Plongée de loisir et scientifique',
    'diving_service_providers' => 'Prestataires de services de plongée',
    'sign_in' => 'Se connecter',
    'login' => 'Connexion',

    // Feature Cards
    'search_certifications_title' => 'Valider les certifications',
    'search_certifications_desc' => "Vérifiez instantanément les certifications reconnues par {$brand['short_name']}.",
    'search' => 'Rechercher',

    'community_directory' => 'Annuaire national',
    'community_directory_desc' => "Trouvez des clubs, écoles et centres de plongée certifiés en {$country}.",
    'explore_map' => 'Explorer la carte',

    // Registration Section
    'become_member' => 'Devenir membre',
    'choose_account_type' => 'Sélectionnez le type de compte qui correspond à votre profil',

    // Individual Account
    'individual_account' => 'Créer un compte individuel',
    'individual_subtitle' => 'Pour les athlètes, entraîneurs, officiels techniques, plongeurs et professionnels de la plongée',
    'athletes_coaches' => 'Athlètes, entraîneurs et officiels techniques',
    'divers_instructors' => 'Plongeurs de loisir, techniques et scientifiques',
    'scientific_divers' => 'Professionnels de la plongée',
    'register_as_individual' => 'S\'inscrire en tant que particulier',
    'join_community' => 'Rejoignez la communauté subaquatique nationale',

    // Organisation Account
    'organisation_account' => 'Créer un compte entité',
    'organisation_subtitle' => 'Pour les clubs et les prestataires de services de plongée',
    'diving_schools' => 'Prestataires de services de plongée',
    'diving_clubs' => 'Clubs sportifs',
    'scientific_facilities' => 'Centres de plongée scientifique',
    'register_as_organisation' => 'S\'inscrire en tant qu\'organisation',
    'get_recognised' => "Obtenez la reconnaissance officielle de {$brand['short_name']}",
    'already_have_account' => 'Vous avez déjà un compte ?',

    // Login Modal
    'email' => 'E-mail',
    'password' => 'Mot de passe',
    'remember_me' => 'Se souvenir de moi',
    'forgot_password' => 'Mot de passe oublié ?',
    'log_in' => 'Se connecter',
    'need_account' => 'Besoin d\'un compte ?',
    'register_here' => 'Inscrivez-vous ici',

    // Footer Info
    'about_federation' => $brand['about'],
    'member_of' => "Membre de {$internationalBrand['name']}",
    'about_federation_title' => "À propos de {$brand['short_name']}",
    'quick_links' => 'Liens utiles',
    'official_website' => 'Site web officiel',
    'contact' => 'Contact',
    'address' => $brand['address'],
    'phone' => $brand['phone'],
    'mobile' => $brand['mobile'],
    'email_address' => $brand['email'],
    'all_rights' => 'Tous droits réservés.',
];
