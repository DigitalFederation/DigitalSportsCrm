<?php

$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalName = config('branding.international.name', 'International Federation');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    // Page titles
    'licenses' => 'Lizenzen',
    'my_licenses_description' => 'Hier können Sie alle Ihre Lizenzen einsehen und neue Mitgliedslizenzen erwerben',
    'view_my_licenses' => 'Meine Lizenzen anzeigen',
    'no_federation_association_description' => 'Sie sind mit keinem Verband verbunden. Bitte wenden Sie sich an Ihren Verbandsadministrator, um diese Verbindung herzustellen, bevor Sie Lizenzen erwerben.',
    'no_international_license_access_description' => 'Sie sind mit keinem Verband verbunden, der über internationale Lizenzvereinbarungen verfügt. Nur Mitglieder von Verbänden mit internationalen Vereinbarungen können diese Lizenzen erwerben.',

    // Tab sections
    'basic_information' => 'Grundlegende Informationen',
    'roles_permissions' => 'Rollen und Berechtigungen',
    'requirements' => 'Anforderungen',
    'pricing' => 'Preisgestaltung',
    'availability' => 'Verfügbarkeit',
    'advanced_settings' => 'Erweiterte Einstellungen',

    // Document requirements sections
    'diving_professionals' => 'Tauchfachkräfte',

    // Purchase page titles and headers
    'Purchase License' => 'Lizenz erwerben',
    'Manage Licenses' => 'Lizenzen verwalten',
    'Manage Licenses for' => 'Lizenzen verwalten für',
    'License Purchased Successfully!' => 'Lizenz erfolgreich erworben!',
    'Purchase Successful!' => 'Kauf erfolgreich!',
    'Purchase Successful' => 'Kauf erfolgreich',
    'order_details' => 'Bestelldetails',

    // Page descriptions
    'Select and purchase a license for yourself' => 'Wählen und erwerben Sie eine Lizenz für sich selbst',
    'Purchase licenses for your entity or members' => 'Erwerben Sie Lizenzen für Ihre Einrichtung oder Ihre Mitglieder',

    // Information messages
    'Information' => 'Informationen',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. Please ensure your profile information is complete before proceeding.' => 'Wählen Sie eine Lizenz aus und fahren Sie mit der Zahlung fort. Ihre Lizenz wird automatisch aktiviert, sobald die Zahlung bestätigt ist. Bitte stellen Sie sicher, dass Ihre Profilinformationen vollständig sind, bevor Sie fortfahren.',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. For group purchases, you can select multiple members to receive the same license.' => 'Wählen Sie eine Lizenz aus und fahren Sie mit der Zahlung fort. Ihre Lizenz wird automatisch aktiviert, sobald die Zahlung bestätigt ist. Bei Gruppenkäufen können Sie mehrere Mitglieder auswählen, die dieselbe Lizenz erhalten sollen.',

    // Form labels and options
    'Select Federation' => 'Verband auswählen',
    'Select a federation...' => 'Verband auswählen...',
    'Select License' => 'Lizenz auswählen',
    'Purchase Type' => 'Kaufart',
    'Individual License' => 'Einzellizenz',
    'Group Purchase' => 'Gruppenkauf',
    'Select Member' => 'Mitglied auswählen',
    'Select Members' => 'Mitglieder auswählen',
    'Select a member...' => 'Mitglied auswählen...',

    // Purchase type descriptions
    'Purchase license for one specific member' => 'Lizenz für ein bestimmtes Mitglied erwerben',
    'Purchase licenses for multiple members' => 'Lizenzen für mehrere Mitglieder erwerben',

    // License information
    'License' => 'Lizenz',
    'License Code' => 'Lizenzcode',
    'License Holder' => 'Lizenzinhaber',
    'License Information' => 'Lizenzinformationen',
    'per license' => 'pro Lizenz',
    'license' => 'Lizenz',
    'start_date' => 'Startdatum',
    'expiry_date' => 'Ablaufdatum',
    'status' => 'Status',

    // Purchase summary
    'Purchase Summary' => 'Kaufübersicht',
    'Purchase Details' => 'Kaufdetails',
    'Entity' => 'Einrichtung',
    'Federation' => 'Verband',
    'Number of Members' => 'Anzahl der Mitglieder',
    'Price per License' => 'Preis pro Lizenz',
    'Total' => 'Gesamt',
    'Total Amount' => 'Gesamtbetrag',
    'Total Paid' => 'Gesamt bezahlt',

    // Status and dates
    'Status' => 'Status',
    'Active' => 'Aktiv',
    'Payment Confirmed' => 'Zahlung bestätigt',
    'Issue Date' => 'Ausstellungsdatum',
    'Expiration Date' => 'Ablaufdatum',
    'Today' => 'Heute',
    'Permanent' => 'Unbefristet',

    // International and codes
    'Pending Assignment' => 'Zuweisung ausstehend',
    'Order Number' => 'Bestellnummer',

    // Success messages
    'Your license has been activated and is ready to use' => 'Ihre Lizenz wurde aktiviert und ist einsatzbereit',
    'Your license purchase has been completed successfully' => 'Ihr Lizenzkauf wurde erfolgreich abgeschlossen',
    'All selected members have been automatically licensed' => 'Alle ausgewählten Mitglieder wurden automatisch lizenziert',
    'Your entity license has been automatically activated' => 'Die Lizenz Ihrer Einrichtung wurde automatisch aktiviert',

    // Certificate information
    'Your License Certificate' => 'Ihr Lizenzzertifikat',
    'Your license certificate is now available for download' => 'Ihr Lizenzzertifikat steht nun zum Download bereit',
    'License certificates are now available for download' => 'Die Lizenzzertifikate stehen nun zum Download bereit',
    'A confirmation email has been sent to your registered email address' => 'Eine Bestätigungs-E-Mail wurde an Ihre registrierte E-Mail-Adresse gesendet',
    'You will receive email confirmation shortly' => 'Sie erhalten in Kürze eine Bestätigung per E-Mail',

    // Next steps and information
    'What happens next?' => 'Wie geht es weiter?',
    'Important Information' => 'Wichtige Informationen',
    'Remember to renew before expiration date' => 'Denken Sie daran, vor dem Ablaufdatum zu verlängern',

    // Action buttons
    'View My Licenses' => 'Meine Lizenzen anzeigen',
    'Download Invoice' => 'Rechnung herunterladen',
    'Download Certificate' => 'Zertifikat herunterladen',
    'Back to Dashboard' => 'Zurück zum Dashboard',

    // Error messages
    'no_license_purchase_found' => 'Kein Lizenzkauf gefunden.',
    'entity_license_required_for_members' => 'Ihre Einrichtung muss über eine aktive Einrichtungslizenz verfügen, bevor Sie Mitgliedslizenzen erwerben können. Bitte erwerben Sie zunächst eine Einrichtungslizenz.',
    'entity_sport_license_required' => 'Ihre Einrichtung muss über eine aktive Einrichtungslizenz für diese Sportart verfügen, bevor Sie dafür Mitgliedslizenzen erwerben können. Bitte erwerben Sie zunächst eine Einrichtungslizenz für diese Sportart.',
    'No licenses available' => 'Keine Lizenzen verfügbar',
    'There are no licenses available for purchase in this federation at the moment.' => 'In diesem Verband sind derzeit keine Lizenzen zum Kauf verfügbar.',
    'There are no licenses available for entity purchase at the moment.' => 'Derzeit sind keine Lizenzen für den Kauf durch Einrichtungen verfügbar.',
    'No Federation Association' => 'Keine Verbandszugehörigkeit',
    'You are not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'Sie sind mit keinem Verband verbunden. Bitte wenden Sie sich an Ihren Verbandsadministrator, um diese Verbindung herzustellen, bevor Sie Lizenzen erwerben.',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'Ihre Einrichtung ist mit keinem Verband verbunden. Bitte wenden Sie sich an Ihren Verbandsadministrator, um diese Verbindung herzustellen, bevor Sie Lizenzen erwerben.',
    'No federation' => 'Kein Verband',

    // Dynamic messages with parameters
    'Purchase for' => 'Erwerben für',
    'Purchase for €:amount' => 'Erwerben für :amount €',
    'Request Free License' => 'Kostenlose Lizenz beantragen',
    ':count members selected' => ':count Mitglieder ausgewählt',
    'This license certifies you for: :role' => 'Diese Lizenz zertifiziert Sie für: :role',
    'Valid for sport: :sport' => 'Gültig für Sportart: :sport',
    'members' => 'Mitglieder',
    'Members' => 'Mitglieder',

    // Federation License Manager
    'Select which licenses this federation can offer to its member entities.' => 'Wählen Sie aus, welche Lizenzen dieser Verband seinen Mitgliedseinrichtungen anbieten kann.',
    'Search Licenses' => 'Lizenzen suchen',
    'Search by name or code...' => 'Suche nach Name oder Code...',
    'Filter by Committee' => 'Nach Ausschuss filtern',
    'All Committees' => 'Alle Ausschüsse',
    'selected' => 'ausgewählt',
    'International' => 'International',
    'No licenses found matching your filters.' => 'Keine Lizenzen gefunden, die Ihren Filtern entsprechen.',
    'No licenses available.' => 'Keine Lizenzen verfügbar.',
    'license(s) selected' => 'Lizenz(en) ausgewählt',
    'Cancel' => 'Abbrechen',
    'Save Changes' => 'Änderungen speichern',
    'Licenses updated successfully!' => 'Lizenzen erfolgreich aktualisiert!',

    // Debug information messages
    'cannot_proceed_with_purchase' => 'Kauf kann nicht fortgesetzt werden:',
    'entity_no_active_affiliation' => 'Die Einrichtung hat keine aktive Mitgliedschaft',
    'no_license_selected' => 'Keine Lizenz ausgewählt',
    'price_not_calculated' => 'Preis nicht berechnet',
    'calculated_price' => 'berechneter Preis',
    'no_members_selected' => 'Keine Mitglieder ausgewählt',
    'no_members_for_entity' => 'Für diese Einrichtung wurden keine Mitglieder gefunden. Bitte stellen Sie sicher, dass Ihrer Einrichtung Personen zugeordnet sind.',
    'validation_plan' => 'Validierungsplan',

    // Affiliation messages
    'Active Affiliation Required' => 'Aktive Mitgliedschaft erforderlich',
    'Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.' => 'Ihre Einrichtung muss über eine aktive Mitgliedschaft (Mitgliedschaftspaket) verfügen, um Lizenzen zu erwerben. Bitte stellen Sie sicher, dass die Mitgliedschaft Ihrer Einrichtung aktiv und bezahlt ist, bevor Sie fortfahren.',
    'You must have an active affiliation (membership package) to purchase licenses. Please ensure your individual membership is active and paid before proceeding.' => 'Sie müssen über eine aktive Mitgliedschaft (Mitgliedschaftspaket) verfügen, um Lizenzen zu erwerben. Bitte stellen Sie sicher, dass Ihre persönliche Mitgliedschaft aktiv und bezahlt ist, bevor Sie fortfahren.',

    // License validation error messages
    'already_has_license' => 'Sie besitzen bereits eine :status Lizenz dieses Typs',
    'Your profile already has this Active License' => 'Ihr Profil besitzt diese aktive Lizenz bereits',
    'Your license is pending payment' => 'Ihre Lizenz wartet auf die Zahlung',
    'missing_required_documents_detailed' => 'Diese Lizenz kann nicht beantragt werden. Die folgenden erforderlichen Dokumente fehlen: :documents. Bitte laden Sie diese Dokumente im Bereich Offizielle Dokumente hoch, bevor Sie diese Lizenz beantragen.',
    'missing_required_certifications' => 'Diese Lizenz kann nicht beantragt werden. Die folgenden erforderlichen Zertifizierungen fehlen: :certifications. Bitte erwerben Sie diese Zertifizierungen, bevor Sie diese Lizenz beantragen.',
    'members_missing_required_certifications' => 'Die folgenden Mitglieder verfügen nicht über die erforderlichen Zertifizierungen: :members',
    'license_requirements' => 'Lizenzanforderungen',
    'required_certifications' => 'Erforderliche Zertifizierungen',
    'required_documents' => 'Erforderliche Dokumente',
    'member_missing_certifications' => 'Fehlende Zertifizierungen: :certifications',
    'member_missing_documents' => 'Fehlende Dokumente: :documents',
    'member_must_have_active_affiliation' => 'Das Mitglied muss über eine aktive Mitgliedschaft verfügen',
    'show_ineligible_members' => 'Nicht teilnahmeberechtigte Mitglieder anzeigen',
    'hide_ineligible_members' => 'Nicht teilnahmeberechtigte Mitglieder ausblenden',
    'member_not_eligible' => 'Dieses Mitglied erfüllt die Anforderungen nicht',
    'no_eligible_members' => 'Keine teilnahmeberechtigten Mitglieder für diese Lizenz',
    'some_members_ineligible' => ':eligible von :total Mitgliedern sind für diese Lizenz teilnahmeberechtigt',
    'entity' => 'Einrichtung',
    'individual' => 'Einzelperson',
    'license_cannot_be_purchased_by' => 'Diese Lizenz kann nicht erworben werden von :type',
    'license_request_not_authorized' => 'Lizenzantrag nicht autorisiert: :reason',
    'license_parameter_null' => 'Der Lizenzparameter ist null',
    'license_missing_properties' => 'Der Lizenz fehlen erforderliche Eigenschaften (id oder license_code)',
    'cannot_determine_federation' => 'Der Verband für den Lizenzkauf kann nicht ermittelt werden',
    'license_price_not_configured' => 'Der Lizenzpreis ist für diesen Käufertyp nicht konfiguriert',

    // License fields
    'license_type' => 'Lizenztyp',
    'license_number' => 'Lizenznummer',
    'valid_until' => 'Gültig bis',
    'acceptance_date' => 'Datum der Annahme',
    'issue_date' => 'Ausstellungsdatum',
    'expiration_date' => 'Ablaufdatum',

    // Error messages for purchase flow
    'This license is not free' => 'Diese Lizenz ist nicht kostenlos.',
    'This license cannot be purchased with this method' => 'Diese Lizenz kann nicht mit dieser Methode erworben werden.',

    // License status messages
    'Your profile already has this Active License' => 'Ihr Profil besitzt diese aktive Lizenz bereits',
    'Your license is pending payment' => 'Ihre Lizenz wartet auf die Zahlung',
    'Your license is pending admin validation' => 'Ihre Lizenz wartet auf die Validierung durch die Verwaltung',
    'Your license is pending technical director approval' => 'Ihre Lizenz wartet auf die Genehmigung durch den technischen Leiter',
    'Your license is being processed' => 'Ihre Lizenz wird bearbeitet',

    // New form translations
    'Search licenses' => 'Lizenzen suchen',
    'Search licenses...' => 'Lizenzen suchen...',
    'licenses found' => 'Lizenzen gefunden',
    'Sport Committee' => 'Sportausschuss',
    'All Sports' => 'Alle Sportarten',
    'Role' => 'Rolle',
    'Price' => 'Preis',
    'Free' => 'Kostenlos',
    'Select' => 'Auswählen',
    'Purchase' => 'Erwerben',
    'Request' => 'Beantragen',
    'Contact Support' => 'Support kontaktieren',
    'Membership Required' => 'Mitgliedschaft erforderlich',

    // Admin validation
    'license_pending_validation_requires_approval' => 'Die Lizenz wartet auf die Validierung und erfordert die Genehmigung durch die Verwaltung.',
    'validate_and_approve' => 'Validieren und genehmigen',
    'reject_validation' => 'Validierung ablehnen',

    // Entity pending licenses
    'entity_has_pending_licenses' => 'Ihre Einrichtung hat ausstehende Lizenzen, die auf die Zahlung warten',
    'invitations_available_after_payment' => 'Einladungen für Athleten und Trainer sind verfügbar, sobald die Lizenzzahlung abgeschlossen ist',
    'complete_payment_to_enable_invitations' => 'Schließen Sie die Zahlung ab, um Ihre Lizenzen zu aktivieren und die Einladungsfunktionen freizuschalten',
    'pending_licenses_for_sports' => 'Ausstehende Lizenzen für: :sports',
    'license_approved_successfully' => 'Lizenz erfolgreich genehmigt.',
    'error_approving_license' => 'Fehler beim Genehmigen der Lizenz: ',
    'license_not_in_approvable_state' => 'Die Lizenz befindet sich nicht in einem Zustand, der eine Genehmigung zulässt',
    'license_validation_rejected' => 'Lizenzvalidierung abgelehnt',
    'license_canceled' => 'Lizenz storniert',
    'cannot_activate_unpaid_license' => 'Lizenz kann nicht aktiviert werden: Die Zahlung wurde nicht abgeschlossen. Bitte stellen Sie sicher, dass das zugehörige Zahlungsdokument bezahlt ist, bevor Sie aktivieren.',

    // License state translations
    'statuses' => [
        'ActiveLicenseAttributedState' => 'Aktiv',
        'PendingLicenseAttributedState' => 'Ausstehend',
        'PendingTechnicalDirectorApprovalLicenseAttributedState' => 'Genehmigung durch TL ausstehend',
        'PendingValidationLicenseAttributedState' => 'Validierung durch Verwaltung ausstehend',
        'CanceledLicenseAttributedState' => 'Storniert',
        'SuspendedLicenseAttributedState' => 'Ausgesetzt',
        'ExpiredLicenseAttributedState' => 'Abgelaufen',
        'ProvisionalLicenseAttributedState' => 'Vorläufig',
    ],

    // State translations for the states themselves
    'states' => [
        'pending' => 'Ausstehend',
        'active' => 'Aktiv',
        'expired' => 'Abgelaufen',
        'suspended' => 'Ausgesetzt',
        'canceled' => 'Storniert',
        'provisional' => 'Vorläufig',
        'waiting_approval' => 'Warten auf Genehmigung',
        'pending_validation' => 'Validierung ausstehend',
        'pending_technical_director_approval' => 'Genehmigung durch technischen Leiter ausstehend',
        'no_license' => 'Keine Lizenz',
    ],

    // International License Specific
    'Active Affiliation Required' => 'Aktive Mitgliedschaft erforderlich',
    'You must have an active affiliation (membership package) to purchase international licenses. Please ensure your individual membership is active and paid before proceeding.' => 'Sie müssen über eine aktive Mitgliedschaft (Mitgliedschaftspaket) verfügen, um internationale Lizenzen zu erwerben. Bitte stellen Sie sicher, dass Ihre persönliche Mitgliedschaft aktiv und bezahlt ist, bevor Sie fortfahren.',
    'Search international licenses' => 'Internationale Lizenzen suchen',
    'Search international licenses...' => 'Internationale Lizenzen suchen...',
    'international licenses found' => 'internationale Lizenzen gefunden',
    'International License' => 'Internationale Lizenz',
    'No international licenses available' => 'Keine internationalen Lizenzen verfügbar',
    'No international licenses are currently available for your federation.' => 'Für Ihren Verband sind derzeit keine internationalen Lizenzen verfügbar.',
    'No international licenses match your search criteria.' => 'Keine internationalen Lizenzen entsprechen Ihren Suchkriterien.',
    'Purchase International License' => 'Internationale Lizenz erwerben',
    'International License Purchase Success' => 'Kauf der internationalen Lizenz erfolgreich',
    'Purchase Initiated Successfully' => 'Kauf erfolgreich eingeleitet',
    'Your international license purchase has been initiated. Please complete the payment to activate your license.' => 'Ihr Kauf einer internationalen Lizenz wurde eingeleitet. Bitte schließen Sie die Zahlung ab, um Ihre Lizenz zu aktivieren.',
    'International License Details' => 'Details zur internationalen Lizenz',
    'View National Licenses' => 'Nationale Lizenzen anzeigen',
    'Select and purchase an international license for yourself' => 'Wählen und erwerben Sie eine internationale Lizenz für sich selbst',
    'No International License Access' => 'Kein Zugang zu internationalen Lizenzen',
    'Back to International Licenses' => 'Zurück zu den internationalen Lizenzen',
    'View My International Licenses' => 'Meine internationalen Lizenzen anzeigen',
    'Purchase International Licenses for Members' => 'Internationale Lizenzen für Mitglieder erwerben',
    'Select members and purchase international licenses on their behalf' => 'Wählen Sie Mitglieder aus und erwerben Sie in ihrem Namen internationale Lizenzen',
    'Purchase International Entity License' => 'Internationale Einrichtungslizenz erwerben',
    'Purchase an international license for your organization' => 'Erwerben Sie eine internationale Lizenz für Ihre Organisation',
    'Switch to International Entity License Purchase' => 'Zum Kauf einer internationalen Einrichtungslizenz wechseln',
    'Switch to International Member License Purchase' => 'Zum Kauf einer internationalen Mitgliedslizenz wechseln',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing international licenses.' => 'Ihre Einrichtung ist mit keinem Verband verbunden. Bitte wenden Sie sich an Ihren Verbandsadministrator, um diese Verbindung herzustellen, bevor Sie internationale Lizenzen erwerben.',

    // Table headers
    'licenses_title' => 'Lizenzen',
    'name' => 'Name',
    'license_name' => 'Name der Lizenz',
    'year' => 'Jahr',
    'actions' => 'Aktionen',
    'sport_commission' => 'Sportkommission',
    'sport_categories' => 'Sportkategorien',
    'not_active' => 'Nicht aktiv',
    'assign_individual_license' => 'Einzellizenz zuweisen',
    'assign_entity_license' => 'Einrichtungslizenz zuweisen',

    // Separated license page titles
    'Sport Club Licenses' => 'Sportvereinslizenzen',
    'Sport Licenses' => 'Sportlizenzen',
    'International Entity Licenses' => "{$internationalName} Einrichtungslizenzen",
    'International Professional Licenses' => "{$internationalName} Berufslizenzen",
    'Scientific Entity Licenses' => 'Wissenschaftliche Einrichtungslizenzen',
    'Scientific Professional Licenses' => 'Wissenschaftliche Berufslizenzen',
    'Primary Diving Services Licenses' => "{$primaryShortName} Lizenzen für Tauchdienstleistungen",

    // Middleware error messages
    'entity_has_inactive_license' => 'Ihre Einrichtung besitzt eine :committee Lizenz, diese ist jedoch derzeit nicht aktiv. Bitte stellen Sie sicher, dass Ihre :committee Lizenz aktiv ist, um auf diese Funktion zuzugreifen.',
    'entity_needs_active_license' => 'Ihre Einrichtung benötigt eine aktive :committee Lizenz, um auf diese Funktion zuzugreifen. Bitte wenden Sie sich an Ihren Verband, um die erforderliche Lizenz zu erhalten.',

    // License states
    'state_active' => 'Aktiv',
    'state_pending' => 'Ausstehend',
    'state_canceled' => 'Storniert',
    'state_provisional' => 'Vorläufig',
    'state_suspended' => 'Ausgesetzt',
    'state_waiting_approval' => 'Warten auf Genehmigung',
    'state_expired' => 'Abgelaufen',
    'state_pending_validation' => 'Validierung ausstehend',
    'state_pending_technical_director_approval' => 'Genehmigung durch technischen Leiter ausstehend',

    // Payment status
    'payment_status' => 'Zahlungsstatus',
    'payment_status_paid' => 'Bezahlt',
    'payment_status_pending_payment' => 'Zahlung ausstehend',
    'payment_status_no_document' => 'Kein Dokument',

    // Filter labels
    'filters' => [
        'first_name' => 'Vorname',
        'surname' => 'Nachname',
        'member_number' => 'Mitgliedsnummer',
        'sport' => 'Sportart',
        'entity_name' => 'Einrichtung',
    ],

    // Separated license purchase page titles and subtitles
    'Purchase Sport Club License' => 'Sportvereinslizenz erwerben',
    'Purchase a sport license for your club' => 'Erwerben Sie eine Sportlizenz für Ihren Verein',
    'Purchase Sport Licenses' => 'Sportlizenzen erwerben',
    'Select members and purchase sport licenses on their behalf' => 'Wählen Sie Mitglieder aus und erwerben Sie in ihrem Namen Sportlizenzen',
    'Purchase International Entity License' => "{$internationalName} Einrichtungslizenz erwerben",
    'Purchase an international license for your entity' => "Erwerben Sie eine {$internationalShortName} Lizenz für Ihre Einrichtung",
    'Purchase International Professional Licenses' => "{$internationalName} Berufslizenzen erwerben",
    'Select members and purchase international licenses on their behalf' => "Wählen Sie Mitglieder aus und erwerben Sie in ihrem Namen {$internationalShortName} Lizenzen",
    'Purchase Scientific Entity License' => 'Wissenschaftliche Einrichtungslizenz erwerben',
    'Purchase a scientific license for your entity' => 'Erwerben Sie eine wissenschaftliche Lizenz für Ihre Einrichtung',
    'Purchase Scientific Professional Licenses' => 'Wissenschaftliche Berufslizenzen erwerben',
    'Select members and purchase scientific licenses on their behalf' => 'Wählen Sie Mitglieder aus und erwerben Sie in ihrem Namen wissenschaftliche Lizenzen',
    'Purchase Primary Diving Services Licenses' => "{$primaryShortName} Lizenzen für Tauchdienstleistungen erwerben",
    'Select members and purchase primary diving licenses on their behalf' => "Wählen Sie Mitglieder aus und erwerben Sie in ihrem Namen {$primaryShortName} Tauchlizenzen",

    // Generic, committee-label-driven fallbacks (used when a committee declares
    // no purchase title/subtitle of its own in config/committees.php).
    'Purchase :committee Entity License' => ':committee Einrichtungslizenz erwerben',
    'Purchase a :committee license for your entity' => 'Erwerben Sie eine :committee Lizenz für Ihre Einrichtung',
    'Purchase :committee Licenses' => ':committee Lizenzen erwerben',
    'Select members and purchase :committee licenses on their behalf' => 'Wählen Sie Mitglieder aus und erwerben Sie in ihrem Namen :committee Lizenzen',
    ':committee Entity Licenses' => ':committee Einrichtungslizenzen',
    ':committee Professional Licenses' => ':committee Berufslizenzen',

    // Individual separated license purchase page titles
    'individual_sport_license_title' => 'Sport-Berufslizenzen',
    'individual_sport_license_subtitle' => 'Erwerben Sie Lizenzen für Schiedsrichter und Trainer',
    'individual_national_diving_license_title' => "{$primaryShortName} Tauchberufslizenz",
    'individual_national_diving_license_subtitle' => "{$primaryShortName} Tauchberufslizenz erwerben",
    'individual_cmas_diving_license_title' => "{$internationalShortName} Berufslizenz für Freizeittauchen",
    'individual_cmas_diving_license_subtitle' => "{$internationalShortName} Berufslizenz für Freizeittauchen erwerben",
    'individual_scientific_license_title' => "{$internationalShortName} Berufslizenz für wissenschaftliches Tauchen",
    'individual_scientific_license_subtitle' => "{$internationalShortName} Berufslizenz für wissenschaftliches Tauchen erwerben",

    // Individual separated licenses attributed page titles
    'individual_sport_licenses_title' => 'Sportlizenzen',
    'individual_sport_licenses_subtitle' => 'Ihre Sportlizenzen für Athleten, Trainer und technische Funktionäre',
    'individual_national_diving_licenses_title' => 'Berufliche Tauchlizenzen',
    'individual_national_diving_licenses_subtitle' => 'Ihre beruflichen Tauchlizenzen',
    'individual_national_diving_licenses_info' => 'Hier können Sie Ihre neuen beruflichen Tauchlizenzen einsehen und erwerben',
    'individual_cmas_diving_licenses_title' => "{$internationalName} Lizenzen",
    'individual_cmas_diving_licenses_subtitle' => '',
    'individual_cmas_diving_licenses_info' => "Hier können Sie alle Ihre jährlichen {$internationalName} beruflichen Tauchlizenzen einsehen",
    'individual_scientific_licenses_title' => "{$internationalName} Lizenzen",
    'individual_scientific_licenses_subtitle' => '',
    'individual_scientific_licenses_info' => "Hier können Sie alle Ihre jährlichen {$internationalName} beruflichen Tauchlizenzen einsehen",

    // Other individual license translations
    'individual_licenses_info' => 'Hier können Sie alle Ihre Lizenzen für Athleten, Trainer und technische Funktionäre einsehen',
    'sport' => 'Sportart',
    'category' => 'Kategorie',

    // Federation separated licenses attributed page titles
    'federation_sport_entity_licenses_title' => 'Sportvereinslizenzen',
    'federation_sport_entity_licenses_subtitle' => 'Vereinen zugewiesene Sportlizenzen',
    'federation_sport_individual_licenses_title' => 'Individuelle Sportlizenzen',
    'federation_sport_individual_licenses_subtitle' => 'Athleten und Trainern zugewiesene Sportlizenzen',
    'federation_national_diving_entity_licenses_title' => "{$primaryShortName} Lizenzen für Tauchzentren",
    'federation_national_diving_entity_licenses_subtitle' => "{$primaryShortName} Tauchlizenzen, die Tauchzentren zugewiesen wurden",
    'federation_national_diving_individual_licenses_title' => "{$primaryShortName} Tauchberufslizenzen",
    'federation_national_diving_individual_licenses_subtitle' => "{$primaryShortName} Tauchlizenzen, die Fachkräften zugewiesen wurden",
    'federation_cmas_diving_entity_licenses_title' => 'Internationale Lizenzen für Tauchzentren',
    'federation_cmas_diving_entity_licenses_subtitle' => 'Internationale Tauchlizenzen, die Tauchzentren zugewiesen wurden',
    'federation_cmas_diving_individual_licenses_title' => 'Internationale Tauchberufslizenzen',
    'federation_cmas_diving_individual_licenses_subtitle' => 'Internationale Tauchlizenzen, die Fachkräften zugewiesen wurden',
    'federation_scientific_entity_licenses_title' => 'Lizenzen für wissenschaftliche Tauchzentren',
    'federation_scientific_entity_licenses_subtitle' => 'Wissenschaftliche Tauchlizenzen, die Tauchzentren zugewiesen wurden',
    'federation_scientific_individual_licenses_title' => 'Berufslizenzen für wissenschaftliches Tauchen',
    'federation_scientific_individual_licenses_subtitle' => 'Wissenschaftliche Tauchlizenzen, die Fachkräften zugewiesen wurden',

    // Admin separated licenses attributed page titles
    'admin_sport_entity_licenses_title' => 'Sportvereinslizenzen',
    'admin_sport_entity_licenses_subtitle' => 'Alle Vereinen zugewiesenen Sportlizenzen',
    'admin_sport_individual_licenses_title' => 'Individuelle Sportlizenzen',
    'admin_sport_individual_licenses_subtitle' => 'Alle Athleten und Trainern zugewiesenen Sportlizenzen',
    'admin_national_diving_entity_licenses_title' => "{$primaryShortName} Lizenzen für Tauchzentren",
    'admin_national_diving_entity_licenses_subtitle' => "Alle {$primaryShortName} Tauchlizenzen, die Tauchzentren zugewiesen wurden",
    'admin_national_diving_individual_licenses_title' => "{$primaryShortName} Tauchberufslizenzen",
    'admin_national_diving_individual_licenses_subtitle' => "Alle {$primaryShortName} Tauchlizenzen, die Fachkräften zugewiesen wurden",
    'admin_cmas_diving_entity_licenses_title' => 'Internationale Einrichtungslizenzen',
    'admin_cmas_diving_entity_licenses_subtitle' => 'Alle internationalen Lizenzen, die Einrichtungen zugewiesen wurden',
    'admin_cmas_diving_individual_licenses_title' => 'Berufslizenzen für Freizeittauchen',
    'admin_cmas_diving_individual_licenses_subtitle' => 'Alle Lizenzen, die Fachkräften für Freizeittauchen zugewiesen wurden',
    'admin_scientific_entity_licenses_title' => 'Wissenschaftliche Einrichtungslizenzen',
    'admin_scientific_entity_licenses_subtitle' => 'Alle wissenschaftlichen Tauchlizenzen, die Einrichtungen zugewiesen wurden',
    'admin_scientific_individual_licenses_title' => 'Berufslizenzen für wissenschaftliches Tauchen',
    'admin_scientific_individual_licenses_subtitle' => 'Alle wissenschaftlichen Tauchlizenzen, die Fachkräften zugewiesen wurden',

    // Committee names (for translation)
    'Technical Committee' => 'Technischer Ausschuss',
    'Scientific Committee' => 'Wissenschaftlicher Ausschuss',

    // International license field
    'is_international_label' => "{$internationalName} Lizenz",
    'is_international_help' => "Wenn Sie diese Option aktivieren, ist diese Lizenz nur für {$internationalName} Tauchlehrer/Tauchgruppenleiter und Einrichtungen verfügbar.",

    // International licenses page
    'international_licenses' => 'Internationale Lizenzen',
    'cmas_international_licenses' => 'Internationale Lizenzen',
    'international_licenses_description' => 'Ihre weltweit anerkannten internationalen Lizenzen',
    'view_national_licenses' => 'Nationale Lizenzen anzeigen',
    'purchase_international_license' => 'Internationale Lizenz erwerben',
    'license' => 'Lizenz',
    'federation' => 'Verband',
    'sport_category' => 'Sportart/Kategorie',
    'validity' => 'Gültigkeit',
    'international_code' => 'Internationaler Code',
    'active' => 'Aktiv',
    'pending' => 'Ausstehend',
    'cancelled' => 'Storniert',
    'unknown' => 'Unbekannt',
    'view' => 'Anzeigen',
    'documents' => 'Dokumente',
    'no_international_licenses' => 'Keine internationalen Lizenzen',
    'no_international_licenses_message' => 'Sie haben noch keine internationalen Lizenzen erworben.',

    // License purchase success page
    'License Purchase Initiated!' => 'Lizenzkauf eingeleitet!',
    'Your license purchase is being processed. You will receive a confirmation once payment is complete.' => 'Ihr Lizenzkauf wird bearbeitet. Sie erhalten eine Bestätigung, sobald die Zahlung abgeschlossen ist.',
    'You can view and manage your license in the My Licenses section' => 'Sie können Ihre Lizenz im Bereich Meine Lizenzen einsehen und verwalten',
    'Payment Required' => 'Zahlung erforderlich',
    'Your license is pending payment to be activated' => 'Ihre Lizenz wartet auf die Zahlung, um aktiviert zu werden',
    'Please complete the payment to activate your license and download the certificate' => 'Bitte schließen Sie die Zahlung ab, um Ihre Lizenz zu aktivieren und das Zertifikat herunterzuladen',
    'An invoice has been generated and is available for download' => 'Eine Rechnung wurde erstellt und steht zum Download bereit',
    'Pending Payment' => 'Zahlung ausstehend',
    'Complete Payment' => 'Zahlung abschließen',
    'Payment integration coming soon' => 'Zahlungsintegration folgt in Kürze',

    // DIVINGSERVICES certification requirement
    'active_diving_certification_required' => 'Aktive Tauchzertifizierung erforderlich',
    'active_diving_certification_required_description' => 'Sie müssen über eine aktive Tauchfachkraft-Zertifizierung verfügen, um eine Tauchberufslizenz zu beantragen.',

    // License detail page actions
    'pending_payment_message' => 'Die Lizenz wartet auf die Zahlungsbestätigung. Sie wird automatisch aktiviert, sobald die Zahlung verarbeitet wurde.',
    'waiting_approval_message' => 'Die Lizenz wartet auf die Genehmigung.',
    'provisional_message' => 'Die Lizenz ist vorläufig und kann aktiviert werden.',
    'manually_activate' => 'Lizenz manuell aktivieren',
    'cancel_license' => 'Lizenz stornieren',
    'suspend_license' => 'Lizenz aussetzen',
    'reactivate_license' => 'Lizenz reaktivieren',
    'approve_license' => 'Lizenz genehmigen',
    'reject_license' => 'Lizenz ablehnen',
    'activate_provisional' => 'Vorläufige Lizenz aktivieren',
    'confirm_manual_activate' => 'Sind Sie sicher, dass Sie diese Lizenz manuell aktivieren möchten?',
    'confirm_cancel' => 'Sind Sie sicher, dass Sie diese Lizenz stornieren möchten?',
    'confirm_suspend' => 'Sind Sie sicher, dass Sie diese Lizenz aussetzen möchten?',
    'confirm_reactivate' => 'Sind Sie sicher, dass Sie diese Lizenz reaktivieren möchten?',
    'confirm_approve' => 'Sind Sie sicher, dass Sie diese Lizenz genehmigen möchten?',
    'confirm_reject' => 'Sind Sie sicher, dass Sie diese Lizenz ablehnen möchten?',
    'confirm_activate_provisional' => 'Sind Sie sicher, dass Sie diese vorläufige Lizenz aktivieren möchten?',
    'confirm_validate_approve' => 'Sind Sie sicher, dass Sie diese Lizenz validieren und genehmigen möchten?',
    'confirm_reject_validation' => 'Sind Sie sicher, dass Sie diese Lizenzvalidierung ablehnen möchten?',
];
