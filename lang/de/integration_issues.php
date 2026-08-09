<?php

return [
    'title' => 'Integrationsprobleme',
    'subtitle' => 'Konsolidierte Ansicht der Integrationsfehler von Moloni und Easypay',

    // Statistics
    'total_errors' => 'Fehler gesamt',
    'errors_today' => 'Fehler heute',
    'last_30_days' => 'Letzte 30 Tage',
    'last' => 'Letzte',

    // Error types
    'moloni_error_types' => 'Moloni-Fehlertypen',
    'easypay_error_types' => 'Easypay-Fehlertypen',

    // Filters
    'integration' => 'Integration',
    'from_date' => 'Von Datum',
    'to_date' => 'Bis Datum',

    // Table
    'recent_errors' => 'Aktuelle Fehler',
    'showing_count' => ':count Fehler werden angezeigt',
    'type' => 'Typ',
    'error_message' => 'Fehlermeldung',
    'reference' => 'Referenz',
    'date' => 'Datum',
    'retry' => 'Wiederholen',

    // Empty state
    'no_errors' => 'Keine Integrationsfehler',
    'no_errors_description' => 'Alle Integrationen funktionieren im ausgewählten Zeitraum korrekt.',

    // Navigation
    'moloni_settings' => 'Moloni-Einstellungen',
    'webhook_logs' => 'Webhook-Protokolle',

    // Troubleshooting
    'troubleshooting_title' => 'Gängige Tipps zur Fehlerbehebung',
    'troubleshooting_moloni_auth' => 'Moloni-Authentifizierungsfehler: Prüfen Sie, ob die Moloni-Verbindung in den Moloni-Einstellungen noch aktiv ist.',
    'troubleshooting_moloni_config' => 'Moloni-Rechnungsfehler: Überprüfen Sie, ob der Belegsatz, die Steuer und andere Einstellungen ordnungsgemäß konfiguriert sind.',
    'troubleshooting_easypay_webhook' => 'Easypay-Webhook-Fehler: Prüfen Sie, ob die Transaktion existiert und der Zahlungsstatus korrekt ist.',
    'troubleshooting_easypay_transaction' => 'Easypay-Transaktionsfehler: Überprüfen Sie den Belegstatus und die Zahlungskonfiguration.',
];
