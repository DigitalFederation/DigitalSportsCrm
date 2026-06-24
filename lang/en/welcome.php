<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$country = $brand['country'];

return [
    // Hero Section
    'federation_portal' => 'Underwater Portal',
    'your_gateway' => $brand['name'],
    'hero_description' => $brand['description'],

    // Navigation
    'home' => 'Home',
    'search_certifications' => 'Validate Certifications',
    'community_map' => 'Entity Map',
    'events' => 'Events',
    'diving_professionals' => 'Diving Professionals',
    'diving' => 'Diving',
    'coach_registry' => 'Coaches',
    'underwater_sports' => 'Underwater Sports',
    'club_registry' => 'Clubs',
    'technical_official_registry' => 'Technical Officials',
    'recreational_scientific_diving' => 'Recreational and Scientific Diving',
    'diving_service_providers' => 'Diving Service Providers',
    'sign_in' => 'Sign In',
    'login' => 'Login',

    // Feature Cards
    'search_certifications_title' => 'Validate Certifications',
    'search_certifications_desc' => "Instantly verify certifications recognized by {$brand['short_name']}.",
    'search' => 'Search',

    'community_directory' => 'National Directory',
    'community_directory_desc' => "Find certified clubs, schools and diving centers in {$country}.",
    'explore_map' => 'Explore map',

    // Registration Section
    'become_member' => 'Become a Member',
    'choose_account_type' => 'Select the account type that fits your profile',

    // Individual Account
    'individual_account' => 'Create Individual Account',
    'individual_subtitle' => 'For athletes, coaches, technical officials, divers and diving professionals',
    'athletes_coaches' => 'Athletes, Coaches and Technical Officials',
    'divers_instructors' => 'Recreational, Technical and Scientific Divers',
    'scientific_divers' => 'Diving Professionals',
    'register_as_individual' => 'Register as Individual',
    'join_community' => 'Join the national underwater community',

    // Organisation Account
    'organisation_account' => 'Create Entity Account',
    'organisation_subtitle' => 'For Clubs and Diving Service Providers',
    'diving_schools' => 'Diving Service Providers',
    'diving_clubs' => 'Sports Clubs',
    'scientific_facilities' => 'Scientific Diving Centers',
    'register_as_organisation' => 'Register as Organisation',
    'get_recognised' => "Get official recognition from {$brand['short_name']}",
    'already_have_account' => 'Already have an account?',

    // Login Modal
    'email' => 'Email',
    'password' => 'Password',
    'remember_me' => 'Remember me',
    'forgot_password' => 'Forgot your password?',
    'log_in' => 'Log in',
    'need_account' => 'Need an account?',
    'register_here' => 'Register here',

    // Footer Info
    'about_federation' => $brand['about'],
    'member_of' => "Member of {$internationalBrand['name']}",
    'about_federation_title' => "About {$brand['short_name']}",
    'quick_links' => 'Useful Links',
    'official_website' => 'Official Website',
    'contact' => 'Contact',
    'address' => $brand['address'],
    'phone' => $brand['phone'],
    'mobile' => $brand['mobile'],
    'email_address' => $brand['email'],
    'all_rights' => 'All rights reserved.',
];
