<?php

return [
    // Validation messages
    'package_required' => 'Es muss ein Mitgliedschaftspaket ausgewählt werden',
    'invalid_package' => 'Das ausgewählte Mitgliedschaftspaket ist ungültig',
    'individuals_required' => 'Es muss mindestens eine Person ausgewählt werden',
    'min_one_individual' => 'Wählen Sie mindestens eine Person aus',

    // Success messages
    'subscriptions_created' => 'Abonnements erstellt',
    'success_count' => ':count Abonnements wurden erfolgreich erstellt',
    'payment_required_count' => ':count erfordern Zahlungsdokumente',
    'free_subscriptions_count' => ':count sind kostenlos und aktiv',

    // Error messages
    'some_subscriptions_failed' => 'Einige Abonnements sind fehlgeschlagen',
    'failed_count' => ':count Abonnements konnten nicht erstellt werden. Bitte prüfen Sie die Protokolle für Details',
    'error' => 'Fehler',
    'unexpected_error' => 'Bei der Verarbeitung der Abonnements ist ein unerwarteter Fehler aufgetreten',
    'unauthorized_action' => 'Nicht autorisierte Aktion',

    // Action buttons
    'retry_failed' => 'Fehlgeschlagene wiederholen',
    'retry_failed_title' => 'Fehlgeschlagene Abonnements wiederholen',
    'retry_failed_description' => 'Möchten Sie versuchen, die fehlgeschlagenen Abonnements erneut zu erstellen?',
    'yes_retry' => 'Ja, wiederholen',
    'no_cancel' => 'Nein, abbrechen',
    'try_again' => 'Erneut versuchen',
    // Headers and titles
    'select_package' => 'Wählen Sie einen der Pläne aus, der den ausgewählten Mitgliedern Ihrer Organisation zugewiesen werden soll.',
    'select_insurance_package' => 'Wählen Sie einen der Versicherungspläne aus, der den ausgewählten Mitgliedern Ihrer Organisation zugewiesen werden soll.',
    'select_members' => 'Mitglieder auswählen',
    'entity_member_memberships_title' => 'Mitgliedschaftspläne für Organisationsmitglieder',
    'entity_member_insurances_title' => 'Versicherungspläne für Organisationsmitglieder',
    'selected' => 'Ausgewählt',

    // Search and filters
    'search_placeholder' => 'Mitglieder nach Name oder ID suchen...',
    'filter' => [
        'all_status' => 'Alle Status',
        'active_subscription' => 'Aktives Abonnement',
        'no_subscription' => 'Kein Abonnement',
    ],

    // Table headers
    'table' => [
        'name' => 'Name',
        'id' => 'ID',
        'status' => 'Status',
    ],

    // Status labels
    'status' => [
        'active' => 'Aktiv',
        'no_subscription' => 'Kein Abonnement',
    ],

    // Messages
    'no_members_found' => 'Keine Mitglieder gefunden, die Ihren Kriterien entsprechen.',

    // Selection tray
    'selected_members' => 'Ausgewählte Mitglieder',
    'click_to_view' => 'zum Anzeigen klicken',
    'clear_all' => 'Alle löschen',
    'remove_selection' => 'Aus der Auswahl entfernen',
    'total_selected' => ':count Mitglied(er) ausgewählt',
    'estimated_total' => 'Geschätzte Gesamtsumme',

    // Actions
    'actions' => [
        'cancel' => 'Abbrechen',
        'subscribe_selected' => 'Ausgewählte Mitglieder anmelden (:count)',
        'confirm' => 'Bestätigen',
    ],

    // Modal
    'modal' => [
        'confirm_title' => 'Abonnement bestätigen',
        'confirm_message' => 'Sie sind dabei, die ausgewählten Mitglieder für das folgende Paket anzumelden:',
        'price' => 'Preis',
        'subscription_count' => 'Diese Aktion erstellt neue Abonnements für :count Mitglieder.',
    ],
];
