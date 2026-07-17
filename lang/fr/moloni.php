<?php

return [
    // Page title
    'title' => 'Intégration Moloni',

    // Connection status
    'connection_status' => 'État de la connexion',
    'connected' => 'Connecté',
    'not_connected' => 'Non connecté',
    'token_expires' => 'Le jeton expire',
    'minutes_remaining' => 'minutes restantes',

    // Buttons
    'authorize' => 'Autoriser avec Moloni',
    'disconnect' => 'Déconnecter',
    'test_connection' => 'Tester la connexion',
    'sync_now' => 'Synchroniser maintenant',
    'save' => 'Enregistrer la configuration',

    // Sync
    'sync_data' => 'Synchroniser les données depuis Moloni',
    'last_sync' => 'Dernière synchronisation',
    'no_sync_yet' => 'Aucune donnée n\'a encore été synchronisée. Cliquez sur « Synchroniser maintenant » pour récupérer les données depuis Moloni.',
    'sync_required' => 'Synchronisation requise',
    'sync_data_first' => 'Veuillez d\'abord synchroniser les données depuis Moloni pour remplir les options de configuration.',

    // Configuration
    'configuration' => 'Configuration des factures',
    'document_set' => 'Série de documents',
    'default_tax' => 'Taxe par défaut',
    'exempt_tax' => 'Taxe exonérée (0 % IVA)',
    'for_exempt_products' => 'pour les produits exonérés',
    'exempt_tax_help' => 'Sélectionnez la taxe à 0 % à utiliser pour les produits exonérés de TVA. Requis lorsque des plans ont un taux de TVA de 0 %.',
    'no_exempt_tax_available' => 'Aucun taux de taxe à 0 % configuré dans Moloni. Créez une taxe à 0 % dans votre compte Moloni et synchronisez les données pour activer cette option.',
    'exemption_reason' => 'Motif d\'exonération',
    'required_for_exempt' => 'requis pour les produits exonérés',
    'exemption_reason_help' => 'Code de motif légal d\'exonération (par ex. M07 pour l\'article 9 du CIVA). Requis par Moloni pour les produits sans TVA.',
    'product_category' => 'Catégorie de produit',
    'payment_method' => 'Méthode de paiement',
    'unit' => 'Unité de mesure',
    'select_option' => 'Sélectionnez une option...',
    'optional' => 'facultatif',
    'category_help' => 'Requis uniquement lors de la création de nouveaux produits dans Moloni. Inutile si les produits existent déjà (correspondance par référence).',
    'auto_detect' => 'Détection automatique depuis le paiement',
    'payment_method_help' => 'Laissez vide pour une détection automatique basée sur la méthode de paiement du document (virement bancaire, Multibanco, etc.).',
    'unit_help' => 'Requis uniquement lors de la création de nouveaux produits dans Moloni. Inutile si les produits existent déjà (correspondance par référence).',

    // Status
    'status' => 'État de l\'intégration',
    'ready' => 'Prêt',
    'incomplete' => 'Configuration incomplète',
    'invoices_will_be_generated' => 'Les factures seront générées automatiquement pour les documents payés.',
    'complete_configuration' => 'Veuillez compléter la configuration pour activer la génération automatique des factures.',

    // Logs
    'recent_logs' => 'Journaux de synchronisation récents',
    'no_logs' => 'Aucun journal de synchronisation disponible.',
    'type' => 'Type',
    'date' => 'Date',
    'duration' => 'Durée',
    'details' => 'Détails',

    // Messages
    'connected_successfully' => 'Connexion à Moloni réussie !',
    'disconnected_successfully' => 'Déconnecté de Moloni.',
    'connection_successful' => 'Test de connexion réussi !',
    'connection_test_failed' => 'Le test de connexion a échoué. Veuillez vérifier vos identifiants.',
    'connection_failed' => 'Échec de la connexion : :error',
    'sync_completed' => 'Données synchronisées avec succès. :count éléments récupérés.',
    'sync_failed' => 'Échec de la synchronisation : :error',
    'settings_saved' => 'Paramètres enregistrés avec succès.',
    'authorization_denied' => 'Autorisation refusée : :error',
    'no_authorization_code' => 'Aucun code d\'autorisation reçu de Moloni.',
    'disconnect_confirm' => 'Êtes-vous sûr de vouloir vous déconnecter de Moloni ? Cette action supprimera les jetons enregistrés.',

    // Warnings
    'integration_disabled' => 'Intégration désactivée',
    'enable_in_env' => 'L\'intégration Moloni est actuellement désactivée. Définissez MOLONI_ENABLED=true dans votre fichier .env pour l\'activer.',
    'missing_credentials' => 'Identifiants manquants',
    'add_credentials_to_env' => 'Veuillez ajouter MOLONI_CLIENT_ID et MOLONI_CLIENT_SECRET à votre fichier .env.',

    // New fields
    'company' => 'Entreprise',
    'maturity_date' => 'Conditions de paiement',
    'days' => 'jours',

    // Invoices
    'recent_invoices' => 'Factures récentes',
    'no_invoices' => 'Aucune facture générée pour le moment.',
    'failed_invoices' => 'Factures en échec',
    'document' => 'Document',
    'moloni_number' => 'Numéro Moloni',
    'moloni_status' => 'Statut',
    'total' => 'Total',
    'error' => 'Erreur',
    'actions' => 'Actions',
    'retry' => 'Réessayer',

    // Manual operations
    'invoice_created' => 'Facture :number créée avec succès.',
    'invoice_not_created' => 'La facture n\'a pas pu être créée (Moloni non configuré ou document non éligible).',
    'invoice_creation_failed' => 'Échec de la création de la facture : :error',
    'customer_synced' => 'Client synchronisé avec succès. ID Moloni : :id',
    'customer_sync_failed' => 'Échec de la synchronisation du client : :error',

    // PDF and status
    'download_pdf' => 'Télécharger le PDF',
    'refresh_status' => 'Actualiser',
    'pdf_not_available' => 'Le PDF n\'est pas disponible pour cette facture.',
    'invoice_not_found' => 'Aucune facture Moloni trouvée pour ce document.',
    'pdf_download_failed' => 'Échec du téléchargement du PDF : :error',
    'status_refreshed' => 'Le statut de la facture :number a été actualisé avec succès.',
    'status_refresh_failed' => 'Échec de l\'actualisation du statut : :error',
    'view_in_moloni' => 'Voir dans Moloni',

    // Customer management
    'synced_customers' => 'Clients synchronisés',
    'no_customers' => 'Aucun client synchronisé pour le moment.',
    'customer_name' => 'Nom',
    'customer_vat' => 'Numéro de TVA',
    'customer_type' => 'Type',
    'moloni_id' => 'ID Moloni',
    'individual' => 'Particulier',
    'entity' => 'Entité',
    'sync_customer_button' => 'Synchroniser le client',

    // Bulk operations
    'retry_selected' => 'Réessayer la sélection',
    'select_all' => 'Tout sélectionner',
    'bulk_retry_success' => ':count factures retraitées avec succès.',
    'bulk_retry_partial' => ':success factures réussies, :failed factures en échec.',
    'bulk_retry_failed' => ':count factures n\'ont pas pu être retraitées.',
    'no_invoices_selected' => 'Veuillez sélectionner au moins une facture à réessayer.',

    // Product reference
    'product_reference' => 'Référence Moloni',
    'product_reference_help' => 'Code de référence unique permettant d\'associer ce plan à un produit Moloni. S\'il est défini, le même produit sera réutilisé sur toutes les factures.',

    // Document series per type
    'document_series_by_type' => 'Séries de documents par type',
    'document_series_by_type_description' => 'Configurez des séries de documents différentes pour chaque type de document. Laissez vide pour utiliser la série par défaut ci-dessus.',
    'owner_type_license' => 'Licences',
    'owner_type_membership' => 'Cotisations d\'entité',
    'owner_type_member_subscription' => 'Affiliations individuelles',
    'owner_type_certification' => 'Certifications',
    'owner_type_enrollment' => 'Inscriptions d\'entités (Événements)',
    'owner_type_individual_enrollment' => 'Inscriptions du personnel/des officiels',
    'owner_type_athlete_enrollment' => 'Inscriptions d\'athlètes (Compétitions)',
    'owner_type_insurance' => 'Assurance',
    'use_default' => 'Utiliser la valeur par défaut',

    // Document type
    'document_type' => 'Type de document',
    'invoice_fatura' => 'Facture (Fatura)',
    'invoice_receipt_fatura_recibo' => 'Facture-Reçu (Fatura-Recibo)',
    'document_type_help' => 'La Facture-Reçu combine facture + paiement. Nécessite que la série de documents ait Fatura-Recibo activé dans Moloni.',

    // Document status (draft vs finalized)
    'document_status' => 'Statut du document',
    'status_finalized' => 'Finalisé (Clôturé)',
    'status_draft' => 'Brouillon (Rascunho)',
    'document_status_help' => 'Les factures en brouillon nécessitent une finalisation manuelle dans Moloni avant de devenir des documents fiscaux valides. Utilisez cette option pour une relecture avant clôture.',

    // Missing invoices
    'missing_invoices' => 'Documents sans facture',
    'documents' => 'documents',
    'create_invoices' => 'Créer les factures',
    'create_invoice' => 'Créer la facture',
    'owner' => 'Titulaire',
    'paid_date' => 'Date de paiement',
    'no_owner' => 'Aucun titulaire',
    'showing_first_50' => 'Affichage des 50 premiers documents sur :count. Les autres seront affichés une fois ceux-ci traités.',
    'no_missing_invoices' => 'Tous les documents payés ont leurs factures Moloni créées.',

    // Failure notification
    'notification_invoice_failed_subject' => 'Échec de la création de la facture Moloni',
    'notification_invoice_failed_greeting' => 'Alerte de génération de facture',
    'notification_invoice_failed_intro' => 'Le système n\'a pas pu créer une facture Moloni pour le document :document après plusieurs tentatives.',
    'notification_invoice_failed_error' => 'Erreur : :error',
    'notification_invoice_failed_attempts' => 'Le système a effectué :attempts tentatives avant d\'abandonner.',
    'notification_invoice_failed_action' => 'Voir les paramètres Moloni',
    'notification_invoice_failed_document_link' => 'Vous pouvez consulter le document à l\'adresse : :url',
    'notification_invoice_failed_database' => 'Échec de la création de la facture Moloni pour le document :document',

    // Invoice generation rules
    'invoice_generation_rules' => 'Règles de génération des factures',
    'invoice_generation_rules_description' => 'Sélectionnez les types de détails de document qui doivent déclencher la génération de factures Moloni. Les types non cochés n\'entraîneront pas de création de facture.',
    'invoice_generation_rules_saved' => 'Règles de génération des factures enregistrées avec succès.',
    'save_invoice_rules' => 'Enregistrer les règles de facturation',
    'require_all_details_enabled' => 'Exiger que tous les types de détails soient activés',
    'require_all_details_enabled_help' => 'Si cette case est cochée, les factures ne seront créées que lorsque TOUS les types de détails du document sont activés. Si elle est décochée, les factures sont créées dès qu\'UN type activé est présent.',

    // Committee-based document series
    'committee_document_series' => 'Séries de documents par comité',
    'committee_document_series_description' => 'Sélectionnez la série de documents pour les licences et les certifications en fonction de leur comité. Cette configuration est prioritaire sur la correspondance par type ci-dessous.',
    'committee_diving' => 'Comité de plongée',
    'committee_scientific' => 'Comité scientifique',
    'committee_sport' => 'Comité sportif',
    'committee_divingservices' => 'Comité des services de plongée',

    // Warnings and validation
    'warning' => 'Avertissement',
    'document_set_not_in_cache' => 'La série de documents configurée (ID : :id) ne figure pas dans les données synchronisées.',
    'sync_to_refresh' => 'Cliquez sur « Synchroniser les données » pour actualiser les séries de documents disponibles depuis Moloni.',
    'not_in_cache' => 'Absent des données synchronisées',
    'no_at_codes' => 'Aucun code AT - invalide pour les factures',

    // Activity log
    'activity_log_description' => 'Activité récente de facturation et de synchronisation',
    'invoice_created_title' => 'Facture créée',
    'invoice_failed_title' => 'Facture en échec',
    'sync_completed_title' => 'Synchronisation des données terminée',
    'sync_failed_title' => 'Échec de la synchronisation des données',
    'success' => 'Succès',
    'failed' => 'Échec',
    'view_document' => 'Voir le document',
    'companies_synced' => 'entreprises',
    'series_synced' => 'séries',
    'taxes_synced' => 'taxes',
    'categories_synced' => 'catégories',
];
