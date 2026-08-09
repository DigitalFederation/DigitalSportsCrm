<?php

return [
    'title' => 'Problèmes d\'intégration',
    'subtitle' => 'Vue consolidée des erreurs d\'intégration Moloni et Easypay',

    // Statistics
    'total_errors' => 'Total des erreurs',
    'errors_today' => 'Erreurs aujourd\'hui',
    'last_30_days' => '30 derniers jours',
    'last' => 'Dernier',

    // Error types
    'moloni_error_types' => 'Types d\'erreurs Moloni',
    'easypay_error_types' => 'Types d\'erreurs Easypay',

    // Filters
    'integration' => 'Intégration',
    'from_date' => 'Date de début',
    'to_date' => 'Date de fin',

    // Table
    'recent_errors' => 'Erreurs récentes',
    'showing_count' => 'Affichage de :count erreurs',
    'type' => 'Type',
    'error_message' => 'Message d\'erreur',
    'reference' => 'Référence',
    'date' => 'Date',
    'retry' => 'Réessayer',

    // Empty state
    'no_errors' => 'Aucune erreur d\'intégration',
    'no_errors_description' => 'Toutes les intégrations fonctionnent correctement sur la période sélectionnée.',

    // Navigation
    'moloni_settings' => 'Paramètres Moloni',
    'webhook_logs' => 'Journaux des webhooks',

    // Troubleshooting
    'troubleshooting_title' => 'Conseils de dépannage courants',
    'troubleshooting_moloni_auth' => 'Erreurs d\'authentification Moloni : vérifiez si la connexion Moloni est toujours active dans les paramètres Moloni.',
    'troubleshooting_moloni_config' => 'Erreurs de facturation Moloni : vérifiez que le jeu de documents, la taxe et les autres paramètres sont correctement configurés.',
    'troubleshooting_easypay_webhook' => 'Erreurs de webhook Easypay : vérifiez si la transaction existe et si le statut du paiement est correct.',
    'troubleshooting_easypay_transaction' => 'Erreurs de transaction Easypay : vérifiez le statut du document et la configuration du paiement.',
];
