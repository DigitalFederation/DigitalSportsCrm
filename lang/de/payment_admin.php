<?php

return [
    'currency_unsupported' => 'Unterstützt :currency nicht',
    'currency_unsupported_hint' => 'Dieses Gateway kann nicht in :currency abrechnen, der Währung der Installation. Es wird beim Bezahlen ausgeblendet.',
    // Page titles
    'payment_methods' => 'Zahlungsmethoden',
    'payment_transactions' => 'Zahlungstransaktionen',
    'webhook_logs' => 'Webhook-Protokolle',
    'edit_method' => 'Zahlungsmethode bearbeiten',
    'transaction_details' => 'Transaktionsdetails',
    'webhook_log_details' => 'Webhook-Protokolldetails',

    // Navigation
    'manage_payment_methods' => 'Zahlungsmethoden verwalten',
    'view_transactions' => 'Transaktionen anzeigen',
    'view_webhook_logs' => 'Webhook-Protokolle anzeigen',

    // Statistics
    'total_transactions' => 'Transaktionen insgesamt',
    'total_webhooks' => 'Webhooks insgesamt',
    'total_amount' => 'Gesamtbetrag',
    'pending' => 'Ausstehend',
    'successful' => 'Erfolgreich',
    'failed' => 'Fehlgeschlagen',
    'success_rate' => 'Erfolgsquote',
    'avg_processing_time' => 'Durchschn. Verarbeitungszeit',
    'status_breakdown' => 'Statusübersicht',
    'today' => 'Heute',

    // Table headers
    'id' => 'ID',
    'name' => 'Name',
    'driver' => 'Treiber',
    'handler' => 'Handler',
    'status' => 'Status',
    'instructions' => 'Anweisungen',
    'document' => 'Dokument',
    'payment_method' => 'Zahlungsmethode',
    'amount' => 'Betrag',
    'date' => 'Datum',
    'gateway' => 'Gateway',
    'request_id' => 'Anfrage-ID',
    'transaction' => 'Transaktion',
    'processing_time' => 'Verarbeitungszeit',

    // Form labels
    'instructions_help' => 'Anweisungen, die Benutzern bei der Auswahl dieser Zahlungsmethode angezeigt werden.',
    'enabled' => 'Aktiviert',
    'disabled' => 'Deaktiviert',
    'technical_info' => 'Technische Informationen',
    'note' => 'Hinweis',
    'easypay_config_note' => 'EasyPay-Zugangsdaten werden über Umgebungsvariablen konfiguriert. Kontaktieren Sie einen Entwickler, um API-Schlüssel zu aktualisieren.',

    // Gateway status
    'configured' => 'Konfiguriert',
    'not_configured' => 'Nicht konfiguriert',
    'mode' => 'Modus',
    'sandbox' => 'Sandbox',
    'production' => 'Produktion',
    'webhook_secret' => 'Webhook-Secret',
    'webhook_url' => 'Webhook-URL',
    'available_methods' => 'Verfügbare Methoden',

    // Actions
    'enable' => 'Aktivieren',
    'disable' => 'Deaktivieren',

    // Transaction details
    'transaction_info' => 'Transaktionsinformationen',
    'document_info' => 'Dokumentinformationen',
    'payment_data' => 'Zahlungsdaten',
    'comment' => 'Kommentar',
    'created_at' => 'Erstellt am',
    'updated_at' => 'Aktualisiert am',
    'document_number' => 'Dokumentnummer',
    'document_status' => 'Dokumentstatus',
    'document_total' => 'Dokumentsumme',
    'owner' => 'Inhaber',
    'view_document' => 'Dokument anzeigen',
    'no_document_associated' => 'Kein Dokument mit dieser Transaktion verknüpft.',

    // Webhook log details
    'request_info' => 'Anfrageinformationen',
    'related_records' => 'Zugehörige Datensätze',
    'ip_address' => 'IP-Adresse',
    'received_at' => 'Empfangen am',
    'request_headers' => 'Anfrage-Header',
    'webhook_payload' => 'Webhook-Payload',
    'response_sent' => 'Gesendete Antwort',
    'no_transaction' => 'Keine Transaktion verknüpft',
    'no_document' => 'Kein Dokument verknüpft',

    // Filter labels
    'from_date' => 'Von Datum',
    'to_date' => 'Bis Datum',

    // Empty states
    'no_transactions_found' => 'Keine Transaktionen gefunden.',
    'no_webhook_logs_found' => 'Keine Webhook-Protokolle gefunden.',
];
