<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'federation_dashboard' => 'Federation Dashboard',
    'admin_dashboard' => 'Admin Dashboard',
    'federation' => 'Federation',
    'entity_billing_title' => 'Total per Entity',
    'entity_name' => 'Entity',
    'district' => 'District',
    'total_billed' => 'Total (:currency)',
    'no_billing_data' => 'No billing data available.',
    'entity_billing_explanation' => 'Total calculated from all paid documents for the current year, including: affiliation plans, insurance plans, event registrations, certifications, licenses, and manual orders.',
    'entity_billing_affiliation_desc' => 'Total from paid affiliation plan documents for individuals and entities requested by each entity in the current year.',
    'monthly_affiliation_revenue' => 'Monthly Affiliation Revenue',
    'annual_entity_affiliation_revenue' => 'Annual Entity Affiliation Plan Revenue',
    'annual_individual_affiliation_revenue' => 'Annual Individual Affiliation Plan Revenue',
    'revenue_eur' => 'Revenue (:currency)',
    'entities_by_district' => 'Entities by District',
    'entity_count' => 'Number of Entities',
    'pending_entity_approvals' => 'Pending Entity Approvals',
    'pending_individual_approvals' => 'Pending Individual Approvals',
    'view_all' => 'View All',
    'no_pending' => 'No pending approvals',
    'requested_at' => 'Requested',

    // Individual Members Distribution Table
    'members_distribution_title' => 'Individual Members Distribution by District',
    'members_distribution_desc' => "Total {$primaryShortName} individual members and active affiliates in the territorial association.",
    'age_group' => 'Age Group / Gender',
    'total' => 'Total',
    'registered' => 'Reg.',
    'affiliated' => 'Aff.',
    'female_up_to_12' => 'Female up to 12 years',
    'male_up_to_12' => 'Male up to 12 years',
    'female_13_to_17' => 'Female 13 to 17 years',
    'male_13_to_17' => 'Male 13 to 17 years',
    'female_18_to_45' => 'Female 18 to 45 years',
    'male_18_to_45' => 'Male 18 to 45 years',
    'female_46_plus' => 'Female 46+ years',
    'male_46_plus' => 'Male 46+ years',
    'no_members_data' => 'No member data available.',
    'members_registered_help' => 'Members registered in the platform',
    'members_affiliated_help' => 'Members with active affiliation',

    // Welcome Banner
    'welcome_back' => 'Welcome back',
    'federation_overview' => 'Overview of your federation activity',
    'member_number' => 'Member No.',
    'id_number' => 'ID No.',

    // Stats Widget
    'individual_members' => 'Individual Members',
    'total_in_federation' => 'Total in federation',
    'active_affiliations' => 'Active affiliations',
    'collective_entities' => 'Collective Entities',

    // Recent Activity
    'recent_actions' => 'Recent Actions',

    // Licenses by Sport Chart
    'licenses_by_sport_heading' => ':role by Sport (:year)',
    'active_licenses' => 'Active Licenses',
    'count' => 'Count',
    'role_athlete' => 'Athletes',
    'role_coach' => 'Coaches',
    'role_technical_official' => 'Technical Officials',

    // Territorial Affiliations Table
    'territorial_affiliation_income' => 'Affiliation Income by Territorial Association (:year)',
    'territorial_association' => 'Territorial Association',
    'no_data' => 'No data to display',
    'grand_total' => 'Grand Total',

    // Entities by District Chart
    'entity_count_label' => 'Number of Entities',

    // Individuals by District Chart
    'individuals_by_district' => 'Individual Members by District',
    'total_active_individual_members' => 'Total number of active individual members',
    'individual_count_label' => 'Number of Members',

    // Chart Labels (Total vs Active)
    'total_registered' => 'Total',
    'active_members' => 'Active',
    'active_entities' => 'Active',
    'total_active_entities' => 'Total number of active entities',

    // Chart Descriptions
    'entity_affiliation_revenue_desc' => 'Monthly revenue from entity affiliation plans',
    'individual_affiliation_revenue_desc' => 'Monthly revenue from individual affiliation plans',
    'entity_license_revenue_desc' => 'Monthly revenue from entity license purchases',
    'individual_license_revenue_desc' => 'Monthly revenue from individual license purchases',
    'individual_sport_licenses_desc' => 'Active sport licenses by role (athletes, coaches, officials)',
    'entity_sport_licenses_desc' => 'Active sport licenses held by clubs',

    // Stats Overview
    'total_description' => 'Total',

    // Dashboard Sections
    'statistics_section' => 'Statistics',
    'ranking' => 'Ranking',
    'logo' => 'Logo',

    // License Revenue Charts
    'annual_entity_license_revenue' => 'Annual Entity License Revenue',
    'annual_individual_license_revenue' => 'Annual Individual License Revenue',

    // Sport License Charts
    'individual_sport_licenses_by_role' => 'Individual Sport Licenses',
    'entity_sport_licenses' => 'Sports Club Licenses',
    'license_count' => 'Number of Licenses',

    // Info Messages
    'license_revenue_organization_only' => 'The results shown are only for licenses from your organization.',

    // Monthly Payments Table
    'monthly_payments_title' => 'Monthly Payments',
    'monthly_payments_desc' => 'Total paid document amounts by category and month',
    'year' => 'Year',
    'category' => 'Category',
    'entity_affiliations' => 'Entity Affiliations',
    'individual_affiliations' => 'Individual Affiliations',
    'entity_licenses' => 'Entity Licenses',
    'individual_licenses' => 'Individual Licenses',
    'event_registrations' => 'Event Registrations',
    'certifications' => 'Certifications',
    'entity_insurances' => 'Entity Insurances',
    'individual_insurances' => 'Individual Insurances',
    'others' => 'Others',
    'month_jan' => 'Jan',
    'month_feb' => 'Feb',
    'month_mar' => 'Mar',
    'month_apr' => 'Apr',
    'month_may' => 'May',
    'month_jun' => 'Jun',
    'month_jul' => 'Jul',
    'month_aug' => 'Aug',
    'month_sep' => 'Sep',
    'month_oct' => 'Oct',
    'month_nov' => 'Nov',
    'month_dec' => 'Dec',
];
