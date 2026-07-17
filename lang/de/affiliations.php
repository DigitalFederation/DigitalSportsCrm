<?php

return [
    // Page titles and headers
    'title' => 'Zugehörigkeiten',
    'info_title' => 'Verwaltung der Zugehörigkeiten',
    'info_body' => 'Zeigen und verwalten Sie alle Zugehörigkeiten im System. Überwachen Sie Mitgliedszugehörigkeiten, deren Status und zugehörige Verbände.',

    // Filter labels
    'member_type' => 'Mitgliedstyp',
    'status' => 'Status',
    'federation' => 'Verband',
    'member_name' => 'Name des Mitglieds',
    'start_date' => 'Startdatum',
    'end_date' => 'Enddatum',

    // Table headers
    'table' => [
        'member' => 'Mitglied',
        'type' => 'Typ',
        'federation' => 'Verband',
        'start_date' => 'Startdatum',
        'end_date' => 'Enddatum',
        'fee' => 'Gebühr',
        'status' => 'Status',
    ],

    // Status labels
    'statuses' => [
        'active' => 'Aktiv',
        'inactive' => 'Inaktiv',
        'suspended' => 'Ausgesetzt',
        'expired' => 'Abgelaufen',
        'pending_payment' => 'Zahlung ausstehend',
    ],

    // Actions
    'view_member' => 'Mitglied anzeigen',
    'delete' => 'Löschen',
    'member' => 'Mitglied',

    // Data placeholders
    'member_not_found' => 'Mitglied nicht gefunden',
    'no_federation' => 'Kein Verband',
    'no_date' => 'Kein Datum',
    'no_fee' => 'Keine Gebühr',
    'via_entity' => 'über Organisation',

    // Messages
    'no_affiliations_found' => 'Keine Zugehörigkeiten gefunden, die Ihren Kriterien entsprechen.',
    'affiliations_empty' => 'Es wurden noch keine Zugehörigkeiten erstellt.',
    'status_updated_successfully' => 'Zugehörigkeitsstatus erfolgreich aktualisiert.',
    'status_update_failed' => 'Aktualisierung des Zugehörigkeitsstatus fehlgeschlagen. Bitte versuchen Sie es erneut.',
    'deleted_successfully' => 'Zugehörigkeit erfolgreich gelöscht.',
    'delete_failed' => 'Löschen der Zugehörigkeit fehlgeschlagen. Bitte versuchen Sie es erneut.',

    // Delete confirmation
    'confirm_delete_title' => 'Zugehörigkeit löschen',
    'confirm_delete_message' => 'Sind Sie sicher, dass Sie diese Zugehörigkeit löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.',
    'delete_confirm' => 'Zugehörigkeit löschen',

    // Status change confirmation
    'confirm_status_change' => 'Sind Sie sicher, dass Sie den Status dieser Zugehörigkeit ändern möchten?',

    // Individual profile table
    'active_affiliations' => 'Aktive Zugehörigkeiten',
    'affiliation_count' => '{0} Keine Zugehörigkeiten|{1} :count Zugehörigkeit|[2,*] :count Zugehörigkeiten',
    'no_active_affiliations' => 'Keine aktiven Zugehörigkeiten',
    'plan' => 'Plan',
    'period' => 'Zeitraum',
    'privileges' => 'Berechtigungen',
    'standard_plan' => 'Standardplan',
    'until' => 'bis',
    'active' => 'Aktiv',
    'expired' => 'Abgelaufen',
    'validation_plan' => 'Validierungsplan',
    'insurance_requests' => 'Versicherungsanfragen',
    'license_requests' => 'Lizenzanfragen',
    'standard' => 'Standard',
];
