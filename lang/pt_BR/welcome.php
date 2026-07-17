<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$country = $brand['country'];

return [
    // Hero Section
    'federation_portal' => 'Portal Subaquático',
    'your_gateway' => $brand['name'],
    'hero_description' => $brand['description'],

    // Navigation
    'home' => 'Início',
    'search_certifications' => 'Validar Certificações',
    'community_map' => 'Mapa de Entidades',
    'events' => 'Eventos',
    'diving_professionals' => 'Profissionais de Mergulho',
    'diving' => 'Mergulho',
    'coach_registry' => 'Treinadores',
    'underwater_sports' => 'Desporto Subaquatico',
    'club_registry' => 'Clubes',
    'technical_official_registry' => 'Oficiais Técnicos',
    'recreational_scientific_diving' => 'Mergulho Recreativo e Cientifico',
    'diving_service_providers' => 'Prestadores de Serviços de Mergulho',
    'sign_in' => 'Entrar',
    'login' => 'Iniciar Sessão',

    // Feature Cards
    'search_certifications_title' => 'Validar Certificações',
    'search_certifications_desc' => "Verifique instantaneamente certificacoes reconhecidas pela {$brand['short_name']}.",
    'search' => 'Pesquisar',

    'community_directory' => 'Diretório Nacional',
    'community_directory_desc' => "Encontre clubes, escolas e centros de mergulho certificados em {$country}.",
    'explore_map' => 'Explorar mapa',

    // Registration Section
    'become_member' => 'Tornar-se Membro',
    'choose_account_type' => 'Selecione o tipo de conta adequado ao seu perfil',

    // Individual Account
    'individual_account' => 'Criar Conta de Individual',
    'individual_subtitle' => 'Para atletas, treinadores, oficiais técnicos, mergulhadores e profissionais de mergulho',
    'athletes_coaches' => 'Atletas, Treinadores e Oficiais Técnicos',
    'divers_instructors' => 'Mergulhadores Recreativos, Técnicos e Científicos',
    'scientific_divers' => 'Profissionais de Mergulho',
    'register_as_individual' => 'Registar como Individual',
    'join_community' => 'Junte-se à comunidade subaquática nacional',

    // Organisation Account
    'organisation_account' => 'Criar Conta de Entidade Coletiva',
    'organisation_subtitle' => 'Para Clubes e Prestadores de Serviços de Mergulho',
    'diving_schools' => 'Prestadores de Serviços de Mergulho',
    'diving_clubs' => 'Clubes Desportivos',
    'scientific_facilities' => 'Centros de Mergulho Científico',
    'register_as_organisation' => 'Registar como Organização',
    'get_recognised' => "Obtenha reconhecimento oficial da {$brand['short_name']}",
    'already_have_account' => 'Já tem conta?',

    // Login Modal
    'email' => 'Email',
    'password' => 'Senha',
    'remember_me' => 'Lembrar-me',
    'forgot_password' => 'Esqueceu a senha?',
    'log_in' => 'Entrar',
    'need_account' => 'Precisa de uma conta?',
    'register_here' => 'Registe-se aqui',

    // Footer Info
    'about_federation' => $brand['about'],
    'member_of' => "Membro de {$internationalBrand['name']}",
    'about_federation_title' => "Sobre a {$brand['short_name']}",
    'quick_links' => 'Links Úteis',
    'official_website' => 'Site Oficial',
    'contact' => 'Contatos',
    'address' => $brand['address'],
    'phone' => $brand['phone'],
    'mobile' => $brand['mobile'],
    'email_address' => $brand['email'],
    'all_rights' => 'Todos os direitos reservados.',
];
