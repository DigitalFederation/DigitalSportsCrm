<?php

$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalName = config('branding.international.name', 'International Federation');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    // Page titles
    'licenses' => 'Licences',
    'my_licenses_description' => 'Vous pouvez consulter ici toutes vos licences et acheter de nouvelles licences de membre',
    'view_my_licenses' => 'Voir mes licences',
    'no_federation_association_description' => 'Vous n\'êtes associé à aucune fédération. Veuillez contacter l\'administrateur de votre fédération afin d\'établir cette association avant d\'acheter des licences.',
    'no_international_license_access_description' => 'Vous n\'êtes pas associé à une fédération disposant d\'accords de licence internationaux. Seuls les membres des fédérations ayant des accords internationaux peuvent acheter ces licences.',

    // Tab sections
    'basic_information' => 'Informations de base',
    'roles_permissions' => 'Rôles et permissions',
    'requirements' => 'Prérequis',
    'pricing' => 'Tarification',
    'availability' => 'Disponibilité',
    'advanced_settings' => 'Paramètres avancés',

    // Document requirements sections
    'diving_professionals' => 'Professionnels de la plongée',

    // Purchase page titles and headers
    'Purchase License' => 'Acheter une licence',
    'Manage Licenses' => 'Gérer les licences',
    'Manage Licenses for' => 'Gérer les licences pour',
    'License Purchased Successfully!' => 'Licence achetée avec succès !',
    'Purchase Successful!' => 'Achat réussi !',
    'Purchase Successful' => 'Achat réussi',
    'order_details' => 'Détails de la commande',

    // Page descriptions
    'Select and purchase a license for yourself' => 'Sélectionnez et achetez une licence pour vous-même',
    'Purchase licenses for your entity or members' => 'Achetez des licences pour votre entité ou vos membres',

    // Information messages
    'Information' => 'Informations',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. Please ensure your profile information is complete before proceeding.' => 'Sélectionnez une licence et procédez au paiement. Votre licence sera activée automatiquement dès la confirmation du paiement. Veuillez vous assurer que les informations de votre profil sont complètes avant de continuer.',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. For group purchases, you can select multiple members to receive the same license.' => 'Sélectionnez une licence et procédez au paiement. Votre licence sera activée automatiquement dès la confirmation du paiement. Pour les achats groupés, vous pouvez sélectionner plusieurs membres devant recevoir la même licence.',

    // Form labels and options
    'Select Federation' => 'Sélectionner une fédération',
    'Select a federation...' => 'Sélectionnez une fédération...',
    'Select License' => 'Sélectionner une licence',
    'Purchase Type' => 'Type d\'achat',
    'Individual License' => 'Licence individuelle',
    'Group Purchase' => 'Achat groupé',
    'Select Member' => 'Sélectionner un membre',
    'Select Members' => 'Sélectionner des membres',
    'Select a member...' => 'Sélectionnez un membre...',

    // Purchase type descriptions
    'Purchase license for one specific member' => 'Acheter une licence pour un membre spécifique',
    'Purchase licenses for multiple members' => 'Acheter des licences pour plusieurs membres',

    // License information
    'License' => 'Licence',
    'License Code' => 'Code de la licence',
    'License Holder' => 'Titulaire de la licence',
    'License Information' => 'Informations sur la licence',
    'per license' => 'par licence',
    'license' => 'Licence',
    'start_date' => 'Date de début',
    'expiry_date' => 'Date d\'expiration',
    'status' => 'Statut',

    // Purchase summary
    'Purchase Summary' => 'Récapitulatif de l\'achat',
    'Purchase Details' => 'Détails de l\'achat',
    'Entity' => 'Entité',
    'Federation' => 'Fédération',
    'Number of Members' => 'Nombre de membres',
    'Price per License' => 'Prix par licence',
    'Total' => 'Total',
    'Total Amount' => 'Montant total',
    'Total Paid' => 'Total payé',

    // Status and dates
    'Status' => 'Statut',
    'Active' => 'Active',
    'Payment Confirmed' => 'Paiement confirmé',
    'Issue Date' => 'Date de délivrance',
    'Expiration Date' => 'Date d\'expiration',
    'Today' => 'Aujourd\'hui',
    'Permanent' => 'Permanente',

    // International and codes
    'Pending Assignment' => 'Attribution en attente',
    'Order Number' => 'Numéro de commande',

    // Success messages
    'Your license has been activated and is ready to use' => 'Votre licence a été activée et est prête à être utilisée',
    'Your license purchase has been completed successfully' => 'Votre achat de licence a été effectué avec succès',
    'All selected members have been automatically licensed' => 'Tous les membres sélectionnés ont automatiquement reçu leur licence',
    'Your entity license has been automatically activated' => 'La licence de votre entité a été activée automatiquement',

    // Certificate information
    'Your License Certificate' => 'Votre certificat de licence',
    'Your license certificate is now available for download' => 'Votre certificat de licence est désormais disponible au téléchargement',
    'License certificates are now available for download' => 'Les certificats de licence sont désormais disponibles au téléchargement',
    'A confirmation email has been sent to your registered email address' => 'Un e-mail de confirmation a été envoyé à votre adresse e-mail enregistrée',
    'You will receive email confirmation shortly' => 'Vous recevrez une confirmation par e-mail sous peu',

    // Next steps and information
    'What happens next?' => 'Que se passe-t-il ensuite ?',
    'Important Information' => 'Informations importantes',
    'Remember to renew before expiration date' => 'N\'oubliez pas de renouveler avant la date d\'expiration',

    // Action buttons
    'View My Licenses' => 'Voir mes licences',
    'Download Invoice' => 'Télécharger la facture',
    'Download Certificate' => 'Télécharger le certificat',
    'Back to Dashboard' => 'Retour au tableau de bord',

    // Error messages
    'no_license_purchase_found' => 'Aucun achat de licence trouvé.',
    'entity_license_required_for_members' => 'Votre entité doit disposer d\'une licence d\'entité active avant de pouvoir acheter des licences de membre. Veuillez d\'abord acheter une licence d\'entité.',
    'entity_sport_license_required' => 'Votre entité doit disposer d\'une licence d\'entité active pour ce sport avant de pouvoir acheter des licences de membre correspondantes. Veuillez d\'abord acheter une licence d\'entité pour ce sport.',
    'No licenses available' => 'Aucune licence disponible',
    'There are no licenses available for purchase in this federation at the moment.' => 'Aucune licence n\'est actuellement disponible à l\'achat dans cette fédération.',
    'There are no licenses available for entity purchase at the moment.' => 'Aucune licence n\'est actuellement disponible à l\'achat pour les entités.',
    'No Federation Association' => 'Aucune association à une fédération',
    'You are not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'Vous n\'êtes associé à aucune fédération. Veuillez contacter l\'administrateur de votre fédération afin d\'établir cette association avant d\'acheter des licences.',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'Votre entité n\'est associée à aucune fédération. Veuillez contacter l\'administrateur de votre fédération afin d\'établir cette association avant d\'acheter des licences.',
    'No federation' => 'Aucune fédération',

    // Dynamic messages with parameters
    'Purchase for' => 'Acheter pour',
    'Purchase for €:amount' => 'Acheter pour :amount €',
    'Purchase for :amount' => 'Acheter pour :amount',
    'Request Free License' => 'Demander une licence gratuite',
    ':count members selected' => ':count membres sélectionnés',
    'This license certifies you for: :role' => 'Cette licence vous certifie pour : :role',
    'Valid for sport: :sport' => 'Valable pour le sport : :sport',
    'members' => 'membres',
    'Members' => 'Membres',

    // Federation License Manager
    'Select which licenses this federation can offer to its member entities.' => 'Sélectionnez les licences que cette fédération peut proposer à ses entités membres.',
    'Search Licenses' => 'Rechercher des licences',
    'Search by name or code...' => 'Rechercher par nom ou par code...',
    'Filter by Committee' => 'Filtrer par commission',
    'All Committees' => 'Toutes les commissions',
    'selected' => 'sélectionné(s)',
    'International' => 'International',
    'No licenses found matching your filters.' => 'Aucune licence ne correspond à vos filtres.',
    'No licenses available.' => 'Aucune licence disponible.',
    'license(s) selected' => 'licence(s) sélectionnée(s)',
    'Cancel' => 'Annuler',
    'Save Changes' => 'Enregistrer les modifications',
    'Licenses updated successfully!' => 'Licences mises à jour avec succès !',

    // Debug information messages
    'cannot_proceed_with_purchase' => 'Impossible de poursuivre l\'achat :',
    'entity_no_active_affiliation' => 'L\'entité ne dispose pas d\'une affiliation active',
    'no_license_selected' => 'Aucune licence sélectionnée',
    'price_not_calculated' => 'Prix non calculé',
    'calculated_price' => 'prix calculé',
    'no_members_selected' => 'Aucun membre sélectionné',
    'no_members_for_entity' => 'Aucun membre trouvé pour cette entité. Veuillez vous assurer que votre entité comporte des personnes associées.',
    'validation_plan' => 'Plan de validation',

    // Affiliation messages
    'Active Affiliation Required' => 'Affiliation active requise',
    'Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.' => 'Votre entité doit disposer d\'une affiliation active (formule d\'adhésion) pour acheter des licences. Veuillez vous assurer que l\'adhésion de votre entité est active et réglée avant de continuer.',
    'You must have an active affiliation (membership package) to purchase licenses. Please ensure your individual membership is active and paid before proceeding.' => 'Vous devez disposer d\'une affiliation active (formule d\'adhésion) pour acheter des licences. Veuillez vous assurer que votre adhésion individuelle est active et réglée avant de continuer.',

    // License validation error messages
    'already_has_license' => 'Vous possédez déjà une licence :status de ce type',
    'Your profile already has this Active License' => 'Votre profil possède déjà cette licence active',
    'Your license is pending payment' => 'Votre licence est en attente de paiement',
    'missing_required_documents_detailed' => 'Impossible de demander cette licence. Les documents requis suivants sont manquants : :documents. Veuillez téléverser ces documents dans la section Documents officiels avant de demander cette licence.',
    'missing_required_certifications' => 'Impossible de demander cette licence. Les certifications requises suivantes sont manquantes : :certifications. Veuillez obtenir ces certifications avant de demander cette licence.',
    'members_missing_required_certifications' => 'Les membres suivants ne possèdent pas les certifications requises : :members',
    'license_requirements' => 'Prérequis de la licence',
    'required_certifications' => 'Certifications requises',
    'required_documents' => 'Documents requis',
    'member_missing_certifications' => 'Certifications manquantes : :certifications',
    'member_missing_documents' => 'Documents manquants : :documents',
    'member_must_have_active_affiliation' => 'Le membre doit disposer d\'une affiliation active',
    'show_ineligible_members' => 'Afficher les membres non éligibles',
    'hide_ineligible_members' => 'Masquer les membres non éligibles',
    'member_not_eligible' => 'Ce membre ne remplit pas les conditions requises',
    'no_eligible_members' => 'Aucun membre éligible pour cette licence',
    'some_members_ineligible' => ':eligible membres sur :total sont éligibles pour cette licence',
    'entity' => 'entité',
    'individual' => 'personne',
    'license_cannot_be_purchased_by' => 'Cette licence ne peut pas être achetée par :type',
    'license_request_not_authorized' => 'Demande de licence non autorisée : :reason',
    'license_parameter_null' => 'Le paramètre de licence est nul',
    'license_missing_properties' => 'Il manque à la licence des propriétés requises (id ou license_code)',
    'cannot_determine_federation' => 'Impossible de déterminer la fédération pour l\'achat de la licence',
    'license_price_not_configured' => 'Le prix de la licence n\'est pas configuré pour ce type d\'acheteur',

    // License fields
    'license_type' => 'Type de licence',
    'license_number' => 'Numéro de licence',
    'valid_until' => 'Valable jusqu\'au',
    'acceptance_date' => 'Date d\'acceptation',
    'issue_date' => 'Date de délivrance',
    'expiration_date' => 'Date d\'expiration',

    // Error messages for purchase flow
    'This license is not free' => 'Cette licence n\'est pas gratuite.',
    'This license cannot be purchased with this method' => 'Cette licence ne peut pas être achetée avec cette méthode.',

    // License status messages
    'Your profile already has this Active License' => 'Votre profil possède déjà cette licence active',
    'Your license is pending payment' => 'Votre licence est en attente de paiement',
    'Your license is pending admin validation' => 'Votre licence est en attente de validation par un administrateur',
    'Your license is pending technical director approval' => 'Votre licence est en attente d\'approbation par le directeur technique',
    'Your license is being processed' => 'Votre licence est en cours de traitement',

    // New form translations
    'Search licenses' => 'Rechercher des licences',
    'Search licenses...' => 'Rechercher des licences...',
    'licenses found' => 'licences trouvées',
    'Sport Committee' => 'Commission sportive',
    'All Sports' => 'Tous les sports',
    'Role' => 'Rôle',
    'Price' => 'Prix',
    'Free' => 'Gratuite',
    'Select' => 'Sélectionner',
    'Purchase' => 'Acheter',
    'Request' => 'Demander',
    'Contact Support' => 'Contacter le support',
    'Membership Required' => 'Adhésion requise',

    // Admin validation
    'license_pending_validation_requires_approval' => 'La licence est en attente de validation et requiert l\'approbation d\'un administrateur.',
    'validate_and_approve' => 'Valider et approuver',
    'reject_validation' => 'Rejeter la validation',

    // Entity pending licenses
    'entity_has_pending_licenses' => 'Votre entité a des licences en attente de paiement',
    'invitations_available_after_payment' => 'Les invitations d\'athlètes et d\'entraîneurs seront disponibles une fois le paiement de la licence effectué',
    'complete_payment_to_enable_invitations' => 'Effectuez le paiement pour activer vos licences et débloquer les fonctionnalités d\'invitation',
    'pending_licenses_for_sports' => 'Licences en attente pour : :sports',
    'license_approved_successfully' => 'Licence approuvée avec succès.',
    'error_approving_license' => 'Erreur lors de l\'approbation de la licence : ',
    'license_not_in_approvable_state' => 'La licence n\'est pas dans un état permettant l\'approbation',
    'license_validation_rejected' => 'Validation de la licence rejetée',
    'license_canceled' => 'Licence annulée',
    'cannot_activate_unpaid_license' => 'Impossible d\'activer la licence : le paiement n\'a pas été effectué. Veuillez vous assurer que le document de paiement associé est réglé avant l\'activation.',

    // License state translations
    'statuses' => [
        'ActiveLicenseAttributedState' => 'Active',
        'PendingLicenseAttributedState' => 'En attente',
        'PendingTechnicalDirectorApprovalLicenseAttributedState' => 'En attente d\'approbation DT',
        'PendingValidationLicenseAttributedState' => 'En attente de validation administrateur',
        'CanceledLicenseAttributedState' => 'Annulée',
        'SuspendedLicenseAttributedState' => 'Suspendue',
        'ExpiredLicenseAttributedState' => 'Expirée',
        'ProvisionalLicenseAttributedState' => 'Provisoire',
    ],

    // State translations for the states themselves
    'states' => [
        'pending' => 'En attente',
        'active' => 'Active',
        'expired' => 'Expirée',
        'suspended' => 'Suspendue',
        'canceled' => 'Annulée',
        'provisional' => 'Provisoire',
        'waiting_approval' => 'En attente d\'approbation',
        'pending_validation' => 'En attente de validation',
        'pending_technical_director_approval' => 'En attente d\'approbation du directeur technique',
        'no_license' => 'Aucune licence',
    ],

    // International License Specific
    'Active Affiliation Required' => 'Affiliation active requise',
    'You must have an active affiliation (membership package) to purchase international licenses. Please ensure your individual membership is active and paid before proceeding.' => 'Vous devez disposer d\'une affiliation active (formule d\'adhésion) pour acheter des licences internationales. Veuillez vous assurer que votre adhésion individuelle est active et réglée avant de continuer.',
    'Search international licenses' => 'Rechercher des licences internationales',
    'Search international licenses...' => 'Rechercher des licences internationales...',
    'international licenses found' => 'licences internationales trouvées',
    'International License' => 'Licence internationale',
    'No international licenses available' => 'Aucune licence internationale disponible',
    'No international licenses are currently available for your federation.' => 'Aucune licence internationale n\'est actuellement disponible pour votre fédération.',
    'No international licenses match your search criteria.' => 'Aucune licence internationale ne correspond à vos critères de recherche.',
    'Purchase International License' => 'Acheter une licence internationale',
    'International License Purchase Success' => 'Achat de licence internationale réussi',
    'Purchase Initiated Successfully' => 'Achat initié avec succès',
    'Your international license purchase has been initiated. Please complete the payment to activate your license.' => 'Votre achat de licence internationale a été initié. Veuillez effectuer le paiement pour activer votre licence.',
    'International License Details' => 'Détails de la licence internationale',
    'View National Licenses' => 'Voir les licences nationales',
    'Select and purchase an international license for yourself' => 'Sélectionnez et achetez une licence internationale pour vous-même',
    'No International License Access' => 'Aucun accès aux licences internationales',
    'Back to International Licenses' => 'Retour aux licences internationales',
    'View My International Licenses' => 'Voir mes licences internationales',
    'Purchase International Licenses for Members' => 'Acheter des licences internationales pour les membres',
    'Select members and purchase international licenses on their behalf' => 'Sélectionnez des membres et achetez des licences internationales en leur nom',
    'Purchase International Entity License' => 'Acheter une licence d\'entité internationale',
    'Purchase an international license for your organization' => 'Achetez une licence internationale pour votre organisation',
    'Switch to International Entity License Purchase' => 'Passer à l\'achat de licence d\'entité internationale',
    'Switch to International Member License Purchase' => 'Passer à l\'achat de licence de membre internationale',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing international licenses.' => 'Votre entité n\'est associée à aucune fédération. Veuillez contacter l\'administrateur de votre fédération afin d\'établir cette association avant d\'acheter des licences internationales.',

    // Table headers
    'licenses_title' => 'Licences',
    'name' => 'Nom',
    'license_name' => 'Nom de la licence',
    'year' => 'Année',
    'actions' => 'Actions',
    'sport_commission' => 'Commission sportive',
    'sport_categories' => 'Catégories sportives',
    'not_active' => 'Non active',
    'assign_individual_license' => 'Attribuer une licence individuelle',
    'assign_entity_license' => 'Attribuer une licence d\'entité',

    // Separated license page titles
    'Sport Club Licenses' => 'Licences de club sportif',
    'Sport Licenses' => 'Licences sportives',
    'International Entity Licenses' => "Licences d'entité {$internationalName}",
    'International Professional Licenses' => "Licences professionnelles {$internationalName}",
    'Scientific Entity Licenses' => 'Licences d\'entité scientifiques',
    'Scientific Professional Licenses' => 'Licences professionnelles scientifiques',
    'Primary Diving Services Licenses' => "Licences de services de plongée {$primaryShortName}",

    // Middleware error messages
    'entity_has_inactive_license' => 'Votre entité possède une licence :committee, mais celle-ci n\'est pas active actuellement. Veuillez vous assurer que votre licence :committee est active pour accéder à cette fonctionnalité.',
    'entity_needs_active_license' => 'Votre entité a besoin d\'une licence :committee active pour accéder à cette fonctionnalité. Veuillez contacter votre fédération pour obtenir la licence nécessaire.',

    // License states
    'state_active' => 'Active',
    'state_pending' => 'En attente',
    'state_canceled' => 'Annulée',
    'state_provisional' => 'Provisoire',
    'state_suspended' => 'Suspendue',
    'state_waiting_approval' => 'En attente d\'approbation',
    'state_expired' => 'Expirée',
    'state_pending_validation' => 'En attente de validation',
    'state_pending_technical_director_approval' => 'En attente d\'approbation du directeur technique',

    // Payment status
    'payment_status' => 'Statut du paiement',
    'payment_status_paid' => 'Payé',
    'payment_status_pending_payment' => 'En attente de paiement',
    'payment_status_no_document' => 'Aucun document',

    // Filter labels
    'filters' => [
        'first_name' => 'Prénom',
        'surname' => 'Nom de famille',
        'member_number' => 'Numéro de membre',
        'sport' => 'Sport',
        'entity_name' => 'Entité',
    ],

    // Separated license purchase page titles and subtitles
    'Purchase Sport Club License' => 'Acheter une licence de club sportif',
    'Purchase a sport license for your club' => 'Achetez une licence sportive pour votre club',
    'Purchase Sport Licenses' => 'Acheter des licences sportives',
    'Select members and purchase sport licenses on their behalf' => 'Sélectionnez des membres et achetez des licences sportives en leur nom',
    'Purchase International Entity License' => "Acheter une licence d'entité {$internationalName}",
    'Purchase an international license for your entity' => "Achetez une licence {$internationalShortName} pour votre entité",
    'Purchase International Professional Licenses' => "Acheter des licences professionnelles {$internationalName}",
    'Select members and purchase international licenses on their behalf' => "Sélectionnez des membres et achetez des licences {$internationalShortName} en leur nom",
    'Purchase Scientific Entity License' => 'Acheter une licence d\'entité scientifique',
    'Purchase a scientific license for your entity' => 'Achetez une licence scientifique pour votre entité',
    'Purchase Scientific Professional Licenses' => 'Acheter des licences professionnelles scientifiques',
    'Select members and purchase scientific licenses on their behalf' => 'Sélectionnez des membres et achetez des licences scientifiques en leur nom',
    'Purchase Primary Diving Services Licenses' => "Acheter des licences de services de plongée {$primaryShortName}",
    'Select members and purchase primary diving licenses on their behalf' => "Sélectionnez des membres et achetez des licences de plongée {$primaryShortName} en leur nom",

    // Generic, committee-label-driven fallbacks (used when a committee declares
    // no purchase title/subtitle of its own in config/committees.php).
    'Purchase :committee Entity License' => 'Acheter une licence d\'entité :committee',
    'Purchase a :committee license for your entity' => 'Achetez une licence :committee pour votre entité',
    'Purchase :committee Licenses' => 'Acheter des licences :committee',
    'Select members and purchase :committee licenses on their behalf' => 'Sélectionnez des membres et achetez des licences :committee en leur nom',
    ':committee Entity Licenses' => 'Licences d\'entité :committee',
    ':committee Professional Licenses' => 'Licences professionnelles :committee',

    // Individual separated license purchase page titles
    'individual_sport_license_title' => 'Licences professionnelles sportives',
    'individual_sport_license_subtitle' => 'Achetez des licences pour les arbitres et les entraîneurs',
    'individual_national_diving_license_title' => "Licence professionnelle de plongée {$primaryShortName}",
    'individual_national_diving_license_subtitle' => "Achetez une licence professionnelle de plongée {$primaryShortName}",
    'individual_cmas_diving_license_title' => "Licence professionnelle de plongée loisir {$internationalShortName}",
    'individual_cmas_diving_license_subtitle' => "Achetez une licence professionnelle de plongée loisir {$internationalShortName}",
    'individual_scientific_license_title' => "Licence professionnelle de plongée scientifique {$internationalShortName}",
    'individual_scientific_license_subtitle' => "Achetez une licence professionnelle de plongée scientifique {$internationalShortName}",

    // Individual separated licenses attributed page titles
    'individual_sport_licenses_title' => 'Licences sportives',
    'individual_sport_licenses_subtitle' => 'Vos licences sportives pour les athlètes, entraîneurs et officiels techniques',
    'individual_national_diving_licenses_title' => 'Licences professionnelles de plongée',
    'individual_national_diving_licenses_subtitle' => 'Vos licences professionnelles de plongée',
    'individual_national_diving_licenses_info' => 'Vous pouvez consulter ici vos licences professionnelles de plongée et en acheter de nouvelles',
    'individual_cmas_diving_licenses_title' => "Licences {$internationalName}",
    'individual_cmas_diving_licenses_subtitle' => '',
    'individual_cmas_diving_licenses_info' => "Vous pouvez consulter ici toutes vos licences professionnelles annuelles de plongée {$internationalName}",
    'individual_scientific_licenses_title' => "Licences {$internationalName}",
    'individual_scientific_licenses_subtitle' => '',
    'individual_scientific_licenses_info' => "Vous pouvez consulter ici toutes vos licences professionnelles annuelles de plongée {$internationalName}",

    // Other individual license translations
    'individual_licenses_info' => 'Vous pouvez consulter ici toutes vos licences pour les athlètes, entraîneurs et officiels techniques',
    'sport' => 'Sport',
    'category' => 'Catégorie',

    // Federation separated licenses attributed page titles
    'federation_sport_entity_licenses_title' => 'Licences de club sportif',
    'federation_sport_entity_licenses_subtitle' => 'Licences sportives attribuées aux clubs',
    'federation_sport_individual_licenses_title' => 'Licences sportives individuelles',
    'federation_sport_individual_licenses_subtitle' => 'Licences sportives attribuées aux athlètes et aux entraîneurs',
    'federation_national_diving_entity_licenses_title' => "Licences de centre de plongée {$primaryShortName}",
    'federation_national_diving_entity_licenses_subtitle' => "Licences de plongée {$primaryShortName} attribuées aux centres de plongée",
    'federation_national_diving_individual_licenses_title' => "Licences professionnelles de plongée {$primaryShortName}",
    'federation_national_diving_individual_licenses_subtitle' => "Licences de plongée {$primaryShortName} attribuées aux professionnels",
    'federation_cmas_diving_entity_licenses_title' => 'Licences de centre de plongée internationales',
    'federation_cmas_diving_entity_licenses_subtitle' => 'Licences de plongée internationales attribuées aux centres de plongée',
    'federation_cmas_diving_individual_licenses_title' => 'Licences professionnelles de plongée internationales',
    'federation_cmas_diving_individual_licenses_subtitle' => 'Licences de plongée internationales attribuées aux professionnels',
    'federation_scientific_entity_licenses_title' => 'Licences de centre de plongée scientifiques',
    'federation_scientific_entity_licenses_subtitle' => 'Licences de plongée scientifiques attribuées aux centres de plongée',
    'federation_scientific_individual_licenses_title' => 'Licences professionnelles de plongée scientifiques',
    'federation_scientific_individual_licenses_subtitle' => 'Licences de plongée scientifiques attribuées aux professionnels',

    // Admin separated licenses attributed page titles
    'admin_sport_entity_licenses_title' => 'Licences de club sportif',
    'admin_sport_entity_licenses_subtitle' => 'Toutes les licences sportives attribuées aux clubs',
    'admin_sport_individual_licenses_title' => 'Licences sportives individuelles',
    'admin_sport_individual_licenses_subtitle' => 'Toutes les licences sportives attribuées aux athlètes et aux entraîneurs',
    'admin_national_diving_entity_licenses_title' => "Licences de centre de plongée {$primaryShortName}",
    'admin_national_diving_entity_licenses_subtitle' => "Toutes les licences de plongée {$primaryShortName} attribuées aux centres de plongée",
    'admin_national_diving_individual_licenses_title' => "Licences professionnelles de plongée {$primaryShortName}",
    'admin_national_diving_individual_licenses_subtitle' => "Toutes les licences de plongée {$primaryShortName} attribuées aux professionnels",
    'admin_cmas_diving_entity_licenses_title' => 'Licences d\'entité internationales',
    'admin_cmas_diving_entity_licenses_subtitle' => 'Toutes les licences internationales attribuées aux entités',
    'admin_cmas_diving_individual_licenses_title' => 'Licences professionnelles de plongée loisir',
    'admin_cmas_diving_individual_licenses_subtitle' => 'Toutes les licences attribuées aux professionnels de la plongée loisir',
    'admin_scientific_entity_licenses_title' => 'Licences d\'entité scientifiques',
    'admin_scientific_entity_licenses_subtitle' => 'Toutes les licences de plongée scientifiques attribuées aux entités',
    'admin_scientific_individual_licenses_title' => 'Licences professionnelles de plongée scientifiques',
    'admin_scientific_individual_licenses_subtitle' => 'Toutes les licences de plongée scientifiques attribuées aux professionnels',

    // Committee names (for translation)
    'Technical Committee' => 'Commission technique',
    'Scientific Committee' => 'Commission scientifique',

    // International license field
    'is_international_label' => "Licence {$internationalName}",
    'is_international_help' => "Si vous cochez cette option, cette licence ne sera disponible que pour les moniteurs/encadrants et les entités {$internationalName}.",

    // International licenses page
    'international_licenses' => 'Licences internationales',
    'cmas_international_licenses' => 'Licences internationales',
    'international_licenses_description' => 'Vos licences internationales reconnues dans le monde entier',
    'view_national_licenses' => 'Voir les licences nationales',
    'purchase_international_license' => 'Acheter une licence internationale',
    'license' => 'Licence',
    'federation' => 'Fédération',
    'sport_category' => 'Sport/Catégorie',
    'validity' => 'Validité',
    'international_code' => 'Code international',
    'active' => 'Active',
    'pending' => 'En attente',
    'cancelled' => 'Annulée',
    'unknown' => 'Inconnu',
    'view' => 'Voir',
    'documents' => 'Documents',
    'no_international_licenses' => 'Aucune licence internationale',
    'no_international_licenses_message' => 'Vous n\'avez encore acheté aucune licence internationale.',

    // License purchase success page
    'License Purchase Initiated!' => 'Achat de licence initié !',
    'Your license purchase is being processed. You will receive a confirmation once payment is complete.' => 'Votre achat de licence est en cours de traitement. Vous recevrez une confirmation dès que le paiement sera effectué.',
    'You can view and manage your license in the My Licenses section' => 'Vous pouvez consulter et gérer votre licence dans la section Mes licences',
    'Payment Required' => 'Paiement requis',
    'Your license is pending payment to be activated' => 'Votre licence est en attente de paiement pour être activée',
    'Please complete the payment to activate your license and download the certificate' => 'Veuillez effectuer le paiement pour activer votre licence et télécharger le certificat',
    'An invoice has been generated and is available for download' => 'Une facture a été générée et est disponible au téléchargement',
    'Pending Payment' => 'En attente de paiement',
    'Complete Payment' => 'Effectuer le paiement',
    'Payment integration coming soon' => 'Intégration du paiement bientôt disponible',

    // DIVINGSERVICES certification requirement
    'active_diving_certification_required' => 'Certification de plongée active requise',
    'active_diving_certification_required_description' => 'Vous devez disposer d\'une certification professionnelle de plongée active pour demander une licence professionnelle de plongée.',

    // License detail page actions
    'pending_payment_message' => 'La licence est en attente de confirmation du paiement. Elle sera activée automatiquement dès que le paiement sera traité.',
    'waiting_approval_message' => 'La licence est en attente d\'approbation.',
    'provisional_message' => 'La licence est provisoire et peut être activée.',
    'manually_activate' => 'Activer manuellement la licence',
    'cancel_license' => 'Annuler la licence',
    'suspend_license' => 'Suspendre la licence',
    'reactivate_license' => 'Réactiver la licence',
    'approve_license' => 'Approuver la licence',
    'reject_license' => 'Rejeter la licence',
    'activate_provisional' => 'Activer la licence provisoire',
    'confirm_manual_activate' => 'Êtes-vous sûr de vouloir activer manuellement cette licence ?',
    'confirm_cancel' => 'Êtes-vous sûr de vouloir annuler cette licence ?',
    'confirm_suspend' => 'Êtes-vous sûr de vouloir suspendre cette licence ?',
    'confirm_reactivate' => 'Êtes-vous sûr de vouloir réactiver cette licence ?',
    'confirm_approve' => 'Êtes-vous sûr de vouloir approuver cette licence ?',
    'confirm_reject' => 'Êtes-vous sûr de vouloir rejeter cette licence ?',
    'confirm_activate_provisional' => 'Êtes-vous sûr de vouloir activer cette licence provisoire ?',
    'confirm_validate_approve' => 'Êtes-vous sûr de vouloir valider et approuver cette licence ?',
    'confirm_reject_validation' => 'Êtes-vous sûr de vouloir rejeter la validation de cette licence ?',
];
