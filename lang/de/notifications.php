<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'entity_created' => [
        'subject' => 'Willkommen bei :app',
        'greeting' => 'Guten Tag, :name!',
        'line1' => 'Für Ihre Einrichtung wurde ein Konto erstellt.',
        'line2' => 'Um Ihr Einrichtungsprofil zu verwalten und die Funktionen unserer Plattform zu erkunden, legen Sie bitte Ihr Passwort fest.',
        'action' => 'Passwort festlegen',
        'line3' => 'Sobald Ihr Passwort festgelegt ist, haben Sie vollständigen Zugang zu Ihrem Dashboard.',
        'line4' => 'Wir freuen uns auf Ihre aktive Teilnahme.',
        'line5' => 'Vielen Dank, dass Sie Teil von :app sind. Bei Fragen können Sie sich gerne an uns wenden.',
        'salutation' => 'Mit freundlichen Grüßen, das Team von :app',
    ],

    'welcome_email' => [
        'title' => 'Willkommens-E-Mail',
        'user_email' => 'E-Mail des Nutzers',
        'sent_status' => 'Gesendet',
        'not_sent_status' => 'Nicht gesendet',
        'send_button' => 'Willkommens-E-Mail senden',
        'resend_button' => 'Willkommens-E-Mail erneut senden',
        'confirm_send' => 'Sind Sie sicher, dass Sie die Willkommens-E-Mail senden möchten?',
        'description' => 'Diese E-Mail enthält einen Link, mit dem der Nutzer sein Passwort festlegen und sein Konto aktivieren kann.',
        'sent' => 'Willkommens-E-Mail erfolgreich gesendet.',
        'failed' => 'Senden der Willkommens-E-Mail fehlgeschlagen.',
        'no_user' => 'Kein Nutzerkonto zugeordnet.',
    ],

    // Payment notifications
    'payment_made' => 'Eine Zahlung in Höhe von :value wurde geleistet.',

    // Event notifications
    'event_enrollment_confirmed' => 'Ihre Anmeldung zur Veranstaltung wurde bestätigt.',
    'event_registration_confirmed' => 'Ihre Registrierung für die Veranstaltung wurde bestätigt.',

    // Request notifications
    'request_approved' => 'Ihr Antrag auf Beitritt zu :federation wurde genehmigt.',
    'federation_request_approved' => "Ihr Antrag auf Beitritt zu {$portalName} wurde genehmigt.",
    'association_request_accepted' => 'Verbandsanfrage erfolgreich angenommen.',
    'error_accepting_request' => 'Fehler beim Annehmen der Anfrage.',
    'request_join_accepted' => 'Der Beitrittsantrag von :name wurde angenommen.',
    'request_rejected' => 'Antrag der Einzelperson erfolgreich abgelehnt.',
    'error_rejecting_request' => 'Ablehnung des Antrags der Einzelperson fehlgeschlagen.',
    'request_deleted' => 'Antrag der Einzelperson erfolgreich gelöscht.',

    // Document notifications
    'document_created' => [
        'subject' => 'Benachrichtigung über Dokumenterstellung',
        'greeting' => 'Benachrichtigung',
        'line' => "Das Dokument :invoice ist auf {$portalName} verfügbar. Klicken Sie auf die Schaltfläche unten, um auf {$portalName} zuzugreifen, wo Sie den Dokumentstatus im Menü Zahlungen einsehen können.",
        'action' => 'Dokument öffnen',
    ],

    'admin_license_attributed' => [
        'subject' => 'Neue Lizenz angefordert',
        'greeting' => 'Benachrichtigung',
        'line_intro' => 'Eine neue Lizenz wurde angefordert.',
        'line_license' => '**Name der Lizenz:** :name',
        'line_holder' => '**Name des Inhabers:** :holder',
        'line_federation' => '**Name des Verbands:** :federation',
        'action' => 'Details anzeigen',
    ],

    'membership_create' => [
        'intro' => 'Eine neue Mitgliedschaft wurde zugewiesen. Sie wird nach Bestätigung der Zahlung aktiv.',
        'action' => 'Mitgliedschaft öffnen',
        'outro' => 'Vielen Dank, dass Sie unsere Anwendung nutzen!',
        'database' => 'Eine neue Mitgliedschaft wurde zugewiesen. Sie wird nach Bestätigung der Zahlung aktiv.',
    ],

    'entity_approval' => [
        'subject' => 'Genehmigung der Einrichtung erforderlich',
        'greeting' => 'Hallo :name,',
        'line_intro' => 'Es gibt eine neue Einrichtung, die auf Ihre Genehmigung wartet.',
        'line_entity' => 'Name der Einrichtung: :entity',
        'action' => 'Einrichtung anzeigen',
        'line_review' => 'Bitte überprüfen Sie die Details der Einrichtung und fahren Sie mit dem Genehmigungsverfahren fort.',
        'salutation_regards' => 'Mit freundlichen Grüßen,',
        'salutation_team' => 'Team von :app',
        'database' => 'Eine neue Einrichtung erfordert Ihre Genehmigung.',
    ],

    'entity_member_accepted' => [
        'subject' => 'Neues Mitglied angenommen: :name',
        'greeting' => 'Hallo!',
        'line_accepted' => ':name hat die Einladung angenommen, Mitglied von :entity zu werden.',
        'line_active' => 'Dieses Mitglied ist nun in Ihrer Einrichtung aktiv.',
        'action' => 'Mitglieder anzeigen',
        'salutation' => 'Mit freundlichen Grüßen,<br>das Team von :app',
        'database' => ':name hat die Einladung angenommen, Mitglied zu werden.',
    ],

    'entity_member_invitation' => [
        'subject' => 'Einladung, Mitglied von :entity zu werden',
        'greeting' => 'Hallo!',
        'line_invited' => ':inviter hat Sie eingeladen, Mitglied seiner Einrichtung zu werden.',
        'line_instructions' => 'Um diese Einladung anzunehmen, melden Sie sich an der Plattform an und navigieren Sie im Seitenmenü zu \'Einrichtungen\'.',
        'action' => 'Einladung anzeigen',
        'line_ignore' => 'Falls Sie diese Einladung nicht erwartet haben, können Sie diese E-Mail ignorieren.',
        'salutation' => 'Mit freundlichen Grüßen,<br>das Team von :app',
        'database' => 'Die Einrichtung :entity hat Sie eingeladen, Mitglied zu werden.',
    ],

    'entity_request' => [
        'database_title' => 'Neue Einrichtungsanfrage',
        'database_message' => 'Sie haben eine neue Beitrittsanfrage von :name.',
    ],

    'export_ready' => [
        'line_intro' => 'Ihr Export steht zum Herunterladen bereit. Den Link finden Sie in der E-Mail.',
        'action' => 'Export herunterladen',
        'database' => 'Ihr Export steht zum Herunterladen bereit.',
    ],

    'federation_join_request' => [
        'database' => ':name hat den Beitritt zum Verband beantragt.',
    ],

    'individual_request_license' => [
        'line' => 'Es gibt eine neue :type-Lizenz zu genehmigen.',
        'database' => 'Es gibt eine neue :type-Lizenz zu genehmigen.',
    ],

    'instructor_new_certification' => [
        'line' => 'Es gibt eine neue Zertifizierung zu genehmigen.',
        'action' => 'Öffnen',
        'database' => 'Es gibt eine neue Zertifizierung zu genehmigen.',
    ],

    'invite_individual_professional' => [
        'subject' => 'Einladung, :role zu werden',
        'greeting' => 'Hallo :name!',
        'line_invited' => 'Sie wurden eingeladen, :role von :entity zu werden.',
        'action' => 'Einladung ansehen',
        'line_thanks' => 'Vielen Dank, dass Sie unsere Einladung in Betracht ziehen!',
        'salutation' => 'Mit freundlichen Grüßen, :app',
        'database' => 'Sie wurden eingeladen, :role von :entity zu werden.',
    ],

    'membership_activation' => [
        'line_activated' => 'Die Mitgliedschaft :name wurde erfolgreich aktiviert.',
        'action' => 'Mitgliedschaft öffnen',
        'salutation' => $primaryShortName,
        'database' => 'Die Mitgliedschaft :name wurde erfolgreich aktiviert.',
    ],

    'membership_expiration' => [
        'line_expires' => 'Ihre Mitgliedschaft :name läuft am :date ab.',
        'action' => 'Mitgliedschaft öffnen',
        'outro' => 'Vielen Dank, dass Sie unsere Anwendung nutzen!',
    ],

    'official_document_activated' => [
        'database' => 'Das Dokument :name wurde genehmigt.',
    ],

    'official_document_created' => [
        'database' => 'Das offizielle Dokument :name wurde gesendet.',
    ],

    'official_document_deleted' => [
        'database' => 'Das Dokument :name wurde gelöscht.',
    ],

    'report_generated' => [
        'line_ready' => 'Ihr Bericht ist fertig.',
        'action' => 'Bericht herunterladen',
        'line_auth' => 'Sie müssen authentifiziert sein, um den Bericht herunterzuladen.',
        'database' => 'Ihr Bericht steht zum Herunterladen bereit. Klicken Sie hier, um ihn herunterzuladen.',
    ],
];
