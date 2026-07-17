<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'members_list' => 'Liste des membres',
    'member_list' => 'Liste des membres',
    'entities' => 'Entités collectives',
    'entity_detail' => 'Détail de l\'entité',
    'entities_to_approve' => 'Entités à approuver',
    'create_entity' => 'Créer une entité',
    'create_entity_account' => 'Créer un compte d\'entité',
    'edit_entity_record' => 'Modifier la fiche de l\'entité',

    // Actions
    'create_individual' => 'Créer un particulier',
    'individuals_to_approve' => 'Particuliers à approuver',
    'invite_member' => 'Inviter un membre',
    'submit_request' => 'Soumettre la demande',
    'approve_entity' => 'Approuver l\'entité',
    'accept_request' => 'Accepter cette demande ?',
    'view_all' => 'Voir tout',
    'see_all_instructors' => 'Voir tous les instructeurs',
    'open_url' => 'Ouvrir l\'URL',

    // Table headers
    'gender' => 'Sexe',
    'id_number' => 'Numéro d\'identification',
    'national_affiliation' => "Affiliation {$primaryShortName}",
    'table_name' => 'Nom',
    'table_country' => 'Pays',
    'table_national_fed_nr' => 'N° féd. nationale',
    'table_cmas_zone' => 'Zone internationale',
    'table_sub_region' => 'Sous-région',
    'table_actions' => 'Actions',
    'table_nationality' => 'Nationalité',
    'table_email' => 'E-mail',
    'table_requested' => 'Demandé',
    'table_federation' => 'Organisation',
    'table_type' => 'Type',
    'table_status' => 'Statut du membre',
    'table_national_number' => 'Numéro national',
    'table_number' => 'Numéro',
    'table_date' => 'Date',
    'table_total' => 'Total',
    'table_zone_or_association' => 'Zone ou association territoriale',

    // Form labels
    'name' => 'Nom de l\'entité',
    'given_name' => 'Prénom',
    'family_name' => 'Nom de famille',
    'nationality' => 'Nationalité',
    'federation' => 'Fédération',
    'birthdate' => 'Date de naissance',
    'member_number' => 'Numéro de membre',
    'affiliation_status' => 'Statut d\'affiliation',
    'affiliation_active' => 'Actif',
    'affiliation_inactive' => 'Inactif',
    'valid_member_code' => 'Code de membre valide',

    // Form sections
    'information' => 'Informations',
    'entity_logo' => 'Logo de l\'entité',
    'club_school_center_name' => 'Nom du club/de l\'école/du centre',
    'legal_name' => 'Raison sociale',
    'responsible_person_name' => 'Nom de la personne responsable',
    'nif' => 'Numéro fiscal (NIF)',
    'national_fed_nr' => 'N° féd. nationale',
    'affiliate_nr' => 'N° d\'affilié',
    'hq_location' => 'Emplacement du siège',
    'district' => 'District',
    'zones' => 'Zones',
    'no_zones_assigned' => 'Aucune zone attribuée',
    'address' => 'Adresse',
    'location' => 'Localité',
    'zip_code' => 'Code postal',
    'country' => 'Pays',
    'select_option' => '-- Sélectionnez une option --',
    'public_contacts' => 'Contacts publics',
    'contact_email' => 'E-mail de contact',
    'website' => 'Site web',
    'phone_number' => 'Numéro de téléphone',
    'social_media_links' => 'Liens vers les réseaux sociaux',
    'facebook_url' => 'URL Facebook',
    'x_url' => 'URL X',
    'instagram_url' => 'URL Instagram',
    'linkedin_url' => 'URL LinkedIn',

    // Terms and policies
    'terms_policies' => 'Conditions et politiques',
    'terms_confirm' => 'Je confirme que l\'entité accepte les',
    'terms_of_service' => 'Conditions d\'utilisation',
    'and' => 'et',
    'privacy_policy' => 'Politique de confidentialité',
    'data_sharing_confirm' => 'Je confirme que l\'entité consent au partage des données avec des tiers autorisés, tel que décrit dans la',
    'data_sharing_policy' => 'Politique de partage des données',
    'save_record' => 'Enregistrer la fiche',

    // User login section
    'user_login_information' => 'Informations de connexion de l\'utilisateur',
    'user_login_info_description' => 'Après avoir choisi l\'adresse e-mail de l\'utilisateur, un e-mail sera envoyé afin que la personne enregistre ses propres identifiants.',
    'user_login_email' => 'E-mail de connexion de l\'utilisateur',
    'confirm_user_login_email' => 'Confirmer l\'e-mail de connexion de l\'utilisateur',
    'confirm_email_address' => 'Confirmer l\'adresse e-mail',
    'email_credential_hint' => 'Identifiant e-mail permettant à l\'utilisateur de se connecter',
    'entity_creation_info' => 'Lorsqu\'une fiche d\'entité est créée, un utilisateur est également automatiquement associé à cette fiche. Un e-mail sera envoyé à l\'adresse choisie afin que l\'utilisateur enregistre ses propres identifiants. Après cela, la personne pourra se connecter à la plateforme.',

    // Modal content
    'member_invitation_form' => 'Formulaire d\'invitation de membre',
    'member_request' => 'Invitation de membre',
    'member_request_description' => 'Vous pouvez utiliser ce formulaire pour inviter des membres à l\'aide de leur identifiant personnel ou de leur numéro de membre. Vous devez demander l\'un de ces éléments au membre avant d\'envoyer cette invitation.',
    'or_separator' => 'OU',

    // Zone assignment
    'zone_auto_assigned' => 'La zone sera automatiquement attribuée en fonction de votre association.',
    'zone_will_be' => 'Zone',
    'zone_edit_restricted' => "Seul {$primaryShortName} ou un administrateur peut modifier ce champ.",

    // Entity approval
    'approval_national_federation_message' => 'Vous êtes sur le point d\'approuver cette entité. Un numéro de membre sera automatiquement attribué.',
    'approval_association_message' => 'Vous êtes sur le point d\'approuver cette entité pour votre association.',
    'member_number_auto_generated' => 'Le numéro de membre sera automatiquement généré lors de l\'approbation.',
    'member_number_primary_federation_only' => "Remarque : seul {$primaryShortName} peut attribuer le numéro de fédération nationale. L\'entité sera approuvée pour votre association sans numéro de membre.",

    // Show page
    'tax_identification_number' => 'Numéro d\'identification fiscale',
    'hq_address_city_postal' => 'Adresse du siège, ville, code postal',
    'individuals' => 'Particuliers',
    'diving_certifications' => 'Certifications de plongée',
    'scientific_certifications' => 'Certifications scientifiques',
    'diving_licenses' => 'Licences de prestataire de services de plongée',
    'scientific_licenses' => 'Licences scientifiques',
    'sport_licenses' => 'Licences sportives',
    'instructors' => 'Instructeurs',
    'active' => 'actif',
    'no_instructors_yet' => 'Aucun instructeur pour le moment',
    'federations' => 'Fédération(s)',
    'associations' => 'Associations',
    'federation_and_associations' => 'Fédération et associations',
    'no_individuals_yet' => 'Aucun particulier pour le moment',
    'local_federation' => 'Association',
    'main_federation' => 'Fédération principale',
    'no_federation_memberships' => 'Aucune adhésion à une fédération trouvée',
    'no_association_memberships' => 'Aucune adhésion à une association trouvée',
    'table_association' => 'Association',
    'association_type_territorial' => 'Territoriale',
    'association_type_nacional' => 'Nationale',
    'association_type_modalidade' => 'Discipline',

    // Documents
    'documents_invoices' => 'Documents et factures',
    'view' => 'Voir',
    'no_documents_found' => 'Aucun document trouvé',
    'no_documents_description' => 'Aucun document ni facture n\'a encore été généré pour cette entité.',
    'showing_last_documents' => 'Affichage des :count derniers documents',

    // Messages
    'invalid_cmas_code' => 'Le code international est invalide. Veuillez vérifier les informations fournies.',
    'invalid_member_number' => 'Le numéro de membre est invalide. Veuillez vérifier les informations fournies.',
    'member_must_have_federation' => 'Ce membre doit avoir une relation avec une fédération (active ou en attente) et ne doit pas déjà faire partie de votre entité.',
    'invitation_sent_success' => 'L\'invitation du membre a été envoyée avec succès. Veuillez laisser le temps au membre d\'examiner votre demande.',
    'error_creating_record' => 'Erreur lors de la création de cette fiche : :error',

    // Entity Profile Tabs
    'no_certifications_message' => 'Cette entité n\'a encore aucune certification attribuée.',
    'no_licenses_message' => 'Cette entité n\'a encore aucune licence attribuée.',

    // Federation membership
    'designation' => 'Désignation',
    'member_approved' => 'Membre approuvé',
    'member_pending_approval' => 'En attente d\'approbation',
    'federation_membership_info' => 'Ce tableau affiche votre statut d\'adhésion à la fédération et aux associations.',

    // Entity Dashboard
    'dashboard' => [
        'entity_profile' => 'Profil de l\'entité',
        'members_to_approve' => 'Membres à approuver',
        'no_pending_members' => 'Aucune demande de membre en attente',
        'entity_affiliations' => 'Affiliations de l\'entité',
        'no_affiliations' => 'Aucune affiliation trouvée',
        'no_sport_licenses' => 'Aucune licence sportive',
        'no_diving_licenses' => 'Aucune licence de plongée',
        'no_entity_found' => 'Entité introuvable',
        'no_entity_associated' => 'Aucune entité n\'est associée à votre compte.',
    ],

    // Error messages
    'committee_not_found' => 'Le comité requis pour le type d\'entité :type n\'est pas configuré. Veuillez contacter le support.',

    // Map
    'get_directions' => 'Obtenir l\'itinéraire',

    // International Portal
    'cmas_portal_access' => 'Accès au portail international',
    'has_cmas_portal_account' => 'Possède un compte sur le portail international',
    'cmas_portal_description' => 'Cochez cette case si l\'entité possède un compte sur le portail international',

    // Public Page Management
    'public_page' => [
        'title' => 'Gestion de la page publique',
        'subtitle' => 'Gérez le profil public et le contenu de votre organisation',
        'view_public_page' => 'Voir la page publique',
        'tabs' => [
            'general' => 'Paramètres généraux',
            'featured_locations' => 'Sites en vedette',
            'courses' => 'Cours de plongée',
        ],
        'background_image' => 'Image d\'arrière-plan du profil',
        'current_background' => 'Arrière-plan actuel',
        'current_image' => 'Image actuelle',
        'confirm_remove_background' => 'Êtes-vous sûr de vouloir supprimer l\'image d\'arrière-plan ?',
        'background_removed' => 'Image d\'arrière-plan supprimée avec succès.',
        'upload_file' => 'Téléverser un fichier',
        'or_drag_drop' => 'ou glisser-déposer',
        'image_requirements' => 'PNG, JPG, WEBP jusqu\'à 2 Mo',
        'preview' => 'Aperçu',
        'public_description' => 'Description publique',
        'description_help' => 'Cette description sera affichée sur votre page de profil publique.',
        'save_settings' => 'Enregistrer les paramètres',
        'settings_saved' => 'Paramètres enregistrés avec succès.',
        'featured_locations' => [
            'title' => 'Sites de plongée en vedette',
            'description' => 'Sélectionnez les sites de plongée que vous souhaitez mettre en avant sur votre profil public.',
            'select_locations' => 'Sélectionner des sites',
            'no_locations_selected' => 'Aucun site de plongée sélectionné.',
            'selected_preview' => 'Aperçu des sites sélectionnés',
            'save_locations' => 'Enregistrer les sites en vedette',
            'locations_saved' => 'Sites en vedette mis à jour avec succès.',
            'create_new' => 'Créer un nouveau site',
        ],
    ],
];
