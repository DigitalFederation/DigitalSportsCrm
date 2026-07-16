<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$country = $brand['country'];

return [
    // Hero Section
    'federation_portal' => 'Portal Subacuático',
    'your_gateway' => $brand['name'],
    'hero_description' => $brand['description'],

    // Navigation
    'home' => 'Inicio',
    'search_certifications' => 'Validar certificaciones',
    'community_map' => 'Mapa de entidades',
    'events' => 'Eventos',
    'diving_professionals' => 'Profesionales del buceo',
    'diving' => 'Buceo',
    'coach_registry' => 'Entrenadores',
    'underwater_sports' => 'Deportes subacuáticos',
    'club_registry' => 'Clubes',
    'technical_official_registry' => 'Oficiales técnicos',
    'recreational_scientific_diving' => 'Buceo recreativo y científico',
    'diving_service_providers' => 'Proveedores de servicios de buceo',
    'sign_in' => 'Iniciar sesión',
    'login' => 'Acceder',

    // Feature Cards
    'search_certifications_title' => 'Validar certificaciones',
    'search_certifications_desc' => "Verifica al instante las certificaciones reconocidas por {$brand['short_name']}.",
    'search' => 'Buscar',

    'community_directory' => 'Directorio nacional',
    'community_directory_desc' => "Encuentra clubes, escuelas y centros de buceo certificados en {$country}.",
    'explore_map' => 'Explorar mapa',

    // Registration Section
    'become_member' => 'Hazte miembro',
    'choose_account_type' => 'Selecciona el tipo de cuenta que se ajuste a tu perfil',

    // Individual Account
    'individual_account' => 'Crear cuenta individual',
    'individual_subtitle' => 'Para deportistas, entrenadores, oficiales técnicos, buceadores y profesionales del buceo',
    'athletes_coaches' => 'Deportistas, entrenadores y oficiales técnicos',
    'divers_instructors' => 'Buceadores recreativos, técnicos y científicos',
    'scientific_divers' => 'Profesionales del buceo',
    'register_as_individual' => 'Registrarse como persona',
    'join_community' => 'Únete a la comunidad subacuática nacional',

    // Organisation Account
    'organisation_account' => 'Crear cuenta de entidad',
    'organisation_subtitle' => 'Para clubes y proveedores de servicios de buceo',
    'diving_schools' => 'Proveedores de servicios de buceo',
    'diving_clubs' => 'Clubes deportivos',
    'scientific_facilities' => 'Centros de buceo científico',
    'register_as_organisation' => 'Registrarse como organización',
    'get_recognised' => "Obtén el reconocimiento oficial de {$brand['short_name']}",
    'already_have_account' => '¿Ya tienes una cuenta?',

    // Login Modal
    'email' => 'Correo electrónico',
    'password' => 'Contraseña',
    'remember_me' => 'Recordarme',
    'forgot_password' => '¿Olvidaste tu contraseña?',
    'log_in' => 'Acceder',
    'need_account' => '¿Necesitas una cuenta?',
    'register_here' => 'Regístrate aquí',

    // Footer Info
    'about_federation' => $brand['about'],
    'member_of' => "Miembro de {$internationalBrand['name']}",
    'about_federation_title' => "Acerca de {$brand['short_name']}",
    'quick_links' => 'Enlaces útiles',
    'official_website' => 'Sitio web oficial',
    'contact' => 'Contacto',
    'address' => $brand['address'],
    'phone' => $brand['phone'],
    'mobile' => $brand['mobile'],
    'email_address' => $brand['email'],
    'all_rights' => 'Todos los derechos reservados.',
];
