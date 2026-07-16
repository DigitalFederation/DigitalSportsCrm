<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'federation_dashboard' => 'Tableau de bord de la fédération',
    'admin_dashboard' => 'Tableau de bord administrateur',
    'federation' => 'Fédération',
    'entity_billing_title' => 'Total par entité',
    'entity_name' => 'Entité',
    'district' => 'District',
    'total_billed' => 'Total (:currency)',
    'no_billing_data' => 'Aucune donnée de facturation disponible.',
    'entity_billing_explanation' => 'Total calculé à partir de tous les documents payés de l\'année en cours, incluant : les plans d\'affiliation, les plans d\'assurance, les inscriptions aux événements, les certifications, les licences et les commandes manuelles.',
    'entity_billing_affiliation_desc' => 'Total des documents de plans d\'affiliation payés pour les particuliers et les entités demandés par chaque entité au cours de l\'année en cours.',
    'monthly_affiliation_revenue' => 'Revenus mensuels d\'affiliation',
    'annual_entity_affiliation_revenue' => 'Revenus annuels des plans d\'affiliation d\'entités',
    'annual_individual_affiliation_revenue' => 'Revenus annuels des plans d\'affiliation de particuliers',
    'revenue_eur' => 'Revenus (:currency)',
    'entities_by_district' => 'Entités par district',
    'entity_count' => 'Nombre d\'entités',
    'pending_entity_approvals' => 'Approbations d\'entités en attente',
    'pending_individual_approvals' => 'Approbations de particuliers en attente',
    'view_all' => 'Voir tout',
    'no_pending' => 'Aucune approbation en attente',
    'requested_at' => 'Demandé',

    // Individual Members Distribution Table
    'members_distribution_title' => 'Répartition des membres particuliers par district',
    'members_distribution_desc' => "Total des membres particuliers {$primaryShortName} et des affiliés actifs dans l\'association territoriale.",
    'age_group' => 'Tranche d\'âge / Sexe',
    'total' => 'Total',
    'registered' => 'Inscr.',
    'affiliated' => 'Affil.',
    'female_up_to_12' => 'Femmes jusqu\'à 12 ans',
    'male_up_to_12' => 'Hommes jusqu\'à 12 ans',
    'female_13_to_17' => 'Femmes de 13 à 17 ans',
    'male_13_to_17' => 'Hommes de 13 à 17 ans',
    'female_18_to_45' => 'Femmes de 18 à 45 ans',
    'male_18_to_45' => 'Hommes de 18 à 45 ans',
    'female_46_plus' => 'Femmes de 46 ans et plus',
    'male_46_plus' => 'Hommes de 46 ans et plus',
    'no_members_data' => 'Aucune donnée de membre disponible.',
    'members_registered_help' => 'Membres inscrits sur la plateforme',
    'members_affiliated_help' => 'Membres avec une affiliation active',

    // Welcome Banner
    'welcome_back' => 'Bon retour',
    'federation_overview' => 'Aperçu de l\'activité de votre fédération',
    'member_number' => 'N° de membre',
    'id_number' => 'N° d\'identification',

    // Stats Widget
    'individual_members' => 'Membres particuliers',
    'total_in_federation' => 'Total dans la fédération',
    'active_affiliations' => 'Affiliations actives',
    'collective_entities' => 'Entités collectives',

    // Recent Activity
    'recent_actions' => 'Actions récentes',

    // Licenses by Sport Chart
    'licenses_by_sport_heading' => ':role par sport (:year)',
    'active_licenses' => 'Licences actives',
    'count' => 'Nombre',
    'role_athlete' => 'Athlètes',
    'role_coach' => 'Entraîneurs',
    'role_technical_official' => 'Officiels techniques',

    // Territorial Affiliations Table
    'territorial_affiliation_income' => 'Revenus d\'affiliation par association territoriale (:year)',
    'territorial_association' => 'Association territoriale',
    'no_data' => 'Aucune donnée à afficher',
    'grand_total' => 'Total général',

    // Entities by District Chart
    'entity_count_label' => 'Nombre d\'entités',

    // Individuals by District Chart
    'individuals_by_district' => 'Membres particuliers par district',
    'total_active_individual_members' => 'Nombre total de membres particuliers actifs',
    'individual_count_label' => 'Nombre de membres',

    // Chart Labels (Total vs Active)
    'total_registered' => 'Total',
    'active_members' => 'Actifs',
    'active_entities' => 'Actives',
    'total_active_entities' => 'Nombre total d\'entités actives',

    // Chart Descriptions
    'entity_affiliation_revenue_desc' => 'Revenus mensuels des plans d\'affiliation d\'entités',
    'individual_affiliation_revenue_desc' => 'Revenus mensuels des plans d\'affiliation de particuliers',
    'entity_license_revenue_desc' => 'Revenus mensuels des achats de licences d\'entités',
    'individual_license_revenue_desc' => 'Revenus mensuels des achats de licences de particuliers',
    'individual_sport_licenses_desc' => 'Licences sportives actives par rôle (athlètes, entraîneurs, officiels)',
    'entity_sport_licenses_desc' => 'Licences sportives actives détenues par les clubs',

    // Stats Overview
    'total_description' => 'Total',

    // Dashboard Sections
    'statistics_section' => 'Statistiques',
    'ranking' => 'Classement',
    'logo' => 'Logo',

    // License Revenue Charts
    'annual_entity_license_revenue' => 'Revenus annuels des licences d\'entités',
    'annual_individual_license_revenue' => 'Revenus annuels des licences de particuliers',

    // Sport License Charts
    'individual_sport_licenses_by_role' => 'Licences sportives de particuliers',
    'entity_sport_licenses' => 'Licences de clubs sportifs',
    'license_count' => 'Nombre de licences',

    // Info Messages
    'license_revenue_organization_only' => 'Les résultats affichés concernent uniquement les licences de votre organisation.',

    // Monthly Payments Table
    'monthly_payments_title' => 'Paiements mensuels',
    'monthly_payments_desc' => 'Montants totaux des documents payés par catégorie et par mois',
    'year' => 'Année',
    'category' => 'Catégorie',
    'entity_affiliations' => 'Affiliations d\'entités',
    'individual_affiliations' => 'Affiliations de particuliers',
    'entity_licenses' => 'Licences d\'entités',
    'individual_licenses' => 'Licences de particuliers',
    'event_registrations' => 'Inscriptions aux événements',
    'certifications' => 'Certifications',
    'entity_insurances' => 'Assurances d\'entités',
    'individual_insurances' => 'Assurances de particuliers',
    'others' => 'Autres',
    'month_jan' => 'Janv.',
    'month_feb' => 'Févr.',
    'month_mar' => 'Mars',
    'month_apr' => 'Avr.',
    'month_may' => 'Mai',
    'month_jun' => 'Juin',
    'month_jul' => 'Juil.',
    'month_aug' => 'Août',
    'month_sep' => 'Sept.',
    'month_oct' => 'Oct.',
    'month_nov' => 'Nov.',
    'month_dec' => 'Déc.',
];
