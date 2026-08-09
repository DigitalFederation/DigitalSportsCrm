<?php

return [
    // Page title
    'title' => 'Moloni-Integration',

    // Connection status
    'connection_status' => 'Verbindungsstatus',
    'connected' => 'Verbunden',
    'not_connected' => 'Nicht verbunden',
    'token_expires' => 'Token läuft ab',
    'minutes_remaining' => 'Minuten verbleibend',

    // Buttons
    'authorize' => 'Mit Moloni autorisieren',
    'disconnect' => 'Verbindung trennen',
    'test_connection' => 'Verbindung testen',
    'sync_now' => 'Jetzt synchronisieren',
    'save' => 'Konfiguration speichern',

    // Sync
    'sync_data' => 'Daten von Moloni synchronisieren',
    'last_sync' => 'Letzte Synchronisierung',
    'no_sync_yet' => 'Es wurden noch keine Daten synchronisiert. Klicken Sie auf „Jetzt synchronisieren“, um Daten von Moloni abzurufen.',
    'sync_required' => 'Synchronisierung erforderlich',
    'sync_data_first' => 'Bitte synchronisieren Sie zuerst die Daten von Moloni, um die Konfigurationsoptionen zu füllen.',

    // Configuration
    'configuration' => 'Rechnungskonfiguration',
    'document_set' => 'Dokumentenserie',
    'default_tax' => 'Standardsteuer',
    'exempt_tax' => 'Befreite Steuer (0 % IVA)',
    'for_exempt_products' => 'für befreite Produkte',
    'exempt_tax_help' => 'Wählen Sie die 0 %-Steuer aus, die für mehrwertsteuerbefreite Produkte verwendet werden soll. Erforderlich, wenn Pläne einen Mehrwertsteuersatz von 0 % haben.',
    'no_exempt_tax_available' => 'Kein Steuersatz von 0 % in Moloni konfiguriert. Erstellen Sie eine 0 %-Steuer in Ihrem Moloni-Konto und synchronisieren Sie die Daten, um diese Option zu aktivieren.',
    'exemption_reason' => 'Befreiungsgrund',
    'required_for_exempt' => 'erforderlich für befreite Produkte',
    'exemption_reason_help' => 'Gesetzlicher Befreiungsgrund-Code (z. B. M07 für Artikel 9 CIVA). Von Moloni für Produkte ohne Mehrwertsteuer erforderlich.',
    'product_category' => 'Produktkategorie',
    'payment_method' => 'Zahlungsmethode',
    'unit' => 'Maßeinheit',
    'select_option' => 'Wählen Sie eine Option...',
    'optional' => 'optional',
    'category_help' => 'Nur erforderlich, wenn neue Produkte in Moloni erstellt werden. Nicht nötig, wenn Produkte bereits existieren (Zuordnung über Referenz).',
    'auto_detect' => 'Automatisch aus Zahlung erkennen',
    'payment_method_help' => 'Leer lassen, um automatisch anhand der Zahlungsmethode des Dokuments zu erkennen (Banküberweisung, Multibanco usw.).',
    'unit_help' => 'Nur erforderlich, wenn neue Produkte in Moloni erstellt werden. Nicht nötig, wenn Produkte bereits existieren (Zuordnung über Referenz).',

    // Status
    'status' => 'Integrationsstatus',
    'ready' => 'Bereit',
    'incomplete' => 'Konfiguration unvollständig',
    'invoices_will_be_generated' => 'Rechnungen werden für bezahlte Dokumente automatisch erstellt.',
    'complete_configuration' => 'Bitte vervollständigen Sie die Konfiguration, um die automatische Rechnungserstellung zu aktivieren.',

    // Logs
    'recent_logs' => 'Aktuelle Synchronisierungsprotokolle',
    'no_logs' => 'Keine Synchronisierungsprotokolle verfügbar.',
    'type' => 'Typ',
    'date' => 'Datum',
    'duration' => 'Dauer',
    'details' => 'Details',

    // Messages
    'connected_successfully' => 'Erfolgreich mit Moloni verbunden!',
    'disconnected_successfully' => 'Verbindung zu Moloni getrennt.',
    'connection_successful' => 'Verbindungstest erfolgreich!',
    'connection_test_failed' => 'Verbindungstest fehlgeschlagen. Bitte überprüfen Sie Ihre Zugangsdaten.',
    'connection_failed' => 'Verbindung fehlgeschlagen: :error',
    'sync_completed' => 'Daten erfolgreich synchronisiert. :count Einträge abgerufen.',
    'sync_failed' => 'Synchronisierung fehlgeschlagen: :error',
    'settings_saved' => 'Einstellungen erfolgreich gespeichert.',
    'authorization_denied' => 'Autorisierung verweigert: :error',
    'no_authorization_code' => 'Kein Autorisierungscode von Moloni erhalten.',
    'disconnect_confirm' => 'Sind Sie sicher, dass Sie die Verbindung zu Moloni trennen möchten? Dadurch werden die gespeicherten Tokens entfernt.',

    // Warnings
    'integration_disabled' => 'Integration deaktiviert',
    'enable_in_env' => 'Die Moloni-Integration ist derzeit deaktiviert. Setzen Sie MOLONI_ENABLED=true in Ihrer .env-Datei, um sie zu aktivieren.',
    'missing_credentials' => 'Fehlende Zugangsdaten',
    'add_credentials_to_env' => 'Bitte fügen Sie MOLONI_CLIENT_ID und MOLONI_CLIENT_SECRET zu Ihrer .env-Datei hinzu.',

    // New fields
    'company' => 'Unternehmen',
    'maturity_date' => 'Zahlungsbedingungen',
    'days' => 'Tage',

    // Invoices
    'recent_invoices' => 'Aktuelle Rechnungen',
    'no_invoices' => 'Noch keine Rechnungen erstellt.',
    'failed_invoices' => 'Fehlgeschlagene Rechnungen',
    'document' => 'Dokument',
    'moloni_number' => 'Moloni-Nummer',
    'moloni_status' => 'Status',
    'total' => 'Gesamt',
    'error' => 'Fehler',
    'actions' => 'Aktionen',
    'retry' => 'Erneut versuchen',

    // Manual operations
    'invoice_created' => 'Rechnung :number erfolgreich erstellt.',
    'invoice_not_created' => 'Die Rechnung konnte nicht erstellt werden (Moloni nicht konfiguriert oder Dokument nicht berechtigt).',
    'invoice_creation_failed' => 'Rechnungserstellung fehlgeschlagen: :error',
    'customer_synced' => 'Kunde erfolgreich synchronisiert. Moloni-ID: :id',
    'customer_sync_failed' => 'Kundensynchronisierung fehlgeschlagen: :error',

    // PDF and status
    'download_pdf' => 'PDF herunterladen',
    'refresh_status' => 'Aktualisieren',
    'pdf_not_available' => 'Für diese Rechnung ist kein PDF verfügbar.',
    'invoice_not_found' => 'Keine Moloni-Rechnung für dieses Dokument gefunden.',
    'pdf_download_failed' => 'PDF-Download fehlgeschlagen: :error',
    'status_refreshed' => 'Status der Rechnung :number erfolgreich aktualisiert.',
    'status_refresh_failed' => 'Statusaktualisierung fehlgeschlagen: :error',
    'view_in_moloni' => 'In Moloni anzeigen',

    // Customer management
    'synced_customers' => 'Synchronisierte Kunden',
    'no_customers' => 'Noch keine Kunden synchronisiert.',
    'customer_name' => 'Name',
    'customer_vat' => 'USt-IdNr.',
    'customer_type' => 'Typ',
    'moloni_id' => 'Moloni-ID',
    'individual' => 'Einzelperson',
    'entity' => 'Organisation',
    'sync_customer_button' => 'Kunde synchronisieren',

    // Bulk operations
    'retry_selected' => 'Ausgewählte erneut versuchen',
    'select_all' => 'Alle auswählen',
    'bulk_retry_success' => ':count Rechnungen erfolgreich erneut verarbeitet.',
    'bulk_retry_partial' => ':success Rechnungen erfolgreich, :failed Rechnungen fehlgeschlagen.',
    'bulk_retry_failed' => ':count Rechnungen konnten nicht erneut verarbeitet werden.',
    'no_invoices_selected' => 'Bitte wählen Sie mindestens eine Rechnung für einen erneuten Versuch aus.',

    // Product reference
    'product_reference' => 'Moloni-Referenz',
    'product_reference_help' => 'Eindeutiger Referenzcode zur Zuordnung dieses Plans zu einem Moloni-Produkt. Wenn gesetzt, wird dasselbe Produkt für alle Rechnungen wiederverwendet.',

    // Document series per type
    'document_series_by_type' => 'Dokumentenserien nach Typ',
    'document_series_by_type_description' => 'Konfigurieren Sie unterschiedliche Dokumentenserien für jeden Dokumenttyp. Leer lassen, um die obige Standardserie zu verwenden.',
    'owner_type_license' => 'Lizenzen',
    'owner_type_membership' => 'Organisationsbeiträge',
    'owner_type_member_subscription' => 'Individuelle Mitgliedschaften',
    'owner_type_certification' => 'Zertifizierungen',
    'owner_type_enrollment' => 'Organisationsanmeldungen (Veranstaltungen)',
    'owner_type_individual_enrollment' => 'Anmeldungen von Personal/Offiziellen',
    'owner_type_athlete_enrollment' => 'Athletenanmeldungen (Wettkämpfe)',
    'owner_type_insurance' => 'Versicherung',
    'use_default' => 'Standard verwenden',

    // Document type
    'document_type' => 'Dokumenttyp',
    'invoice_fatura' => 'Rechnung (Fatura)',
    'invoice_receipt_fatura_recibo' => 'Rechnungsquittung (Fatura-Recibo)',
    'document_type_help' => 'Die Rechnungsquittung kombiniert Rechnung und Zahlung. Erfordert, dass die Dokumentenserie in Moloni Fatura-Recibo aktiviert hat.',

    // Document status (draft vs finalized)
    'document_status' => 'Dokumentstatus',
    'status_finalized' => 'Abgeschlossen (Geschlossen)',
    'status_draft' => 'Entwurf (Rascunho)',
    'document_status_help' => 'Entwurfsrechnungen müssen in Moloni manuell abgeschlossen werden, bevor sie gültige Steuerdokumente werden. Verwenden Sie dies zur Überprüfung vor dem Abschluss.',

    // Missing invoices
    'missing_invoices' => 'Dokumente ohne Rechnung',
    'documents' => 'Dokumente',
    'create_invoices' => 'Rechnungen erstellen',
    'create_invoice' => 'Rechnung erstellen',
    'owner' => 'Inhaber',
    'paid_date' => 'Zahlungsdatum',
    'no_owner' => 'Kein Inhaber',
    'showing_first_50' => 'Es werden die ersten 50 von :count Dokumenten angezeigt. Die übrigen werden angezeigt, nachdem diese verarbeitet wurden.',
    'no_missing_invoices' => 'Für alle bezahlten Dokumente wurden Moloni-Rechnungen erstellt.',

    // Failure notification
    'notification_invoice_failed_subject' => 'Moloni-Rechnungserstellung fehlgeschlagen',
    'notification_invoice_failed_greeting' => 'Warnung zur Rechnungserstellung',
    'notification_invoice_failed_intro' => 'Das System konnte nach mehreren Versuchen keine Moloni-Rechnung für das Dokument :document erstellen.',
    'notification_invoice_failed_error' => 'Fehler: :error',
    'notification_invoice_failed_attempts' => 'Das System hat es :attempts Mal versucht, bevor es aufgegeben hat.',
    'notification_invoice_failed_action' => 'Moloni-Einstellungen anzeigen',
    'notification_invoice_failed_document_link' => 'Sie können das Dokument hier einsehen: :url',
    'notification_invoice_failed_database' => 'Moloni-Rechnung für Dokument :document konnte nicht erstellt werden',

    // Invoice generation rules
    'invoice_generation_rules' => 'Regeln für die Rechnungserstellung',
    'invoice_generation_rules_description' => 'Wählen Sie aus, welche Dokumentdetailtypen die Erstellung von Moloni-Rechnungen auslösen sollen. Nicht angehakte Typen überspringen die Rechnungserstellung.',
    'invoice_generation_rules_saved' => 'Regeln für die Rechnungserstellung erfolgreich gespeichert.',
    'save_invoice_rules' => 'Rechnungsregeln speichern',
    'require_all_details_enabled' => 'Alle Detailtypen müssen aktiviert sein',
    'require_all_details_enabled_help' => 'Wenn aktiviert, werden Rechnungen nur erstellt, wenn ALLE Detailtypen im Dokument aktiviert sind. Wenn deaktiviert, werden Rechnungen erstellt, sobald IRGENDEIN aktivierter Typ vorhanden ist.',

    // Committee-based document series
    'committee_document_series' => 'Komitee-basierte Dokumentenserien',
    'committee_document_series_description' => 'Wählen Sie die Dokumentenserie für Lizenzen und Zertifizierungen basierend auf ihrem Komitee. Dies hat Vorrang vor der typbasierten Zuordnung unten.',
    'committee_diving' => 'Tauchkomitee',
    'committee_scientific' => 'Wissenschaftliches Komitee',
    'committee_sport' => 'Sportkomitee',
    'committee_divingservices' => 'Komitee für Tauchdienstleistungen',

    // Warnings and validation
    'warning' => 'Warnung',
    'document_set_not_in_cache' => 'Die konfigurierte Dokumentenserie (ID: :id) ist nicht in den synchronisierten Daten enthalten.',
    'sync_to_refresh' => 'Klicken Sie auf „Daten synchronisieren“, um die verfügbaren Dokumentenserien von Moloni zu aktualisieren.',
    'not_in_cache' => 'Nicht in den synchronisierten Daten',
    'no_at_codes' => 'Keine AT-Codes – ungültig für Rechnungen',

    // Activity log
    'activity_log_description' => 'Aktuelle Rechnungs- und Synchronisierungsaktivitäten',
    'invoice_created_title' => 'Rechnung erstellt',
    'invoice_failed_title' => 'Rechnung fehlgeschlagen',
    'sync_completed_title' => 'Datensynchronisierung abgeschlossen',
    'sync_failed_title' => 'Datensynchronisierung fehlgeschlagen',
    'success' => 'Erfolg',
    'failed' => 'Fehlgeschlagen',
    'view_document' => 'Dokument anzeigen',
    'companies_synced' => 'Unternehmen',
    'series_synced' => 'Serien',
    'taxes_synced' => 'Steuern',
    'categories_synced' => 'Kategorien',
];
