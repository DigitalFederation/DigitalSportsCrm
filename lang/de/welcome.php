<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$country = $brand['country'];

return [
    // Hero Section
    'federation_portal' => 'Unterwasser-Portal',
    'your_gateway' => $brand['name'],
    'hero_description' => $brand['description'],

    // Navigation
    'home' => 'Startseite',
    'search_certifications' => 'Zertifizierungen validieren',
    'community_map' => 'Organisationskarte',
    'events' => 'Veranstaltungen',
    'diving_professionals' => 'Tauchprofis',
    'diving' => 'Tauchen',
    'coach_registry' => 'Trainer',
    'underwater_sports' => 'Unterwassersport',
    'club_registry' => 'Vereine',
    'technical_official_registry' => 'Technische Offizielle',
    'recreational_scientific_diving' => 'Freizeit- und wissenschaftliches Tauchen',
    'diving_service_providers' => 'Tauchdienstleister',
    'sign_in' => 'Anmelden',
    'login' => 'Login',

    // Feature Cards
    'search_certifications_title' => 'Zertifizierungen validieren',
    'search_certifications_desc' => "Überprüfen Sie sofort Zertifizierungen, die von {$brand['short_name']} anerkannt sind.",
    'search' => 'Suchen',

    'community_directory' => 'Nationales Verzeichnis',
    'community_directory_desc' => "Finden Sie zertifizierte Vereine, Schulen und Tauchzentren in {$country}.",
    'explore_map' => 'Karte erkunden',

    // Registration Section
    'become_member' => 'Mitglied werden',
    'choose_account_type' => 'Wählen Sie den Kontotyp, der zu Ihrem Profil passt',

    // Individual Account
    'individual_account' => 'Einzelkonto erstellen',
    'individual_subtitle' => 'Für Athleten, Trainer, technische Offizielle, Taucher und Tauchprofis',
    'athletes_coaches' => 'Athleten, Trainer und technische Offizielle',
    'divers_instructors' => 'Freizeit-, technische und wissenschaftliche Taucher',
    'scientific_divers' => 'Tauchprofis',
    'register_as_individual' => 'Als Einzelperson registrieren',
    'join_community' => 'Treten Sie der nationalen Unterwasser-Community bei',

    // Organisation Account
    'organisation_account' => 'Organisationskonto erstellen',
    'organisation_subtitle' => 'Für Vereine und Tauchdienstleister',
    'diving_schools' => 'Tauchdienstleister',
    'diving_clubs' => 'Sportvereine',
    'scientific_facilities' => 'Wissenschaftliche Tauchzentren',
    'register_as_organisation' => 'Als Organisation registrieren',
    'get_recognised' => "Erhalten Sie die offizielle Anerkennung von {$brand['short_name']}",
    'already_have_account' => 'Haben Sie bereits ein Konto?',

    // Login Modal
    'email' => 'E-Mail',
    'password' => 'Passwort',
    'remember_me' => 'Angemeldet bleiben',
    'forgot_password' => 'Passwort vergessen?',
    'log_in' => 'Anmelden',
    'need_account' => 'Benötigen Sie ein Konto?',
    'register_here' => 'Hier registrieren',

    // Footer Info
    'about_federation' => $brand['about'],
    'member_of' => "Mitglied von {$internationalBrand['name']}",
    'about_federation_title' => "Über {$brand['short_name']}",
    'quick_links' => 'Nützliche Links',
    'official_website' => 'Offizielle Website',
    'contact' => 'Kontakt',
    'address' => $brand['address'],
    'phone' => $brand['phone'],
    'mobile' => $brand['mobile'],
    'email_address' => $brand['email'],
    'all_rights' => 'Alle Rechte vorbehalten.',
];
