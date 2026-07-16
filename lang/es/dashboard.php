<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'federation_dashboard' => 'Panel de la federación',
    'admin_dashboard' => 'Panel de administración',
    'federation' => 'Federación',
    'entity_billing_title' => 'Total por entidad',
    'entity_name' => 'Entidad',
    'district' => 'Distrito',
    'total_billed' => 'Total (:currency)',
    'no_billing_data' => 'No hay datos de facturación disponibles.',
    'entity_billing_explanation' => 'Total calculado a partir de todos los documentos pagados del año actual, incluyendo: planes de afiliación, planes de seguro, inscripciones a eventos, certificaciones, licencias y pedidos manuales.',
    'entity_billing_affiliation_desc' => 'Total de los documentos de planes de afiliación pagados para particulares y entidades solicitados por cada entidad en el año actual.',
    'monthly_affiliation_revenue' => 'Ingresos mensuales por afiliación',
    'annual_entity_affiliation_revenue' => 'Ingresos anuales por planes de afiliación de entidades',
    'annual_individual_affiliation_revenue' => 'Ingresos anuales por planes de afiliación individuales',
    'revenue_eur' => 'Ingresos (:currency)',
    'entities_by_district' => 'Entidades por distrito',
    'entity_count' => 'Número de entidades',
    'pending_entity_approvals' => 'Aprobaciones de entidades pendientes',
    'pending_individual_approvals' => 'Aprobaciones de particulares pendientes',
    'view_all' => 'Ver todo',
    'no_pending' => 'No hay aprobaciones pendientes',
    'requested_at' => 'Solicitado',

    // Individual Members Distribution Table
    'members_distribution_title' => 'Distribución de miembros individuales por distrito',
    'members_distribution_desc' => "Total de miembros individuales de {$primaryShortName} y afiliados activos en la asociación territorial.",
    'age_group' => 'Grupo de edad / Género',
    'total' => 'Total',
    'registered' => 'Reg.',
    'affiliated' => 'Afil.',
    'female_up_to_12' => 'Mujeres hasta 12 años',
    'male_up_to_12' => 'Hombres hasta 12 años',
    'female_13_to_17' => 'Mujeres de 13 a 17 años',
    'male_13_to_17' => 'Hombres de 13 a 17 años',
    'female_18_to_45' => 'Mujeres de 18 a 45 años',
    'male_18_to_45' => 'Hombres de 18 a 45 años',
    'female_46_plus' => 'Mujeres de 46+ años',
    'male_46_plus' => 'Hombres de 46+ años',
    'no_members_data' => 'No hay datos de miembros disponibles.',
    'members_registered_help' => 'Miembros registrados en la plataforma',
    'members_affiliated_help' => 'Miembros con afiliación activa',

    // Welcome Banner
    'welcome_back' => 'Bienvenido de nuevo',
    'federation_overview' => 'Resumen de la actividad de tu federación',
    'member_number' => 'N.º de miembro',
    'id_number' => 'N.º de identificación',

    // Stats Widget
    'individual_members' => 'Miembros individuales',
    'total_in_federation' => 'Total en la federación',
    'active_affiliations' => 'Afiliaciones activas',
    'collective_entities' => 'Entidades colectivas',

    // Recent Activity
    'recent_actions' => 'Acciones recientes',

    // Licenses by Sport Chart
    'licenses_by_sport_heading' => ':role por deporte (:year)',
    'active_licenses' => 'Licencias activas',
    'count' => 'Cantidad',
    'role_athlete' => 'Atletas',
    'role_coach' => 'Entrenadores',
    'role_technical_official' => 'Oficiales técnicos',

    // Territorial Affiliations Table
    'territorial_affiliation_income' => 'Ingresos por afiliación por asociación territorial (:year)',
    'territorial_association' => 'Asociación territorial',
    'no_data' => 'No hay datos para mostrar',
    'grand_total' => 'Total general',

    // Entities by District Chart
    'entity_count_label' => 'Número de entidades',

    // Individuals by District Chart
    'individuals_by_district' => 'Miembros individuales por distrito',
    'total_active_individual_members' => 'Número total de miembros individuales activos',
    'individual_count_label' => 'Número de miembros',

    // Chart Labels (Total vs Active)
    'total_registered' => 'Total',
    'active_members' => 'Activos',
    'active_entities' => 'Activas',
    'total_active_entities' => 'Número total de entidades activas',

    // Chart Descriptions
    'entity_affiliation_revenue_desc' => 'Ingresos mensuales por planes de afiliación de entidades',
    'individual_affiliation_revenue_desc' => 'Ingresos mensuales por planes de afiliación individuales',
    'entity_license_revenue_desc' => 'Ingresos mensuales por compras de licencias de entidades',
    'individual_license_revenue_desc' => 'Ingresos mensuales por compras de licencias individuales',
    'individual_sport_licenses_desc' => 'Licencias deportivas activas por rol (atletas, entrenadores, oficiales)',
    'entity_sport_licenses_desc' => 'Licencias deportivas activas en poder de los clubes',

    // Stats Overview
    'total_description' => 'Total',

    // Dashboard Sections
    'statistics_section' => 'Estadísticas',
    'ranking' => 'Clasificación',
    'logo' => 'Logotipo',

    // License Revenue Charts
    'annual_entity_license_revenue' => 'Ingresos anuales por licencias de entidades',
    'annual_individual_license_revenue' => 'Ingresos anuales por licencias individuales',

    // Sport License Charts
    'individual_sport_licenses_by_role' => 'Licencias deportivas individuales',
    'entity_sport_licenses' => 'Licencias de clubes deportivos',
    'license_count' => 'Número de licencias',

    // Info Messages
    'license_revenue_organization_only' => 'Los resultados mostrados corresponden únicamente a las licencias de tu organización.',

    // Monthly Payments Table
    'monthly_payments_title' => 'Pagos mensuales',
    'monthly_payments_desc' => 'Importes totales de los documentos pagados por categoría y mes',
    'year' => 'Año',
    'category' => 'Categoría',
    'entity_affiliations' => 'Afiliaciones de entidades',
    'individual_affiliations' => 'Afiliaciones individuales',
    'entity_licenses' => 'Licencias de entidades',
    'individual_licenses' => 'Licencias individuales',
    'event_registrations' => 'Inscripciones a eventos',
    'certifications' => 'Certificaciones',
    'entity_insurances' => 'Seguros de entidades',
    'individual_insurances' => 'Seguros individuales',
    'others' => 'Otros',
    'month_jan' => 'Ene',
    'month_feb' => 'Feb',
    'month_mar' => 'Mar',
    'month_apr' => 'Abr',
    'month_may' => 'May',
    'month_jun' => 'Jun',
    'month_jul' => 'Jul',
    'month_aug' => 'Ago',
    'month_sep' => 'Sep',
    'month_oct' => 'Oct',
    'month_nov' => 'Nov',
    'month_dec' => 'Dic',
];
