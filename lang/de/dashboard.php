<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'federation_dashboard' => 'Verbands-Dashboard',
    'admin_dashboard' => 'Admin-Dashboard',
    'federation' => 'Verband',
    'entity_billing_title' => 'Gesamt pro Einheit',
    'entity_name' => 'Einheit',
    'district' => 'Bezirk',
    'total_billed' => 'Gesamt (EUR)',
    'no_billing_data' => 'Keine Abrechnungsdaten verfügbar.',
    'entity_billing_explanation' => 'Gesamtsumme berechnet aus allen bezahlten Dokumenten des laufenden Jahres, einschließlich: Mitgliedschaftspläne, Versicherungspläne, Veranstaltungsanmeldungen, Zertifizierungen, Lizenzen und manuelle Bestellungen.',
    'entity_billing_affiliation_desc' => 'Gesamtsumme aus bezahlten Mitgliedschaftsplan-Dokumenten für Einzelpersonen und Einheiten, die von jeder Einheit im laufenden Jahr angefordert wurden.',
    'monthly_affiliation_revenue' => 'Monatliche Mitgliedschaftseinnahmen',
    'annual_entity_affiliation_revenue' => 'Jährliche Einnahmen aus Einheiten-Mitgliedschaftsplänen',
    'annual_individual_affiliation_revenue' => 'Jährliche Einnahmen aus Einzel-Mitgliedschaftsplänen',
    'revenue_eur' => 'Einnahmen (EUR)',
    'entities_by_district' => 'Einheiten nach Bezirk',
    'entity_count' => 'Anzahl der Einheiten',
    'pending_entity_approvals' => 'Ausstehende Einheiten-Genehmigungen',
    'pending_individual_approvals' => 'Ausstehende Einzelpersonen-Genehmigungen',
    'view_all' => 'Alle anzeigen',
    'no_pending' => 'Keine ausstehenden Genehmigungen',
    'requested_at' => 'Angefordert',

    // Individual Members Distribution Table
    'members_distribution_title' => 'Verteilung der Einzelmitglieder nach Bezirk',
    'members_distribution_desc' => "Gesamtzahl der {$primaryShortName}-Einzelmitglieder und aktiven Angehörigen im Territorialverband.",
    'age_group' => 'Altersgruppe / Geschlecht',
    'total' => 'Gesamt',
    'registered' => 'Reg.',
    'affiliated' => 'Angeh.',
    'female_up_to_12' => 'Weiblich bis 12 Jahre',
    'male_up_to_12' => 'Männlich bis 12 Jahre',
    'female_13_to_17' => 'Weiblich 13 bis 17 Jahre',
    'male_13_to_17' => 'Männlich 13 bis 17 Jahre',
    'female_18_to_45' => 'Weiblich 18 bis 45 Jahre',
    'male_18_to_45' => 'Männlich 18 bis 45 Jahre',
    'female_46_plus' => 'Weiblich 46+ Jahre',
    'male_46_plus' => 'Männlich 46+ Jahre',
    'no_members_data' => 'Keine Mitgliederdaten verfügbar.',
    'members_registered_help' => 'In der Plattform registrierte Mitglieder',
    'members_affiliated_help' => 'Mitglieder mit aktiver Mitgliedschaft',

    // Welcome Banner
    'welcome_back' => 'Willkommen zurück',
    'federation_overview' => 'Übersicht über Ihre Verbandsaktivität',
    'member_number' => 'Mitgliedsnr.',
    'id_number' => 'ID-Nr.',

    // Stats Widget
    'individual_members' => 'Einzelmitglieder',
    'total_in_federation' => 'Gesamt im Verband',
    'active_affiliations' => 'Aktive Mitgliedschaften',
    'collective_entities' => 'Kollektive Einheiten',

    // Recent Activity
    'recent_actions' => 'Letzte Aktionen',

    // Licenses by Sport Chart
    'licenses_by_sport_heading' => ':role nach Sportart (:year)',
    'active_licenses' => 'Aktive Lizenzen',
    'count' => 'Anzahl',
    'role_athlete' => 'Athleten',
    'role_coach' => 'Trainer',
    'role_technical_official' => 'Technische Funktionäre',

    // Territorial Affiliations Table
    'territorial_affiliation_income' => 'Mitgliedschaftseinnahmen nach Territorialverband (:year)',
    'territorial_association' => 'Territorialverband',
    'no_data' => 'Keine Daten zum Anzeigen',
    'grand_total' => 'Gesamtsumme',

    // Entities by District Chart
    'entity_count_label' => 'Anzahl der Einheiten',

    // Individuals by District Chart
    'individuals_by_district' => 'Einzelmitglieder nach Bezirk',
    'total_active_individual_members' => 'Gesamtzahl der aktiven Einzelmitglieder',
    'individual_count_label' => 'Anzahl der Mitglieder',

    // Chart Labels (Total vs Active)
    'total_registered' => 'Gesamt',
    'active_members' => 'Aktiv',
    'active_entities' => 'Aktiv',
    'total_active_entities' => 'Gesamtzahl der aktiven Einheiten',

    // Chart Descriptions
    'entity_affiliation_revenue_desc' => 'Monatliche Einnahmen aus Einheiten-Mitgliedschaftsplänen',
    'individual_affiliation_revenue_desc' => 'Monatliche Einnahmen aus Einzel-Mitgliedschaftsplänen',
    'entity_license_revenue_desc' => 'Monatliche Einnahmen aus Einheiten-Lizenzkäufen',
    'individual_license_revenue_desc' => 'Monatliche Einnahmen aus Einzel-Lizenzkäufen',
    'individual_sport_licenses_desc' => 'Aktive Sportlizenzen nach Rolle (Athleten, Trainer, Funktionäre)',
    'entity_sport_licenses_desc' => 'Aktive Sportlizenzen im Besitz von Vereinen',

    // Stats Overview
    'total_description' => 'Gesamt',

    // Dashboard Sections
    'statistics_section' => 'Statistiken',
    'ranking' => 'Rangliste',
    'logo' => 'Logo',

    // License Revenue Charts
    'annual_entity_license_revenue' => 'Jährliche Einnahmen aus Einheiten-Lizenzen',
    'annual_individual_license_revenue' => 'Jährliche Einnahmen aus Einzel-Lizenzen',

    // Sport License Charts
    'individual_sport_licenses_by_role' => 'Einzel-Sportlizenzen',
    'entity_sport_licenses' => 'Sportvereins-Lizenzen',
    'license_count' => 'Anzahl der Lizenzen',

    // Info Messages
    'license_revenue_organization_only' => 'Die angezeigten Ergebnisse gelten nur für Lizenzen Ihrer Organisation.',

    // Monthly Payments Table
    'monthly_payments_title' => 'Monatliche Zahlungen',
    'monthly_payments_desc' => 'Gesamtbeträge bezahlter Dokumente nach Kategorie und Monat',
    'year' => 'Jahr',
    'category' => 'Kategorie',
    'entity_affiliations' => 'Einheiten-Mitgliedschaften',
    'individual_affiliations' => 'Einzel-Mitgliedschaften',
    'entity_licenses' => 'Einheiten-Lizenzen',
    'individual_licenses' => 'Einzel-Lizenzen',
    'event_registrations' => 'Veranstaltungsanmeldungen',
    'certifications' => 'Zertifizierungen',
    'entity_insurances' => 'Einheiten-Versicherungen',
    'individual_insurances' => 'Einzel-Versicherungen',
    'others' => 'Sonstige',
    'month_jan' => 'Jan',
    'month_feb' => 'Feb',
    'month_mar' => 'Mär',
    'month_apr' => 'Apr',
    'month_may' => 'Mai',
    'month_jun' => 'Jun',
    'month_jul' => 'Jul',
    'month_aug' => 'Aug',
    'month_sep' => 'Sep',
    'month_oct' => 'Okt',
    'month_nov' => 'Nov',
    'month_dec' => 'Dez',
];
