<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'members_list' => 'Mitgliederliste',
    'member_list' => 'Mitgliederliste',
    'entities' => 'Kollektive Einrichtungen',
    'entity_detail' => 'Einrichtungsdetails',
    'entities_to_approve' => 'Zu genehmigende Einrichtungen',
    'create_entity' => 'Einrichtung erstellen',
    'create_entity_account' => 'Ein Einrichtungskonto erstellen',
    'edit_entity_record' => 'Einrichtungsdatensatz bearbeiten',

    // Actions
    'create_individual' => 'Einzelperson erstellen',
    'individuals_to_approve' => 'Zu genehmigende Einzelpersonen',
    'invite_member' => 'Mitglied einladen',
    'submit_request' => 'Anfrage absenden',
    'approve_entity' => 'Einrichtung genehmigen',
    'accept_request' => 'Diese Anfrage annehmen?',
    'view_all' => 'Alle anzeigen',
    'see_all_instructors' => 'Alle Ausbilder anzeigen',
    'open_url' => 'URL öffnen',

    // Table headers
    'gender' => 'Geschlecht',
    'id_number' => 'Ausweisnummer',
    'national_affiliation' => "{$primaryShortName}-Mitgliedschaft",
    'table_name' => 'Name',
    'table_country' => 'Land',
    'table_national_fed_nr' => 'Nat. Verb.-Nr.',
    'table_cmas_zone' => 'Internationale Zone',
    'table_sub_region' => 'Teilregion',
    'table_actions' => 'Aktionen',
    'table_nationality' => 'Staatsangehörigkeit',
    'table_email' => 'E-Mail',
    'table_requested' => 'Angefragt',
    'table_federation' => 'Organisation',
    'table_type' => 'Typ',
    'table_status' => 'Mitgliederstatus',
    'table_national_number' => 'Nationale Nummer',
    'table_number' => 'Nummer',
    'table_date' => 'Datum',
    'table_total' => 'Gesamt',
    'table_zone_or_association' => 'Zone oder Gebietsverband',

    // Form labels
    'name' => 'Name der Einrichtung',
    'given_name' => 'Vorname',
    'family_name' => 'Nachname',
    'nationality' => 'Staatsangehörigkeit',
    'federation' => 'Verband',
    'birthdate' => 'Geburtsdatum',
    'member_number' => 'Mitgliedsnummer',
    'affiliation_status' => 'Mitgliedschaftsstatus',
    'affiliation_active' => 'Aktiv',
    'affiliation_inactive' => 'Inaktiv',
    'valid_member_code' => 'Gültiger Mitgliedscode',

    // Form sections
    'information' => 'Informationen',
    'entity_logo' => 'Logo der Einrichtung',
    'club_school_center_name' => 'Name von Verein/Schule/Zentrum',
    'legal_name' => 'Name der steuerlichen Registrierung',
    'responsible_person_name' => 'Name der verantwortlichen Person',
    'nif' => 'Steuernummer (NIF)',
    'national_fed_nr' => 'Nat. Verb.-Nr.',
    'affiliate_nr' => 'Mitglieds-Nr.',
    'hq_location' => 'Standort des Hauptsitzes',
    'district' => 'Bezirk',
    'zones' => 'Zonen',
    'no_zones_assigned' => 'Keine Zonen zugewiesen',
    'address' => 'Anschrift',
    'location' => 'Ort',
    'zip_code' => 'Postleitzahl',
    'country' => 'Land',
    'select_option' => '-- Eine Option auswählen --',
    'public_contacts' => 'Öffentliche Kontakte',
    'contact_email' => 'Kontakt-E-Mail',
    'website' => 'Webseite',
    'phone_number' => 'Telefonnummer',
    'social_media_links' => 'Links zu sozialen Medien',
    'facebook_url' => 'Facebook-URL',
    'x_url' => 'X-URL',
    'instagram_url' => 'Instagram-URL',
    'linkedin_url' => 'LinkedIn-URL',

    // Terms and policies
    'terms_policies' => 'Bedingungen & Richtlinien',
    'terms_confirm' => 'Ich bestätige, dass die Einrichtung die folgenden Bedingungen akzeptiert:',
    'terms_of_service' => 'Nutzungsbedingungen',
    'and' => 'und',
    'privacy_policy' => 'Datenschutzerklärung',
    'data_sharing_confirm' => 'Ich bestätige, dass die Einrichtung der Weitergabe von Daten an autorisierte Dritte zustimmt, wie beschrieben in der',
    'data_sharing_policy' => 'Richtlinie zur Datenweitergabe',
    'save_record' => 'Datensatz speichern',

    // User login section
    'user_login_information' => 'Anmeldeinformationen des Nutzers',
    'user_login_info_description' => 'Nach Auswahl der E-Mail-Adresse des Nutzers wird eine E-Mail versendet, damit die Person ihre eigenen Zugangsdaten registrieren kann.',
    'user_login_email' => 'Anmelde-E-Mail des Nutzers',
    'confirm_user_login_email' => 'Anmelde-E-Mail des Nutzers bestätigen',
    'confirm_email_address' => 'Die E-Mail-Adresse bestätigen',
    'email_credential_hint' => 'E-Mail-Zugangsdaten für die Anmeldung des Nutzers',
    'entity_creation_info' => 'Bei der Erstellung eines Einrichtungsdatensatzes wird diesem Datensatz automatisch auch ein Nutzer zugewiesen. Eine E-Mail wird an die gewählte E-Mail-Adresse gesendet, damit der Nutzer seine eigenen Zugangsdaten registrieren kann. Danach kann sich die Person an der Plattform anmelden.',

    // Modal content
    'member_invitation_form' => 'Formular zur Mitgliedereinladung',
    'member_request' => 'Mitgliedereinladung',
    'member_request_description' => 'Sie können dieses Formular verwenden, um Mitglieder anhand ihrer persönlichen ID oder Mitgliedsnummer einzuladen. Fordern Sie eine dieser Angaben vom Mitglied an, bevor Sie diese Einladung senden.',
    'or_separator' => 'ODER',

    // Zone assignment
    'zone_auto_assigned' => 'Die Zone wird automatisch anhand Ihres Verbands zugewiesen.',
    'zone_will_be' => 'Zone',
    'zone_edit_restricted' => "Nur {$primaryShortName} oder Admin können dieses Feld bearbeiten.",

    // Entity approval
    'approval_national_federation_message' => 'Sie sind dabei, diese Einrichtung zu genehmigen. Eine Mitgliedsnummer wird automatisch zugewiesen.',
    'approval_association_message' => 'Sie sind dabei, diese Einrichtung für Ihren Verband zu genehmigen.',
    'member_number_auto_generated' => 'Die Mitgliedsnummer wird bei der Genehmigung automatisch generiert.',
    'member_number_primary_federation_only' => "Hinweis: Nur {$primaryShortName} kann die nationale Verbandsnummer zuweisen. Die Einrichtung wird für Ihren Verband ohne Mitgliedsnummer genehmigt.",

    // Show page
    'tax_identification_number' => 'Steueridentifikationsnummer',
    'hq_address_city_postal' => 'Anschrift, Ort, Postleitzahl des Hauptsitzes',
    'individuals' => 'Einzelpersonen',
    'diving_certifications' => 'Tauchzertifizierungen',
    'scientific_certifications' => 'Wissenschaftliche Zertifizierungen',
    'diving_licenses' => 'Lizenzen für Tauchdienstleister',
    'scientific_licenses' => 'Wissenschaftliche Lizenzen',
    'sport_licenses' => 'Sportlizenzen',
    'instructors' => 'Ausbilder',
    'active' => 'aktiv',
    'no_instructors_yet' => 'Noch keine Ausbilder',
    'federations' => 'Verband/Verbände',
    'associations' => 'Verbände',
    'federation_and_associations' => 'Verband & Verbände',
    'no_individuals_yet' => 'Noch keine Einzelpersonen',
    'local_federation' => 'Verband',
    'main_federation' => 'Hauptverband',
    'no_federation_memberships' => 'Keine Verbandsmitgliedschaften gefunden',
    'no_association_memberships' => 'Keine Verbandsmitgliedschaften gefunden',
    'table_association' => 'Verband',
    'association_type_territorial' => 'Territorial',
    'association_type_nacional' => 'National',
    'association_type_modalidade' => 'Sportart',

    // Documents
    'documents_invoices' => 'Dokumente & Rechnungen',
    'view' => 'Anzeigen',
    'no_documents_found' => 'Keine Dokumente gefunden',
    'no_documents_description' => 'Für diese Einrichtung wurden noch keine Dokumente oder Rechnungen erstellt.',
    'showing_last_documents' => 'Es werden die letzten :count Dokumente angezeigt',

    // Messages
    'invalid_cmas_code' => 'Der internationale Code ist ungültig. Bitte überprüfen Sie die angegebenen Informationen.',
    'invalid_member_number' => 'Die Mitgliedsnummer ist ungültig. Bitte überprüfen Sie die angegebenen Informationen.',
    'member_must_have_federation' => 'Dieses Mitglied muss eine Verbandsbeziehung (aktiv oder ausstehend) haben und darf nicht bereits Teil Ihrer Einrichtung sein.',
    'invitation_sent_success' => 'Die Mitgliedereinladung wurde erfolgreich gesendet. Bitte geben Sie dem Mitglied Zeit, Ihre Anfrage zu prüfen.',
    'error_creating_record' => 'Fehler beim Erstellen dieses Datensatzes: :error',

    // Entity Profile Tabs
    'no_certifications_message' => 'Dieser Einrichtung wurden noch keine Zertifizierungen zugewiesen.',
    'no_licenses_message' => 'Dieser Einrichtung wurden noch keine Lizenzen zugewiesen.',

    // Federation membership
    'designation' => 'Bezeichnung',
    'member_approved' => 'Genehmigtes Mitglied',
    'member_pending_approval' => 'Genehmigung ausstehend',
    'federation_membership_info' => 'Diese Tabelle zeigt Ihren Mitgliedschaftsstatus im Verband und in den Verbänden.',

    // Entity Dashboard
    'dashboard' => [
        'entity_profile' => 'Einrichtungsprofil',
        'members_to_approve' => 'Zu genehmigende Mitglieder',
        'no_pending_members' => 'Keine ausstehenden Mitgliedsanfragen',
        'entity_affiliations' => 'Mitgliedschaften der Einrichtung',
        'no_affiliations' => 'Keine Mitgliedschaften gefunden',
        'no_sport_licenses' => 'Keine Sportlizenzen',
        'no_diving_licenses' => 'Keine Tauchlizenzen',
        'no_entity_found' => 'Einrichtung nicht gefunden',
        'no_entity_associated' => 'Ihrem Konto ist keine Einrichtung zugeordnet.',
    ],

    // Error messages
    'committee_not_found' => 'Das erforderliche Gremium für den Einrichtungstyp :type ist nicht konfiguriert. Bitte wenden Sie sich an den Support.',

    // Map
    'get_directions' => 'Route abrufen',

    // International Portal
    'cmas_portal_access' => 'Zugang zum internationalen Portal',
    'has_cmas_portal_account' => 'Hat ein Konto im internationalen Portal',
    'cmas_portal_description' => 'Aktivieren Sie dieses Kästchen, wenn die Einrichtung ein Konto im internationalen Portal hat',

    // Public Page Management
    'public_page' => [
        'title' => 'Verwaltung der öffentlichen Seite',
        'subtitle' => 'Verwalten Sie das öffentliche Profil und die Inhalte Ihrer Organisation',
        'view_public_page' => 'Öffentliche Seite anzeigen',
        'tabs' => [
            'general' => 'Allgemeine Einstellungen',
            'featured_locations' => 'Hervorgehobene Standorte',
            'courses' => 'Tauchkurse',
        ],
        'background_image' => 'Hintergrundbild des Profils',
        'current_background' => 'Aktueller Hintergrund',
        'current_image' => 'Aktuelles Bild',
        'confirm_remove_background' => 'Sind Sie sicher, dass Sie das Hintergrundbild entfernen möchten?',
        'background_removed' => 'Hintergrundbild erfolgreich entfernt.',
        'upload_file' => 'Eine Datei hochladen',
        'or_drag_drop' => 'oder per Drag & Drop',
        'image_requirements' => 'PNG, JPG, WEBP bis zu 2 MB',
        'preview' => 'Vorschau',
        'public_description' => 'Öffentliche Beschreibung',
        'description_help' => 'Diese Beschreibung wird auf Ihrer öffentlichen Profilseite angezeigt.',
        'save_settings' => 'Einstellungen speichern',
        'settings_saved' => 'Einstellungen erfolgreich gespeichert.',
        'featured_locations' => [
            'title' => 'Hervorgehobene Tauchstandorte',
            'description' => 'Wählen Sie die Tauchstandorte aus, die Sie auf Ihrem öffentlichen Profil hervorheben möchten.',
            'select_locations' => 'Standorte auswählen',
            'no_locations_selected' => 'Keine Tauchstandorte ausgewählt.',
            'selected_preview' => 'Vorschau der ausgewählten Standorte',
            'save_locations' => 'Hervorgehobene Standorte speichern',
            'locations_saved' => 'Hervorgehobene Standorte erfolgreich aktualisiert.',
            'create_new' => 'Neuen Standort erstellen',
        ],
    ],
];
