<?php

return [
    // Page titles
    'payment_methods' => 'Moyens de paiement',
    'payment_transactions' => 'Transactions de paiement',
    'webhook_logs' => 'Journaux des webhooks',
    'edit_method' => 'Modifier le moyen de paiement',
    'transaction_details' => 'Détails de la transaction',
    'webhook_log_details' => 'Détails du journal de webhook',

    // Navigation
    'manage_payment_methods' => 'Gérer les moyens de paiement',
    'view_transactions' => 'Voir les transactions',
    'view_webhook_logs' => 'Voir les journaux des webhooks',

    // Statistics
    'total_transactions' => 'Total des transactions',
    'total_webhooks' => 'Total des webhooks',
    'total_amount' => 'Montant total',
    'pending' => 'En attente',
    'successful' => 'Réussies',
    'failed' => 'Échouées',
    'success_rate' => 'Taux de réussite',
    'avg_processing_time' => 'Temps de traitement moyen',
    'status_breakdown' => 'Répartition par statut',
    'today' => 'Aujourd\'hui',

    // Table headers
    'id' => 'ID',
    'name' => 'Nom',
    'driver' => 'Pilote',
    'handler' => 'Gestionnaire',
    'status' => 'Statut',
    'instructions' => 'Instructions',
    'document' => 'Document',
    'payment_method' => 'Moyen de paiement',
    'amount' => 'Montant',
    'date' => 'Date',
    'gateway' => 'Passerelle',
    'request_id' => 'ID de requête',
    'transaction' => 'Transaction',
    'processing_time' => 'Temps de traitement',

    // Form labels
    'instructions_help' => 'Instructions affichées aux utilisateurs lors de la sélection de ce moyen de paiement.',
    'enabled' => 'Activé',
    'disabled' => 'Désactivé',
    'technical_info' => 'Informations techniques',
    'note' => 'Remarque',
    'easypay_config_note' => 'Les identifiants EasyPay sont configurés via des variables d\'environnement. Contactez un développeur pour mettre à jour les clés API.',

    // Gateway status
    'configured' => 'Configurée',
    'not_configured' => 'Non configurée',
    'mode' => 'Mode',
    'sandbox' => 'Sandbox',
    'production' => 'Production',
    'webhook_secret' => 'Secret du webhook',
    'webhook_url' => 'URL du webhook',
    'available_methods' => 'Moyens disponibles',

    // Actions
    'enable' => 'Activer',
    'disable' => 'Désactiver',

    // Transaction details
    'transaction_info' => 'Informations sur la transaction',
    'document_info' => 'Informations sur le document',
    'payment_data' => 'Données de paiement',
    'comment' => 'Commentaire',
    'created_at' => 'Créé le',
    'updated_at' => 'Mis à jour le',
    'document_number' => 'Numéro du document',
    'document_status' => 'Statut du document',
    'document_total' => 'Total du document',
    'owner' => 'Propriétaire',
    'view_document' => 'Voir le document',
    'no_document_associated' => 'Aucun document associé à cette transaction.',

    // Webhook log details
    'request_info' => 'Informations sur la requête',
    'related_records' => 'Enregistrements associés',
    'ip_address' => 'Adresse IP',
    'received_at' => 'Reçu le',
    'request_headers' => 'En-têtes de la requête',
    'webhook_payload' => 'Contenu du webhook',
    'response_sent' => 'Réponse envoyée',
    'no_transaction' => 'Aucune transaction liée',
    'no_document' => 'Aucun document lié',

    // Filter labels
    'from_date' => 'Date de début',
    'to_date' => 'Date de fin',

    // Empty states
    'no_transactions_found' => 'Aucune transaction trouvée.',
    'no_webhook_logs_found' => 'Aucun journal de webhook trouvé.',
];
