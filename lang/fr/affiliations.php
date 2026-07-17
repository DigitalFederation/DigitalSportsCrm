<?php

return [
    // Page titles and headers
    'title' => 'Affiliations',
    'info_title' => 'Gestion des affiliations',
    'info_body' => 'Consultez et gérez toutes les affiliations du système. Suivez les affiliations des membres, leur statut et les fédérations associées.',

    // Filter labels
    'member_type' => 'Type de membre',
    'status' => 'Statut',
    'federation' => 'Fédération',
    'member_name' => 'Nom du membre',
    'start_date' => 'Date de début',
    'end_date' => 'Date de fin',

    // Table headers
    'table' => [
        'member' => 'Membre',
        'type' => 'Type',
        'federation' => 'Fédération',
        'start_date' => 'Date de début',
        'end_date' => 'Date de fin',
        'fee' => 'Cotisation',
        'status' => 'Statut',
    ],

    // Status labels
    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspendue',
        'expired' => 'Expirée',
        'pending_payment' => 'Paiement en attente',
    ],

    // Actions
    'view_member' => 'Voir le membre',
    'delete' => 'Supprimer',
    'member' => 'Membre',

    // Data placeholders
    'member_not_found' => 'Membre introuvable',
    'no_federation' => 'Aucune fédération',
    'no_date' => 'Aucune date',
    'no_fee' => 'Aucune cotisation',
    'via_entity' => 'via l\'entité',

    // Messages
    'no_affiliations_found' => 'Aucune affiliation ne correspond à vos critères.',
    'affiliations_empty' => 'Aucune affiliation n\'a encore été créée.',
    'status_updated_successfully' => 'Le statut de l\'affiliation a été mis à jour avec succès.',
    'status_update_failed' => 'Échec de la mise à jour du statut de l\'affiliation. Veuillez réessayer.',
    'deleted_successfully' => 'Affiliation supprimée avec succès.',
    'delete_failed' => 'Échec de la suppression de l\'affiliation. Veuillez réessayer.',

    // Delete confirmation
    'confirm_delete_title' => 'Supprimer l\'affiliation',
    'confirm_delete_message' => 'Êtes-vous sûr de vouloir supprimer cette affiliation ? Cette action est irréversible.',
    'delete_confirm' => 'Supprimer l\'affiliation',

    // Status change confirmation
    'confirm_status_change' => 'Êtes-vous sûr de vouloir modifier le statut de cette affiliation ?',

    // Individual profile table
    'active_affiliations' => 'Affiliations actives',
    'affiliation_count' => '{0} Aucune affiliation|{1} :count affiliation|[2,*] :count affiliations',
    'no_active_affiliations' => 'Aucune affiliation active',
    'plan' => 'Forfait',
    'period' => 'Période',
    'privileges' => 'Privilèges',
    'standard_plan' => 'Forfait standard',
    'until' => 'jusqu\'au',
    'active' => 'Active',
    'expired' => 'Expirée',
    'validation_plan' => 'Forfait de validation',
    'insurance_requests' => 'Demandes d\'assurance',
    'license_requests' => 'Demandes de licence',
    'standard' => 'Standard',
];
