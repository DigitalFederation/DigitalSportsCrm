<?php

return [
    // Page titles
    'title' => 'Centre de diagnostic d\'éligibilité',
    'subtitle' => 'Diagnostiquez pourquoi certains particuliers n\'apparaissent pas dans les listes d\'inscription',

    // Tab titles
    'tab_individual_profile' => 'Profil du particulier',
    'tab_event_enrollment' => 'Inscription à l\'événement',
    'tab_license_availability' => 'Disponibilité des licences',

    // Individual Profile Tab
    'individual_profile_title' => 'Diagnostic du profil du particulier',
    'individual_profile_description' => 'Recherchez un particulier pour consulter son profil d\'éligibilité complet et comprendre pourquoi il peut ou ne peut pas être inscrit pour différents rôles.',
    'search_placeholder' => 'Rechercher par code international, nom ou e-mail...',
    'no_individual_selected' => 'Aucun particulier sélectionné',
    'search_to_start' => 'Recherchez un particulier pour consulter son profil d\'éligibilité.',
    'quick_status' => 'Statut rapide',

    // Role labels
    'role_athlete' => 'Athlète',
    'role_coach' => 'Entraîneur',
    'role_referee' => 'Arbitre',
    'role_official' => 'Officiel',

    // Sections
    'federation_memberships' => 'Adhésions à des fédérations',
    'entity_memberships' => 'Adhésions à des entités',
    'professional_roles' => 'Rôles professionnels',
    'certifications' => 'Certifications (vérification arbitre)',
    'active_licenses' => 'Licences actives',

    // Table headers
    'federation' => 'Fédération',
    'entity' => 'Entité',
    'type' => 'Type',
    'status' => 'Statut',
    'since' => 'Depuis',
    'sports' => 'Sports',
    'role' => 'Rôle',
    'source' => 'Source',
    'certification' => 'Certification',
    'grants_role' => 'Accorde le rôle',
    'action_needed' => 'Action requise',
    'license' => 'Licence',
    'expires' => 'Expire',

    // Federation types
    'local' => 'Locale',
    'main' => 'Principale',
    'modalidade' => 'Sport',

    // Empty states
    'no_federation_memberships' => 'Aucune adhésion à une fédération trouvée.',
    'no_entity_memberships' => 'Aucune adhésion à une entité trouvée.',
    'no_professional_roles' => 'Aucun rôle professionnel attribué.',
    'no_certifications' => 'Aucune certification attribuée.',
    'no_active_licenses' => 'Aucune licence active.',
    'unknown_federation' => 'Fédération inconnue',
    'unknown_entity' => 'Entité inconnue',
    'unknown_license' => 'Licence inconnue',
    'unknown_certification' => 'Certification inconnue',

    // Sources
    'source_direct_assignment' => 'Attribution directe',
    'source_entity_assignment' => 'Attribution par l\'entité',

    // Certification action
    'action_activate_certification' => 'ACTIVER pour activer le rôle',

    // Quick status reasons
    'not_checked' => 'Non vérifié',
    'reason_no_active_federation' => 'Aucune adhésion active à une fédération',
    'reason_no_active_entity' => 'Aucune adhésion active à une entité',
    'reason_not_registered_athlete' => 'Non inscrit en tant qu\'athlète',
    'reason_registered_athlete' => 'Inscrit en tant qu\'athlète',
    'reason_no_coach_role' => 'Aucun rôle professionnel d\'ENTRAÎNEUR',
    'reason_has_coach_role' => 'Rôle d\'ENTRAÎNEUR attribué',
    'reason_cert_pending_activation' => 'La certification existe mais est EN ATTENTE d\'activation',
    'reason_no_referee_cert' => 'Aucune certification d\'arbitre attribuée',
    'reason_no_referee_role' => 'Aucun rôle professionnel d\'ARBITRE (vérifier la certification)',
    'reason_has_referee_role' => 'Rôle d\'ARBITRE attribué',
    'reason_no_active_membership' => 'Aucune adhésion active',
    'reason_active_member' => 'Membre actif',

    // Event Enrollment Tab
    'event_enrollment_title' => 'Diagnostic d\'inscription à l\'événement',
    'event_enrollment_description' => 'Sélectionnez un événement et un particulier pour diagnostiquer pourquoi il n\'apparaît pas dans la liste d\'inscription pour un rôle spécifique.',
    'select_event' => 'Sélectionner un événement',
    'select_event_placeholder' => '-- Sélectionnez un événement --',
    'select_competition' => 'Sélectionner une compétition (facultatif)',
    'all_competitions' => '-- Toutes les compétitions --',
    'select_role' => 'Rôle à diagnostiquer',
    'search_individual' => 'Rechercher un particulier',
    'run_diagnostic' => 'Lancer le diagnostic',
    'selected' => 'Sélectionné',
    'select_event_first' => 'Sélectionnez d\'abord un événement',
    'select_event_to_start' => 'Choisissez un événement dans la liste déroulante pour commencer le diagnostic.',

    // Diagnostic results
    'eligible_as_role' => 'ÉLIGIBLE en tant que :role',
    'not_eligible_as_role' => 'NON ÉLIGIBLE en tant que :role',
    'passed' => 'RÉUSSI',
    'failed' => 'ÉCHOUÉ',
    'suggestions' => 'Actions suggérées',

    // Check labels
    'check_federation_membership' => 'Adhésion à une fédération',
    'check_entity_membership' => 'Adhésion à une entité',
    'check_athlete_registration' => 'Inscription en tant qu\'athlète',
    'check_coach_role' => 'Rôle professionnel d\'entraîneur',
    'check_referee_role' => 'Rôle professionnel d\'arbitre',
    'check_referee_cert_exists' => 'Existence de la certification d\'arbitre',
    'check_referee_cert_active' => 'La certification est active',
    'check_required_certs' => 'Certifications requises',
    'check_required_licenses' => 'Licences requises',
    'check_active_membership' => 'Adhésion active',
    'check_not_enrolled' => 'Pas encore inscrit',

    // Check messages - Passed
    'check_federation_membership_passed' => 'Membre actif de :federation',
    'check_federation_membership_athlete_passed' => 'Dispose d\'une adhésion active à une fédération',
    'check_federation_membership_coach_passed' => 'Dispose d\'une adhésion active à une fédération',
    'check_entity_membership_passed' => 'Membre actif de : :entities',
    'check_entity_membership_passed_coach' => 'Dispose d\'une adhésion active à une entité',
    'check_athlete_registration_passed' => 'Inscrit en tant qu\'athlète pour :sport',
    'check_coach_role_passed' => 'Rôle professionnel d\'ENTRAÎNEUR attribué',
    'check_referee_role_passed' => 'Rôle professionnel d\'ARBITRE attribué',
    'check_referee_cert_exists_passed' => 'Dispose de certification(s) d\'arbitre : :certs',
    'check_referee_cert_active_passed' => 'Dispose d\'au moins une certification d\'arbitre active',
    'check_required_certs_passed' => 'Dispose de toutes les certifications requises',
    'check_required_licenses_passed' => 'Dispose de toutes les licences requises',
    'check_active_membership_passed' => 'Dispose d\'une adhésion active (peut être inscrit en tant qu\'officiel)',
    'check_not_enrolled_passed' => 'Pas encore inscrit à cet événement',

    // Check messages - Failed
    'check_federation_membership_failed' => 'Aucune adhésion active à une fédération trouvée',
    'check_entity_membership_failed' => 'Aucune adhésion active à une entité trouvée',
    'check_athlete_registration_failed' => 'Non inscrit en tant qu\'athlète dans aucune entité',
    'check_athlete_wrong_sport' => 'Inscrit pour :registered mais l\'événement requiert :required',
    'check_coach_role_failed' => 'Ne dispose pas du rôle professionnel d\'ENTRAÎNEUR',
    'check_referee_role_failed' => 'Ne dispose pas du rôle professionnel d\'ARBITRE',
    'check_referee_role_cert_pending' => 'La certification « :cert » existe mais est EN ATTENTE - le rôle d\'ARBITRE n\'est pas encore attribué',
    'check_referee_cert_exists_failed' => 'Aucune certification de type arbitre attribuée',
    'check_referee_cert_no_certs' => 'Aucune certification d\'arbitre à vérifier',
    'check_referee_cert_pending' => 'Des certifications d\'arbitre existent mais sont EN ATTENTE : :certs',
    'check_referee_cert_inactive' => 'Aucune certification d\'arbitre active trouvée',
    'check_required_certs_failed' => 'Certification(s) requise(s) manquante(s) : :certs',
    'check_required_licenses_failed' => 'Licence(s) requise(s) manquante(s) : :licenses',
    'check_active_membership_failed' => 'Aucune adhésion active dans une fédération ou une entité',
    'check_already_enrolled' => 'Déjà inscrit à cet événement pour ce rôle',

    // Suggestions
    'suggestion_activate_membership' => 'Activer l\'adhésion à la fédération/entité',
    'suggestion_join_entity' => 'Rejoindre une entité en tant que membre',
    'suggestion_register_as_athlete' => 'Inscrire en tant qu\'athlète dans Entité > Athlètes',
    'suggestion_register_for_sport' => 'Inscrire en tant qu\'athlète pour le sport approprié',
    'suggestion_assign_coach_role' => 'Attribuer le rôle d\'ENTRAÎNEUR dans Entité > Entraîneurs',
    'suggestion_attribute_referee_cert' => 'Attribuer une certification d\'arbitre dans Fédération > Certifications',
    'suggestion_activate_certification' => 'ACTIVER la certification en attente pour accorder le rôle d\'ARBITRE',
    'suggestion_check_cert_status' => 'Vérifier le statut de la certification - elle est peut-être expirée ou annulée',
    'suggestion_obtain_required_cert' => 'Obtenir et activer la ou les certifications requises',
    'suggestion_obtain_required_license' => 'Obtenir et activer la ou les licences requises',

    // Membership details
    'member_of_federations' => 'Fédération(s) : :federations',
    'member_of_entities' => 'Entité(s) : :entities',

    // License Availability Tab
    'license_availability_title' => 'Diagnostic de disponibilité des licences',
    'license_availability_description' => 'Diagnostiquez pourquoi certaines licences n\'apparaissent pas dans la liste d\'achat.',
    'coming_soon' => 'Bientôt disponible...',
];
