<?php

$primaryName = config('branding.primary.name', 'Example Federation');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'payment_documents' => 'Documents de paiement',
    'payment_documents_disclaimer' => 'Ces documents sont fournis à titre informatif uniquement, ils n\'ont aucune valeur légale. Pour chaque document, une facture-reçu doit être émise dans un programme de comptabilité certifié.',
    'invoices' => 'Factures',
    'create_manual_order' => 'Créer une commande manuelle',
    'latest_documents' => 'Derniers documents',
    'filtered_results' => 'Résultats filtrés',
    'entities' => 'Entités',
    'member' => 'Membre',

    // Table headers
    'number' => '# Numéro',
    'type' => 'Type',
    'document_name' => 'Nom du document',
    'status' => 'Statut',
    'issue_date' => 'Date d\'émission',
    'expiration_date' => 'Date d\'expiration',
    'total' => 'Total',
    'id' => 'ID',
    'download' => 'Télécharger',
    'category' => 'Catégorie',

    // Document detail page
    'document_detail' => 'Détail du document',
    'payment' => 'Paiement',
    'select_method' => 'Sélectionnez une méthode',
    'proceed_to_payment' => 'Procéder au paiement',

    // Document info labels
    'number_label' => 'Numéro',
    'type_label' => 'Type',
    'date_label' => 'Date',
    'recipient' => 'Destinataire',
    'vat_number' => 'Numéro de TVA',
    'city' => 'Ville',
    'address' => 'Adresse',
    'postal_code' => 'Code postal',
    'country' => 'Pays',

    // Table columns
    'product' => 'Produit',
    'qty' => 'Qté',
    'unit_price' => 'Prix unitaire',
    'amount' => 'Montant',
    'subtotal' => 'Sous-total',
    'amount_paid' => 'Montant payé',
    'remaining_balance' => 'Solde restant',

    // Payment status
    'document_is_paid' => 'Ce document est déjà payé',
    'find_details_below' => 'Retrouvez les détails ci-dessous',
    'view_moloni_invoice' => 'Voir la facture/le reçu',
    'document_type' => 'Type de document',
    'created_at' => 'Créé le',
    'transactions' => 'Transactions',
    'transaction_status' => 'Statut',
    'transaction_date' => 'Date',
    'transaction_info' => 'Infos',
    'associated_documents' => 'Documents associés',

    // Filters
    'year' => 'Année',
    'document_number' => 'Numéro de document',
    'filter_cmas_code_help' => 'Rechercher par le code international du titulaire',
    'filter_member_placeholder' => 'Nom du membre',
    'organization' => 'Organisation',
    'national_organization' => 'Organisation nationale',
    'date_from' => 'Date de début',
    'date_to' => 'Date de fin',
    'payment_date' => 'Date de paiement',

    // Index page filters
    'filters' => [
        'category' => 'Catégorie',
        'status' => 'Statut',
        'type' => 'Type',
    ],

    // Index page table
    'table' => [
        'number' => '# Numéro',
        'date' => 'Date',
        'type' => 'Type',
        'status' => 'Statut',
        'total' => 'Total',
    ],

    // Document manual create
    'attention' => 'Attention',
    'document_no' => 'N° de document',
    'due_date' => 'Date d\'échéance',
    'federation' => 'Fédération',
    'entity' => 'Entité',
    'individual' => 'Particulier',
    'manual_entry' => 'Saisie manuelle',
    'select_federation' => 'Sélectionner une fédération',
    'select_federation_option' => '-- Sélectionner une fédération --',
    'select_entity' => 'Sélectionner une entité',
    'select_entity_option' => '-- Sélectionner une entité --',
    'search_individual' => 'Rechercher un particulier',
    'search_individual_placeholder' => 'Saisissez le n° d\'affiliation, le nom ou l\'email',
    'active_member' => 'Membre actif',
    'birth_date' => 'Date de naissance',
    'manual_customer_entry' => 'Saisie manuelle du client',
    'customer_name' => 'Nom du client',
    'document_state' => 'État du document',
    'description' => 'Description',
    'delete' => 'Supprimer',
    'add_invoice_items' => 'Ajouter des lignes de facture',
    'document_line' => 'Ligne de document',
    'products' => 'Produits',
    'select_product' => '-- Sélectionner un produit --',
    'or' => 'OU',
    'product_service' => 'Produit/Service',
    'vat_percentage' => 'TVA %',
    'add_item' => 'Ajouter une ligne',
    'notes' => 'Notes',
    'save_document' => 'Enregistrer le document',

    // Moloni invoice
    'create_moloni_invoice' => 'Créer une facture Moloni',
    'create_moloni_invoice_description' => 'Cochez cette option pour créer automatiquement une facture dans Moloni pour ce paiement.',

    // Owner type categories (for document filters)
    'categories' => [
        'License' => 'Licence',
        'Membership' => 'Abonnement',
        'Document' => 'Document',
        'Certification' => 'Certification',
        'Registration' => 'Inscription',
        'Manual Order' => 'Commande manuelle',
        'Insurance' => 'Assurance',
    ],

    // Document states
    'states' => [
        'paid' => 'Payé',
        'draft' => 'Brouillon',
        'pending' => 'En attente',
        'canceled' => 'Annulé',
        'partially_paid' => 'Partiellement payé',
        'void' => 'Nul',
    ],

    // Action messages
    'edit_draft_only' => 'La modification n\'est autorisée que pour les documents à l\'état Brouillon.',
    'notification_sent' => 'Notification envoyée.',
    'document_canceled_successfully' => 'Document annulé avec succès.',
    'not_cancellable_state' => 'Le document n\'est pas dans un état permettant l\'annulation.',
    'has_associated_payments' => 'Le document ne peut pas être supprimé car il comporte des paiements associés.',
    'no_invoices_found' => 'Aucune facture ne correspond aux critères spécifiés.',
    'export_failed' => 'Échec de la génération de l\'export. Veuillez réessayer ou contacter le support.',

    // Confirmations
    'confirm_delete_warning' => 'Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible et supprimera toutes les données associées.',
    'confirm_cancel_warning' => 'Êtes-vous sûr de vouloir annuler ce document ?',
    'document_deleted_successfully' => 'Document supprimé avec succès.',

    // Buttons
    'resend_notification' => 'Renvoyer la notification',
    'delete_document' => 'Supprimer le document',

    // Filter labels
    'document_period' => 'Période du document',

    // Invoice/Order PDF labels
    'pdf' => [
        'name' => 'Nom',
        'city' => 'Ville',
        'address' => 'Adresse',
        'date' => 'Date',
        'vat_number' => 'Numéro de TVA',
        'postal_code' => 'Code postal',
        'member_number' => 'N° de membre',
        'country' => 'Pays',
        'notes' => 'Notes',
        'description' => 'DESCRIPTION',
        'qty' => 'QTÉ',
        'unit_price' => 'PRIX UNITAIRE',
        'total' => 'TOTAL',
        'subtotal' => 'Sous-total',
        'tax' => 'Taxe',
        'order_disclaimer' => 'Ce document ne constitue ni une facture ni un reçu. Le document fiscal valide sera émis après confirmation du paiement, au moyen d\'un programme de facturation certifié conformément à la législation en vigueur.',
    ],

    // Invoice PDF compliance text
    'invoice_compliance_en' => "Entities and individuals hereby undertake to comply with and strictly enforce {$primaryShortName} rules, as well as to urge their members to adopt an underwater environmental friendly attitude.",
    'invoice_compliance_pt' => "As entidades e individuos comprometem-se por este documento a aplicar e fazer aplicar rigorosamente as regras de {$primaryShortName} e a incentivar os seus membros a adotar uma atitude respeitosa pelo ambiente subaquatico.",
];
