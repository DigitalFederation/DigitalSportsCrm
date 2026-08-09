<?php

return [
    // Page titles
    'title' => 'Diagnosezentrum für Teilnahmeberechtigung',
    'subtitle' => 'Diagnostizieren Sie, warum Einzelpersonen möglicherweise nicht in Anmeldelisten erscheinen',

    // Tab titles
    'tab_individual_profile' => 'Persönliches Profil',
    'tab_event_enrollment' => 'Veranstaltungsanmeldung',
    'tab_license_availability' => 'Lizenzverfügbarkeit',

    // Individual Profile Tab
    'individual_profile_title' => 'Diagnose des persönlichen Profils',
    'individual_profile_description' => 'Suchen Sie nach einer Einzelperson, um ihr vollständiges Berechtigungsprofil einzusehen und zu verstehen, warum sie für verschiedene Rollen angemeldet werden kann oder nicht.',
    'search_placeholder' => 'Suche nach internationalem Code, Name oder E-Mail...',
    'no_individual_selected' => 'Keine Einzelperson ausgewählt',
    'search_to_start' => 'Suchen Sie nach einer Einzelperson, um ihr Berechtigungsprofil anzuzeigen.',
    'quick_status' => 'Schnellstatus',

    // Role labels
    'role_athlete' => 'Sportler',
    'role_coach' => 'Trainer',
    'role_referee' => 'Schiedsrichter',
    'role_official' => 'Offizieller',

    // Sections
    'federation_memberships' => 'Verbandsmitgliedschaften',
    'entity_memberships' => 'Einrichtungsmitgliedschaften',
    'professional_roles' => 'Berufliche Rollen',
    'certifications' => 'Zertifizierungen (Schiedsrichterprüfung)',
    'active_licenses' => 'Aktive Lizenzen',

    // Table headers
    'federation' => 'Verband',
    'entity' => 'Einrichtung',
    'type' => 'Typ',
    'status' => 'Status',
    'since' => 'Seit',
    'sports' => 'Sportarten',
    'role' => 'Rolle',
    'source' => 'Quelle',
    'certification' => 'Zertifizierung',
    'grants_role' => 'Gewährt Rolle',
    'action_needed' => 'Aktion erforderlich',
    'license' => 'Lizenz',
    'expires' => 'Läuft ab',

    // Federation types
    'local' => 'Lokal',
    'main' => 'Haupt',
    'modalidade' => 'Sportart',

    // Empty states
    'no_federation_memberships' => 'Keine Verbandsmitgliedschaften gefunden.',
    'no_entity_memberships' => 'Keine Einrichtungsmitgliedschaften gefunden.',
    'no_professional_roles' => 'Keine beruflichen Rollen zugewiesen.',
    'no_certifications' => 'Keine Zertifizierungen zugewiesen.',
    'no_active_licenses' => 'Keine aktiven Lizenzen.',
    'unknown_federation' => 'Unbekannter Verband',
    'unknown_entity' => 'Unbekannte Einrichtung',
    'unknown_license' => 'Unbekannte Lizenz',
    'unknown_certification' => 'Unbekannte Zertifizierung',

    // Sources
    'source_direct_assignment' => 'Direkte Zuweisung',
    'source_entity_assignment' => 'Zuweisung über Einrichtung',

    // Certification action
    'action_activate_certification' => 'AKTIVIEREN, um die Rolle freizuschalten',

    // Quick status reasons
    'not_checked' => 'Nicht geprüft',
    'reason_no_active_federation' => 'Keine aktive Verbandsmitgliedschaft',
    'reason_no_active_entity' => 'Keine aktive Einrichtungsmitgliedschaft',
    'reason_not_registered_athlete' => 'Nicht als Sportler registriert',
    'reason_registered_athlete' => 'Als Sportler registriert',
    'reason_no_coach_role' => 'Keine berufliche Rolle als TRAINER',
    'reason_has_coach_role' => 'Hat die Rolle TRAINER zugewiesen',
    'reason_cert_pending_activation' => 'Zertifizierung vorhanden, aber Aktivierung AUSSTEHEND',
    'reason_no_referee_cert' => 'Keine Schiedsrichterzertifizierung zugewiesen',
    'reason_no_referee_role' => 'Keine berufliche Rolle als SCHIEDSRICHTER (Zertifizierung prüfen)',
    'reason_has_referee_role' => 'Hat die Rolle SCHIEDSRICHTER zugewiesen',
    'reason_no_active_membership' => 'Keine aktive Mitgliedschaft',
    'reason_active_member' => 'Aktives Mitglied',

    // Event Enrollment Tab
    'event_enrollment_title' => 'Diagnose der Veranstaltungsanmeldung',
    'event_enrollment_description' => 'Wählen Sie eine Veranstaltung und eine Einzelperson aus, um zu diagnostizieren, warum diese möglicherweise nicht in der Anmeldeliste für eine bestimmte Rolle erscheint.',
    'select_event' => 'Veranstaltung auswählen',
    'select_event_placeholder' => '-- Eine Veranstaltung auswählen --',
    'select_competition' => 'Wettkampf auswählen (optional)',
    'all_competitions' => '-- Alle Wettkämpfe --',
    'select_role' => 'Zu diagnostizierende Rolle',
    'search_individual' => 'Einzelperson suchen',
    'run_diagnostic' => 'Diagnose ausführen',
    'selected' => 'Ausgewählt',
    'select_event_first' => 'Wählen Sie zuerst eine Veranstaltung aus',
    'select_event_to_start' => 'Wählen Sie eine Veranstaltung aus der Auswahlliste, um die Diagnose zu beginnen.',

    // Diagnostic results
    'eligible_as_role' => 'BERECHTIGT als :role',
    'not_eligible_as_role' => 'NICHT BERECHTIGT als :role',
    'passed' => 'BESTANDEN',
    'failed' => 'FEHLGESCHLAGEN',
    'suggestions' => 'Vorgeschlagene Maßnahmen',

    // Check labels
    'check_federation_membership' => 'Verbandsmitgliedschaft',
    'check_entity_membership' => 'Einrichtungsmitgliedschaft',
    'check_athlete_registration' => 'Sportlerregistrierung',
    'check_coach_role' => 'Berufliche Rolle als Trainer',
    'check_referee_role' => 'Berufliche Rolle als Schiedsrichter',
    'check_referee_cert_exists' => 'Schiedsrichterzertifizierung vorhanden',
    'check_referee_cert_active' => 'Zertifizierung ist aktiv',
    'check_required_certs' => 'Erforderliche Zertifizierungen',
    'check_required_licenses' => 'Erforderliche Lizenzen',
    'check_active_membership' => 'Aktive Mitgliedschaft',
    'check_not_enrolled' => 'Noch nicht angemeldet',

    // Check messages - Passed
    'check_federation_membership_passed' => 'Aktives Mitglied von :federation',
    'check_federation_membership_athlete_passed' => 'Hat eine aktive Verbandsmitgliedschaft',
    'check_federation_membership_coach_passed' => 'Hat eine aktive Verbandsmitgliedschaft',
    'check_entity_membership_passed' => 'Aktives Mitglied von: :entities',
    'check_entity_membership_passed_coach' => 'Hat eine aktive Einrichtungsmitgliedschaft',
    'check_athlete_registration_passed' => 'Als Sportler für :sport registriert',
    'check_coach_role_passed' => 'Hat die berufliche Rolle als TRAINER zugewiesen',
    'check_referee_role_passed' => 'Hat die berufliche Rolle als SCHIEDSRICHTER zugewiesen',
    'check_referee_cert_exists_passed' => 'Hat Schiedsrichterzertifizierung(en): :certs',
    'check_referee_cert_active_passed' => 'Hat mindestens eine aktive Schiedsrichterzertifizierung',
    'check_required_certs_passed' => 'Hat alle erforderlichen Zertifizierungen',
    'check_required_licenses_passed' => 'Hat alle erforderlichen Lizenzen',
    'check_active_membership_passed' => 'Hat eine aktive Mitgliedschaft (kann als Offizieller angemeldet werden)',
    'check_not_enrolled_passed' => 'Noch nicht für diese Veranstaltung angemeldet',

    // Check messages - Failed
    'check_federation_membership_failed' => 'Keine aktive Verbandsmitgliedschaft gefunden',
    'check_entity_membership_failed' => 'Keine aktive Einrichtungsmitgliedschaft gefunden',
    'check_athlete_registration_failed' => 'In keiner Einrichtung als Sportler registriert',
    'check_athlete_wrong_sport' => 'Für :registered registriert, die Veranstaltung erfordert jedoch :required',
    'check_coach_role_failed' => 'Hat keine berufliche Rolle als TRAINER zugewiesen',
    'check_referee_role_failed' => 'Hat keine berufliche Rolle als SCHIEDSRICHTER zugewiesen',
    'check_referee_role_cert_pending' => 'Zertifizierung ":cert" vorhanden, aber AUSSTEHEND – Rolle SCHIEDSRICHTER noch nicht zugewiesen',
    'check_referee_cert_exists_failed' => 'Keine Zertifizierung vom Typ Schiedsrichter zugewiesen',
    'check_referee_cert_no_certs' => 'Keine Schiedsrichterzertifizierungen zu prüfen',
    'check_referee_cert_pending' => 'Schiedsrichterzertifizierung(en) vorhanden, aber AUSSTEHEND: :certs',
    'check_referee_cert_inactive' => 'Keine aktiven Schiedsrichterzertifizierungen gefunden',
    'check_required_certs_failed' => 'Fehlende erforderliche Zertifizierung(en): :certs',
    'check_required_licenses_failed' => 'Fehlende erforderliche Lizenz(en): :licenses',
    'check_active_membership_failed' => 'Keine aktive Mitgliedschaft in einem Verband oder einer Einrichtung',
    'check_already_enrolled' => 'Bereits für diese Veranstaltung in dieser Rolle angemeldet',

    // Suggestions
    'suggestion_activate_membership' => 'Verbands-/Einrichtungsmitgliedschaft aktivieren',
    'suggestion_join_entity' => 'Einer Einrichtung als Mitglied beitreten',
    'suggestion_register_as_athlete' => 'Als Sportler unter Einrichtung > Sportler registrieren',
    'suggestion_register_for_sport' => 'Als Sportler für die richtige Sportart registrieren',
    'suggestion_assign_coach_role' => 'Rolle TRAINER unter Einrichtung > Trainer zuweisen',
    'suggestion_attribute_referee_cert' => 'Eine Schiedsrichterzertifizierung unter Verband > Zertifizierungen zuweisen',
    'suggestion_activate_certification' => 'Die ausstehende Zertifizierung AKTIVIEREN, um die Rolle SCHIEDSRICHTER zu gewähren',
    'suggestion_check_cert_status' => 'Zertifizierungsstatus prüfen – möglicherweise abgelaufen oder storniert',
    'suggestion_obtain_required_cert' => 'Die erforderliche(n) Zertifizierung(en) erwerben und aktivieren',
    'suggestion_obtain_required_license' => 'Die erforderliche(n) Lizenz(en) erwerben und aktivieren',

    // Membership details
    'member_of_federations' => 'Verband/Verbände: :federations',
    'member_of_entities' => 'Einrichtung(en): :entities',

    // License Availability Tab
    'license_availability_title' => 'Diagnose der Lizenzverfügbarkeit',
    'license_availability_description' => 'Diagnostizieren Sie, warum bestimmte Lizenzen möglicherweise nicht in der Kaufliste erscheinen.',
    'coming_soon' => 'Demnächst verfügbar...',
];
