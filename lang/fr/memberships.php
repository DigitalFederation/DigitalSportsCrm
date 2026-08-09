<?php

return [
    // Subscription Creation
    'subscription_created_successfully' => 'Abonnement créé avec succès. Veuillez procéder au paiement.',
    'subscription_created_pending_payment' => 'Abonnement créé avec succès. Veuillez procéder au paiement.',
    'insurance_subscription_created_pending_payment' => 'Abonnement d\'assurance créé avec succès ! Veuillez effectuer le paiement pour activer votre couverture d\'assurance.',
    'subscription_created_free' => 'Abonnement créé avec succès.',
    'subscription_creation_error' => 'Une erreur est survenue lors du traitement de votre abonnement. Veuillez réessayer.',
    'subscription_already_pending' => 'Vous avez déjà un abonnement en attente pour ce forfait.',
    'subscription_already_pending_payment' => 'Vous avez déjà un abonnement en attente pour ce forfait. Veuillez effectuer le paiement pour l\'activer.',

    // Document Generation
    'affiliation_description' => 'Affiliation : :name - :federation',
    'insurance_description' => 'Assurance : :name',
    'subscription_document_notes' => 'Abonnement au forfait : :package',
    'bulk_subscription_document_note' => 'Abonnement groupé pour :count membres - Forfait : :package',

    // Document Observer
    'activating_subscription_after_payment' => 'Activation de l\'abonnement du membre après paiement',
    'subscription_activated' => 'Abonnement du membre activé',

    // Payment Flow
    'payment_required' => 'Paiement requis pour finaliser l\'abonnement',
    'proceed_to_payment' => 'Veuillez procéder au paiement pour activer votre abonnement',

    // Validation Messages
    'package_selection_required' => 'La sélection d\'un forfait d\'adhésion est requise.',
    'package_selection_invalid' => 'Le forfait d\'adhésion sélectionné n\'est pas valide.',
    'invalid_member_type' => 'Type de membre invalide pour l\'abonnement.',
    'no_validation_affiliation_for_insurance' => 'Une affiliation de validation active est requise pour souscrire à des forfaits d\'assurance seule.',
    'no_active_affiliation_found' => 'Aucune affiliation active trouvée. Une affiliation de validation est requise.',
    'duplicate_affiliation_plans' => 'Vous avez déjà un abonnement actif aux plans d\'affiliation suivants : :plans',
    'all_affiliation_plans_already_active' => 'Vous avez déjà un abonnement actif à tous les plans d\'affiliation de ce forfait : :plans',
    'duplicate_insurance_plans' => 'Vous avez déjà un abonnement actif ou en attente aux plans d\'assurance suivants : :plans',
    'insufficient_privileges_for_request_type' => 'Privilèges insuffisants pour ce type de demande.',
    'validation_plan_required_for_non_validation_packages' => 'Le particulier doit disposer d\'un plan de validation actif pour souscrire à ce forfait d\'adhésion.',

    // Renewal
    'subscription_renewed_successfully' => 'Abonnement d\'adhésion renouvelé avec succès.',

    // Individual Profile Messages
    'complete_profile_before_managing_subscriptions' => 'Veuillez compléter votre profil individuel avant de gérer vos abonnements.',

    // Affiliation Plan Business Scenarios
    'business_scenarios' => [
        'direct_individual' => [
            'label' => 'Abonnement individuel direct',
            'description' => 'Les particuliers souscrivent eux-mêmes directement à ce plan',
            'example' => 'Exemple : adhésion annuelle personnelle, tarifs étudiants',
        ],
        'entity_for_individuals' => [
            'label' => 'L\'entité souscrit pour des particuliers',
            'description' => 'Les entités (clubs, écoles) souscrivent à ce plan POUR leurs membres individuels',
            'example' => 'Exemple : le club paie les adhésions des athlètes, le centre de plongée paie les certifications des élèves',
        ],
        'direct_entity' => [
            'label' => 'Abonnement direct d\'entité',
            'description' => 'Les entités souscrivent à ce plan pour elles-mêmes (adhésion institutionnelle)',
            'example' => 'Exemple : adhésion institutionnelle d\'un club, certification d\'un centre de plongée',
        ],
        'flexible' => [
            'label' => 'Plan flexible',
            'description' => 'Peut être utilisé à la fois par des particuliers et des entités avec des tarifs différents',
            'example' => 'Exemple : plan premium avec tarifs individuels et institutionnels',
        ],
    ],

    // Form Labels
    'choose_business_scenario' => 'Choisir le scénario commercial',
    'business_scenario_help' => 'Sélectionnez le type de plan d\'abonnement que vous souhaitez créer. Cela détermine qui peut souscrire et comment fonctionne la tarification.',
    'plan_name' => 'Nom du plan',
    'plan_name_help' => 'Choisissez un nom clair et descriptif',
    'select_federation' => 'Sélectionner une fédération...',
    'pricing' => 'Tarification',
    'fee_individual_member' => 'Frais facturés par membre individuel',
    'fee_individual_subscription' => 'Frais en cas de souscription par des particuliers',
    'fee_entity_institution' => 'Frais facturés à l\'entité (institution)',
    'fee_entity_subscription' => 'Frais en cas de souscription par des entités',
    'free_plan_option' => 'Ceci est un plan gratuit (définir les frais à 0 €)',
    'immediate_availability' => 'Laissez vide pour une disponibilité immédiate',
    'no_expiration' => 'Laissez vide pour une durée illimitée',
    'description_help' => 'Fournissez des informations détaillées sur ce que ce plan inclut, les conditions, les avantages, etc.',
    'pdf_documents' => 'Documents PDF',
    'upload_documents_help' => 'Téléversez les conditions générales ou d\'autres documents pertinents. 10 Mo max chacun.',
    'current_attachments' => 'Pièces jointes actuelles',
    'uncheck_remove_files' => 'Décochez pour supprimer les fichiers',
    'plan_summary' => 'Résumé du plan',
    'usage' => 'Utilisation',
    'create_plan_help' => 'Créez un nouveau plan d\'affiliation en choisissant le scénario commercial qui décrit le mieux le fonctionnement souhaité de ce plan. Le formulaire vous guidera à travers les paramètres appropriés.',
    'edit_plan_help' => 'Modifiez les détails de ce plan d\'affiliation. Le scénario commercial détermine la structure du plan et les options de tarification.',
    'complete_profile_before_selecting_subscription' => 'Veuillez compléter votre profil individuel avant de sélectionner un abonnement.',
    'complete_profile_before_purchasing_subscription' => 'Veuillez compléter votre profil individuel avant d\'acheter un abonnement.',
    'complete_profile_before_viewing_history' => 'Veuillez compléter votre profil individuel avant de consulter l\'historique des abonnements.',
    'please_login_to_continue' => 'Veuillez vous connecter pour continuer.',
    'profile_issue_contact_support' => 'Un problème est survenu avec votre profil. Veuillez contacter le support.',
    'subscription_not_eligible_for_renewal' => 'Cet abonnement n\'est pas éligible au renouvellement.',
    'renewal_error_try_again' => 'Une erreur est survenue lors du renouvellement de votre abonnement. Veuillez réessayer.',
    'duplicate_affiliation_plans_error' => 'Vous avez déjà un abonnement actif pour un ou plusieurs plans d\'affiliation de ce forfait.',

    // Official Document Requirements
    'missing_official_documents' => 'Vous ne pouvez pas souscrire à ce forfait car il nécessite des documents officiels que vous n\'avez pas téléversés ou qui ne sont pas actifs.',
    'insurance_requires_document' => 'Requis : :document pour :insurance.',

    // Validation Plan
    'validation_plan' => 'Plan de validation',
    'validation_plan_help' => 'Activer des privilèges avancés pour les abonnés de ce plan',
    'validation_plan_enables' => 'Les plans de validation permettent',
    'insurance_requests' => 'Demander des polices d\'assurance',
    'license_requests' => 'Demander des licences et des certifications',
    'entity_member_licenses' => 'Pour les entités : demander des licences pour leurs membres',

    // Validation Plan Error Messages
    'insurance_subscription_not_authorized' => 'Abonnement d\'assurance non autorisé : :reason',
    'license_request_not_authorized' => 'Demande de licence non autorisée : :reason',
    'entity_member_insurance_not_authorized' => 'Attribution d\'assurance à un membre de l\'entité non autorisée : :reason',
    'entity_member_license_not_authorized' => 'Demande de licence pour un membre de l\'entité non autorisée : :reason',

    // Validation Plan Privilege Messages
    'validation_plan_no_insurance_privileges' => 'Votre plan d\'adhésion actuel n\'inclut pas les privilèges de demande d\'assurance',
    'validation_plan_no_license_privileges' => 'Votre plan d\'adhésion actuel n\'inclut pas les privilèges de demande de licence',
    'validation_plan_no_entity_member_licenses' => 'Votre plan d\'adhésion actuel ne permet pas de demander des licences pour les membres de l\'entité',
    'validation_plan_no_entity_member_subscriptions' => 'Votre plan d\'adhésion actuel ne permet pas d\'abonner des membres à des forfaits',

    // Validation Plan UI Messages
    'validation_plan_required' => 'Plan de validation requis',
    'access_restricted' => 'Accès restreint',
    'contact_federation_validation_plan' => 'Veuillez contacter votre fédération pour mettre à niveau votre plan de validation afin d\'activer les fonctionnalités d\'abonnement des membres.',
    'validation_plan_required_message' => 'Un plan de validation est requis pour abonner des membres à des forfaits.',
    'no_active_affiliation_found' => 'Aucune affiliation active trouvée',
    'entity_member_subscriptions_not_authorized' => 'Vous ne pouvez pas abonner des membres à des forfaits. :reason',
    'invalid_member_type' => 'Type de membre invalide',
    'insufficient_privileges_for_request_type' => 'Privilèges insuffisants pour ce type de demande',

    // Subscription page
    'affiliations' => 'Affiliations',
    'active_affiliations' => 'Affiliations actives',
    'included_plans' => 'Plans inclus',
    'affiliation_plans' => 'Plans d\'affiliation',

    // Member subscriptions
    'member_subscriptions' => [
        'created_successfully' => 'Abonnement du membre créé avec succès.',
        'renewed_successfully' => 'Abonnement du membre renouvelé avec succès.',
        'delete' => 'Supprimer',
        'deleted_successfully' => 'Abonnement du membre supprimé avec succès.',
        'delete_failed' => 'Échec de la suppression de l\'abonnement du membre. Veuillez réessayer.',
        'confirm_delete_title' => 'Supprimer l\'abonnement du membre',
        'confirm_delete_warning' => 'Cette action supprimera définitivement l\'abonnement du membre ainsi que toutes les affiliations et assurances associées. Cette action est irréversible.',
        'will_delete_related' => 'Cette action supprimera :affiliations affiliation(s) et :insurances assurance(s)',
        'delete_confirm' => 'Supprimer l\'abonnement',
        'change_status' => 'Changer le statut',
        'change_status_title' => 'Changer le statut de l\'abonnement',
        'change_status_warning' => 'Cette action ne modifiera que le statut de l\'abonnement. Les documents de paiement, les affiliations et les assurances ne seront PAS affectés.',
        'new_status' => 'Nouveau statut',
        'update_status' => 'Mettre à jour le statut',
        'status_updated_successfully' => 'Statut de l\'abonnement du membre mis à jour avec succès.',
        'status_update_failed' => 'Échec de la mise à jour du statut de l\'abonnement du membre.',
        'pending_payment' => 'Paiement en attente',
    ],

    // Notifications
    'subscription_activated_notification' => 'Votre abonnement à :package a été activé et est valide jusqu\'au :date.',

    // Membership states
    'states' => [
        'active' => 'Actif',
        'pending' => 'En attente',
        'expired' => 'Expiré',
        'canceled' => 'Annulé',
    ],

    // Member subscription states
    'subscription_states' => [
        'active' => 'Actif',
        'pending' => 'En attente',
        'pending_payment' => 'Paiement en attente',
        'expired' => 'Expiré',
    ],

    // Table headers
    'title' => 'Adhésions',
    'name' => 'Nom',
    'plans' => 'Plans',
    'status' => 'Statut',
    'expiration_date' => 'Date d\'expiration',
    'organizations_membership_association' => 'Association d\'adhésion aux organisations',
];
