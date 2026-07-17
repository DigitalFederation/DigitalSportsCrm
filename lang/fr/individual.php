<?php

return [
    // Create individual form
    'create_individual' => 'Créer un compte de membre individuel',
    'full_name' => 'Nom complet',
    'sex' => 'Sexe',
    'male' => 'Masculin',
    'female' => 'Féminin',
    'vat_number' => 'Numéro d\'identification fiscale (NIF)',
    'phone' => 'Téléphone',

    // User login information section
    'user_login_information' => 'Informations de connexion de l\'utilisateur',
    'user_login_description' => 'Choisissez l\'e-mail pour l\'authentification de cet utilisateur. Un e-mail sera envoyé à la personne pour qu\'elle enregistre ses propres identifiants.',
    'login_email' => 'E-mail de connexion',
    'email_credential_help' => 'E-mail utilisé par l\'individu pour se connecter',

    // Form sections
    'personal_information' => 'Informations personnelles',
    'social_media_optional' => 'Facultatif - Ajoutez des profils de réseaux sociaux',
    'address_placeholder' => 'Nom de la rue, numéro',
    'single_name_hint' => 'Saisissez un seul prénom',
    'photo_max_size_hint' => 'Les photos doivent faire moins de 2 Mo',

    // Terms and Privacy Policy acceptance (entity creating individual)
    'terms_privacy_title' => 'Acceptation des conditions d\'utilisation et de la politique de confidentialité',
    'terms_privacy_text' => 'Je confirme avoir l\'autorisation du membre individuel pour créer son compte personnel et l\'avoir informé des conditions d\'utilisation et de la politique de confidentialité du portail.',
    'terms_privacy_checkbox' => 'Je confirme avoir lu et accepté les conditions décrites ci-dessus.',
    'terms_privacy_required' => 'Vous devez confirmer que vous avez l\'autorisation du membre individuel pour créer son compte.',

    // Public registration form
    'registration_title' => 'Inscription de compte individuel',
    'individual_registration' => 'Inscription individuelle',
    'photo' => 'Photo',
    'first_name' => 'Prénom',
    'address' => 'Adresse',
    'district' => 'District',
    'location' => 'Localité',
    'postal_code' => 'Code postal',
    'identification_document' => 'Document d\'identification',
    'document_type' => 'Type de document',
    'document_number' => 'Numéro du document',
    'expiry_date' => 'Date d\'expiration',
    'login_credentials' => 'Identifiants de connexion',
    'login_credentials_description' => 'Vous devez créer un compte pour vous connecter à la plateforme.',
    'password' => 'Mot de passe',
    'confirm_password' => 'Confirmer le mot de passe',
    'terms_and_conditions' => 'Conditions générales',
    'terms_declaration_prefix' => 'Je déclare avoir lu et accepté les',
    'terms_of_service' => 'Conditions d\'utilisation',
    'terms_declaration_middle' => 'et la',
    'privacy_policy' => 'Politique de confidentialité',
    'data_sharing_declaration_prefix' => 'J\'autorise le partage de mes données avec des tiers autorisés aux fins décrites dans la',
    'data_sharing_policy' => 'Politique de partage des données',
    'submit_registration' => 'Soumettre l\'inscription',

    // Document type options
    'doc_types' => [
        'identity_card' => 'Carte d\'identité',
        'citizen_card' => 'Carte de citoyen',
        'foreign_identity_card' => 'Carte d\'identité étrangère',
        'permanent_residence_card' => 'Carte de résident permanent',
        'passport' => 'Passeport',
    ],

    // Profile controller messages
    'error_saving_data' => 'Erreur lors de l\'enregistrement des données, veuillez contacter l\'administration.',
    'profile_updated_successfully' => 'Profil mis à jour avec succès.',
    'invalid_file_upload' => 'Téléchargement de fichier invalide.',
    'image_upload_failed' => 'Échec du téléchargement de l\'image - veuillez essayer une autre image ou compresser l\'image actuelle.',

    // Validation messages
    'duplicate_individual_exists' => 'Un individu avec les mêmes nom, prénom, date de naissance et pays existe déjà.',
    'invalid_district' => 'Le district sélectionné est invalide.',
    'validation' => [
        'photo_required' => 'La photo est obligatoire.',
        'file_must_be_image' => 'Le fichier doit être une image.',
        'photo_mimes' => 'La photo doit être un fichier JPEG ou PNG.',
        'photo_max_size' => 'La photo ne peut pas dépasser 2 Mo.',
        'name_required' => 'Le champ prénom est obligatoire.',
        'surname_required' => 'Le champ nom est obligatoire.',
        'full_name_required' => 'Le champ nom complet est obligatoire.',
        'birthdate_required' => 'Le champ date de naissance est obligatoire.',
        'country_required' => 'Le champ pays est obligatoire.',
        'district_required' => 'Le champ district est obligatoire.',
        'district_invalid' => 'Le district sélectionné est invalide.',
        'gender_required' => 'Le champ genre est obligatoire.',
        'vat_number_required' => 'Le champ NIF est obligatoire.',
        'doc_type_required' => 'Le champ type de document est obligatoire.',
        'doc_number_required' => 'Le champ numéro de document est obligatoire.',
        'doc_validity_required' => 'Le champ date de validité du document est obligatoire.',
        'email_already_registered' => 'Cette adresse e-mail est déjà enregistrée.',
        'terms_accepted' => 'Vous devez accepter les conditions d\'utilisation.',
        'data_sharing_accepted' => 'Vous devez accepter la politique de partage des données.',
        'entity_invalid' => 'L\'entité sélectionnée est invalide.',
    ],
];
