<?php

return [
    // Validation messages
    'package_required' => 'Un forfait d\'adhésion doit être sélectionné',
    'invalid_package' => 'Le forfait d\'adhésion sélectionné est invalide',
    'individuals_required' => 'Au moins une personne doit être sélectionnée',
    'min_one_individual' => 'Sélectionnez au moins une personne',

    // Success messages
    'subscriptions_created' => 'Abonnements créés',
    'success_count' => ':count abonnements ont été créés avec succès',
    'payment_required_count' => ':count nécessitent des documents de paiement',
    'free_subscriptions_count' => ':count sont gratuits et actifs',

    // Error messages
    'some_subscriptions_failed' => 'Certains abonnements ont échoué',
    'failed_count' => ':count abonnements n\'ont pas pu être créés. Veuillez consulter les journaux pour plus de détails',
    'error' => 'Erreur',
    'unexpected_error' => 'Une erreur inattendue s\'est produite lors du traitement des abonnements',
    'unauthorized_action' => 'Action non autorisée',

    // Action buttons
    'retry_failed' => 'Réessayer les échecs',
    'retry_failed_title' => 'Réessayer les abonnements en échec',
    'retry_failed_description' => 'Souhaitez-vous réessayer de créer les abonnements en échec ?',
    'yes_retry' => 'Oui, réessayer',
    'no_cancel' => 'Non, annuler',
    'try_again' => 'Réessayer',
    // Headers and titles
    'select_package' => 'Sélectionnez l\'un des forfaits à attribuer aux membres sélectionnés de votre entité.',
    'select_insurance_package' => 'Sélectionnez l\'un des forfaits d\'assurance à attribuer aux membres sélectionnés de votre entité.',
    'select_members' => 'Sélectionner les membres',
    'entity_member_memberships_title' => 'Forfaits d\'adhésion des membres de l\'entité',
    'entity_member_insurances_title' => 'Forfaits d\'assurance des membres de l\'entité',
    'selected' => 'Sélectionné',

    // Search and filters
    'search_placeholder' => 'Rechercher des membres par nom ou identifiant...',
    'filter' => [
        'all_status' => 'Tous les statuts',
        'active_subscription' => 'Abonnement actif',
        'no_subscription' => 'Aucun abonnement',
    ],

    // Table headers
    'table' => [
        'name' => 'Nom',
        'id' => 'Identifiant',
        'status' => 'Statut',
    ],

    // Status labels
    'status' => [
        'active' => 'Actif',
        'no_subscription' => 'Aucun abonnement',
    ],

    // Messages
    'no_members_found' => 'Aucun membre ne correspond à vos critères.',

    // Selection tray
    'selected_members' => 'Membres sélectionnés',
    'click_to_view' => 'cliquer pour voir',
    'clear_all' => 'Tout effacer',
    'remove_selection' => 'Retirer de la sélection',
    'total_selected' => ':count membre(s) sélectionné(s)',
    'estimated_total' => 'Total estimé',

    // Actions
    'actions' => [
        'cancel' => 'Annuler',
        'subscribe_selected' => 'Abonner les membres sélectionnés (:count)',
        'confirm' => 'Confirmer',
    ],

    // Modal
    'modal' => [
        'confirm_title' => 'Confirmer l\'abonnement',
        'confirm_message' => 'Vous êtes sur le point d\'abonner les membres sélectionnés au forfait suivant :',
        'price' => 'Prix',
        'subscription_count' => 'Cette action créera de nouveaux abonnements pour :count membres.',
    ],
];
