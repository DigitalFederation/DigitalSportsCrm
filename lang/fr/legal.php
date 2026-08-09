<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$federationName = $brand['name'];
$federationShortName = $brand['short_name'];
$portalName = $brand['portal_name'];
$internationalName = $internationalBrand['name'];

return [
    // Common
    'privacy_policy' => 'Politique de confidentialité',
    'privacy_policy_title' => 'POLITIQUE DE CONFIDENTIALITÉ',
    'terms_of_use' => 'Conditions d\'utilisation',
    'terms_of_use_title' => 'CONDITIONS D\'UTILISATION',
    'last_update' => 'Dernière mise à jour',
    'entity' => 'Entité',
    'address' => 'Adresse',
    'email' => 'E-mail',
    'contacts' => 'Contacts',
    'federation_full_name' => "{$federationName} ({$federationShortName})",

    // Privacy Policy
    'privacy' => [
        'responsible_entity' => 'Entité responsable',
        'responsible_entity_text' => "{$federationName} ({$federationShortName}) est l'entité responsable du traitement des données personnelles collectées via ce Portail. Les déploiements publics doivent adapter ce texte à la législation applicable en matière de protection des données et à la juridiction d'exploitation.",
        'dpo' => 'Délégué à la protection des données',
        'dpo_department' => 'Département administratif et financier',

        'legal_framework' => 'Cadre juridique',
        'legal_framework_intro' => "Le traitement des données personnelles par {$federationShortName} est régi par la législation suivante :",
        'gdpr_reference' => 'Règlement (UE) 2016/679 du Parlement européen et du Conseil (Règlement général sur la protection des données - RGPD)',
        'law_58_2019' => 'Loi nationale de mise en œuvre applicable en matière de protection des données, le cas échéant',
        'law_41_2004' => 'Loi applicable relative aux communications électroniques et à la vie privée, le cas échéant',

        'collected_data' => 'Données personnelles collectées',
        'collected_data_intro' => "Dans le cadre de ses activités, {$federationShortName} collecte et traite les catégories suivantes de données personnelles :",

        'identification_data' => 'Données d\'identification',
        'full_name' => 'Nom complet',
        'birth_date' => 'Date de naissance',
        'gender' => 'Sexe',
        'nationality' => 'Nationalité',
        'tax_number' => 'Numéro d\'identification fiscale (NIF)',
        'id_document' => 'Numéro et type de pièce d\'identité',
        'photo' => 'Photographie',

        'contact_data' => 'Données de contact',
        'full_address' => 'Adresse complète',
        'email_address' => 'Adresse e-mail',
        'phone_number' => 'Numéro de téléphone/portable',

        'sports_data' => 'Données sportives',
        'certifications_brevets' => 'Certifications et brevets obtenus',
        'federative_licenses' => 'Licences fédérales',
        'entity_affiliations' => 'Affiliations à des entités (clubs, écoles, centres de plongée)',
        'event_participation' => 'Participation à des événements et compétitions',
        'sports_results' => 'Résultats sportifs',

        'health_data' => 'Données de santé (catégorie particulière)',
        'health_data_text' => 'Aux fins de la délivrance de licences sportives et d\'assurances, il peut être nécessaire de traiter des données relatives à l\'aptitude médicale à la pratique sportive. Ces données sont traitées avec des mesures de sécurité renforcées et uniquement avec le consentement explicite de la personne concernée.',

        'processing_purposes' => 'Finalités du traitement',
        'processing_purposes_intro' => 'Les données personnelles sont traitées aux fins suivantes :',
        'purpose_member_management' => 'Inscription et gestion des membres individuels et des entités affiliées',
        'purpose_license_management' => 'Délivrance, renouvellement et gestion des licences fédérales',
        'purpose_certification_management' => 'Délivrance et gestion des certifications et brevets de plongée',
        'purpose_event_management' => 'Organisation et gestion des événements, compétitions et formations',
        'purpose_insurance_management' => 'Souscription et gestion des assurances sportives',
        'purpose_institutional_communication' => 'Communication institutionnelle et promotion des activités',
        'purpose_legal_obligations' => 'Respect des obligations légales et réglementaires',
        'purpose_statistics' => 'Élaboration de statistiques anonymisées',

        'legal_basis' => 'Base juridique',
        'legal_basis_intro' => "Le traitement des données personnelles par {$federationShortName} repose sur les bases juridiques suivantes :",
        'consent' => 'Consentement',
        'consent_text' => 'Lorsque la personne concernée donne son consentement au traitement pour une ou plusieurs finalités spécifiques (Art. 6, par. 1, point a) du RGPD)',
        'contract_execution' => 'Exécution d\'un contrat',
        'contract_execution_text' => 'Lorsque le traitement est nécessaire à l\'exécution d\'un contrat auquel la personne concernée est partie, tel que l\'adhésion à la fédération (Art. 6, par. 1, point b) du RGPD)',
        'legal_obligation' => 'Obligation légale',
        'legal_obligation_text' => "Lorsque le traitement est nécessaire au respect d'une obligation légale à laquelle {$federationShortName} est soumis (Art. 6, par. 1, point c) du RGPD)",
        'legitimate_interest' => 'Intérêt légitime',
        'legitimate_interest_text' => "Lorsque le traitement est nécessaire aux fins des intérêts légitimes poursuivis par {$federationShortName}, à condition que ces intérêts ne prévalent pas sur les intérêts ou les libertés et droits fondamentaux de la personne concernée (Art. 6, par. 1, point f) du RGPD)",

        'data_sharing' => 'Partage des données',
        'data_sharing_intro' => 'Les données personnelles peuvent être partagées avec les entités suivantes, lorsque cela est nécessaire aux finalités indiquées :',
        'cmas' => $internationalName,
        'cmas_reason' => 'pour la délivrance de certifications internationales',
        'public_sports_authority' => 'Autorité publique compétente en matière de sport',
        'public_sports_authority_reason' => 'pour le respect des obligations légales',
        'cop' => 'Comité olympique ou sportif national, le cas échéant',
        'cop_reason' => 'dans le cadre des activités fédérales',
        'insurers' => 'Compagnies d\'assurance',
        'insurers_reason' => 'pour la souscription d\'assurances sportives',
        'affiliated_entities' => 'Entités affiliées (clubs, écoles, centres de plongée)',
        'affiliated_entities_reason' => 'pour la gestion des membres',
        'public_authorities' => 'Autorités publiques',
        'public_authorities_reason' => 'lorsque la loi l\'exige',
        'data_sharing_compliance' => "{$federationShortName} exige de toutes les entités avec lesquelles il partage des données qu'elles respectent les obligations applicables en matière de protection des données.",

        // Public disclosure of professional members
        'public_disclosure' => 'Divulgation publique des données des membres professionnels',
        'public_disclosure_intro' => "Dans le cadre de ses fonctions de fédération sportive et à des fins de transparence et de vérification publique des qualifications professionnelles, {$federationShortName} peut publier sur les pages publiques du Portail certaines données personnelles des membres individuels titulaires de licences ou de certifications professionnelles :",
        'public_disclosure_photo' => 'Photographie',
        'public_disclosure_name' => 'Nom complet',
        'public_disclosure_birth_date' => 'Date de naissance',
        'public_disclosure_entity' => 'Entité affiliée (club/école/centre de plongée)',
        'public_disclosure_license_status' => 'Statut de la licence professionnelle',
        'public_disclosure_mandatory' => 'Cette publication est une condition nécessaire à la délivrance et au maintien des licences professionnelles, avec les bases juridiques suivantes :',
        'public_disclosure_contract' => 'Exécution du contrat d\'affiliation et de licence professionnelle (Art. 6, par. 1, point b) du RGPD)',
        'public_disclosure_legal_obligation' => 'Respect des obligations légales applicables, le cas échéant (Art. 6, par. 1, point c) du RGPD)',
        'public_disclosure_legitimate_interest' => "L'intérêt légitime de {$federationShortName} à promouvoir la transparence et à permettre la vérification publique des qualifications professionnelles (Art. 6, par. 1, point f) du RGPD)",
        'public_disclosure_no_removal' => 'La publication de ces données est obligatoire pour tous les titulaires de licences professionnelles et il n\'est pas possible de demander leur suppression tant que la licence est active.',

        'international_transfers' => 'Transferts internationaux',
        'international_transfers_text' => "Certaines données peuvent être transférées en dehors de l'Espace économique européen, notamment vers {$internationalName} pour la délivrance de certifications internationales. Les déploiements publics doivent configurer des garanties appropriées pour leur juridiction d'exploitation.",

        'retention_period' => 'Durée de conservation',
        'retention_period_intro' => 'Les données personnelles sont conservées pendant la durée nécessaire aux finalités pour lesquelles elles ont été collectées :',
        'active_member_data' => 'Données des membres actifs',
        'active_member_data_text' => 'pendant la période d\'adhésion et pour la durée légalement requise après la fin de celle-ci',
        'legal_obligation_data' => 'Données nécessaires au respect des obligations légales',
        'legal_obligation_data_text' => 'pendant la durée légalement établie',
        'financial_data' => 'Données financières et fiscales',
        'financial_data_text' => 'pendant la durée requise par la législation fiscale et comptable applicable',

        'data_subject_rights' => 'Droits des personnes concernées',
        'data_subject_rights_intro' => 'En vertu du RGPD, les personnes concernées disposent des droits suivants :',
        'right_access' => 'Droit d\'accès',
        'right_access_text' => 'Droit d\'obtenir la confirmation que vos données sont traitées et, le cas échéant, d\'y accéder',
        'right_rectification' => 'Droit de rectification',
        'right_rectification_text' => 'Droit de demander la rectification des données inexactes ou incomplètes',
        'right_erasure' => 'Droit à l\'effacement',
        'right_erasure_text' => 'Droit de demander l\'effacement des données, le cas échéant',
        'right_portability' => 'Droit à la portabilité',
        'right_portability_text' => 'Droit de recevoir les données dans un format structuré et couramment utilisé',
        'right_objection' => 'Droit d\'opposition',
        'right_objection_text' => 'Droit de s\'opposer au traitement des données dans certaines circonstances',
        'right_restriction' => 'Droit à la limitation',
        'right_restriction_text' => 'Droit de demander la limitation du traitement dans certaines circonstances',
        'right_withdraw_consent' => 'Droit de retirer le consentement',
        'right_withdraw_consent_text' => 'Lorsque le traitement est fondé sur le consentement, la personne concernée peut le retirer à tout moment',
        'exercise_rights_text' => 'Pour exercer l\'un de ces droits, contactez-nous via l\'adresse e-mail de contact configurée ou par courrier à l\'adresse indiquée.',

        'data_security' => 'Sécurité des données',
        'data_security_text' => "{$federationShortName} met en œuvre des mesures techniques et organisationnelles appropriées pour protéger les données personnelles contre la destruction accidentelle ou illicite, la perte, l'altération, la divulgation ou l'accès non autorisés. Ces mesures peuvent inclure le chiffrement des données, des contrôles d'accès, des sauvegardes régulières et la formation du personnel.",

        'cookies' => 'Cookies',
        'cookies_text' => 'Ce Portail utilise des cookies pour améliorer l\'expérience utilisateur et assurer le bon fonctionnement des services. Pour plus d\'informations sur les cookies utilisés, veuillez consulter notre Politique en matière de cookies.',

        'complaints' => 'Réclamations',
        'complaints_intro' => 'Sans préjudice de tout autre recours administratif ou judiciaire, la personne concernée a le droit d\'introduire une réclamation auprès de l\'autorité de contrôle compétente :',
        'cnpd' => 'Commission nationale de protection des données (CNPD)',

        'policy_changes' => 'Modifications de la politique',
        'policy_changes_text' => "{$federationShortName} peut modifier la présente Politique de confidentialité. Les modifications seront publiées sur ce Portail et, lorsqu'elles sont importantes, communiquées aux personnes concernées par e-mail lorsque la loi l'exige.",

        'contacts_intro' => 'Pour toute question relative à la protection des données personnelles, contactez-nous :',
    ],

    // Terms of Use
    'terms' => [
        'general_provisions' => 'Dispositions générales',
        'general_provisions_text' => "Les présentes Conditions d'utilisation régissent l'accès à {$portalName} et son utilisation, exploité par {$federationName} ({$federationShortName}). En accédant à ce Portail et en l'utilisant, l'utilisateur accepte les présentes Conditions d'utilisation.",

        'definitions' => 'Définitions',
        'portal' => 'Portail',
        'portal_definition' => "Plateforme numérique de {$federationShortName} accessible via internet",
        'user' => 'Utilisateur',
        'user_definition' => 'Toute personne qui accède au Portail',
        'member' => 'Membre',
        'member_definition' => "Personne inscrite auprès de {$federationShortName}",
        'entity_definition' => "Organisation affiliée à {$federationShortName}",
        'services' => 'Services',
        'services_definition' => 'Ensemble des fonctionnalités mises à disposition via le Portail',

        'acceptance' => 'Acceptation des conditions',
        'acceptance_text' => "L'utilisation de ce Portail implique l'acceptation des présentes Conditions d'utilisation. Si vous n'acceptez pas ces conditions, vous devez vous abstenir d'utiliser le Portail. {$federationShortName} peut modifier ces Conditions, les modifications prenant effet après leur publication sur le Portail.",

        'services_description' => 'Description des services',
        'services_description_intro' => "Le Portail {$portalName} fournit les services suivants :",
        'service_profile_management' => 'Inscription et gestion des profils des membres et des entités',
        'service_license_acquisition' => 'Acquisition et renouvellement des licences fédérales',
        'service_certification_management' => 'Gestion des certifications et brevets de plongée',
        'service_event_registration' => 'Inscription aux événements, compétitions et formations',
        'service_document_access' => 'Accès et téléchargement des documents officiels',
        'service_payment_processing' => 'Traitement des paiements',
        'service_insurance_management' => 'Gestion des assurances sportives',
        'service_institutional_info' => 'Consultation des informations institutionnelles',

        'user_registration' => 'Inscription de l\'utilisateur',
        'user_registration_intro' => 'Pour accéder à certaines fonctionnalités du Portail, une inscription est requise. En s\'inscrivant, l\'utilisateur s\'engage à :',
        'registration_true_info' => 'Fournir des informations véridiques, exactes, à jour et complètes',
        'registration_keep_updated' => 'Maintenir ses données à jour',
        'registration_credentials' => 'Préserver la confidentialité de ses identifiants d\'accès',
        'registration_notify' => "Notifier immédiatement {$federationShortName} en cas d'utilisation non autorisée de son compte",

        // Public disclosure of professional members
        'public_disclosure' => 'Divulgation publique des données des membres professionnels',
        'public_disclosure_intro' => "En acquérant une licence ou une certification professionnelle, l'utilisateur reconnaît que {$federationShortName} peut publier sur les pages publiques du Portail certaines données nécessaires à la vérification publique :",
        'public_disclosure_photo' => 'Photographie',
        'public_disclosure_name' => 'Nom complet',
        'public_disclosure_birth_date' => 'Date de naissance',
        'public_disclosure_entity' => 'Entité affiliée',
        'public_disclosure_license_status' => 'Statut de la licence professionnelle',
        'public_disclosure_mandatory' => 'Cette publication est une condition obligatoire pour la délivrance et le maintien des licences professionnelles, et il n\'est pas possible de demander sa suppression tant que la licence est active.',
        'public_disclosure_purpose' => 'La publication vise à permettre la vérification publique des qualifications professionnelles des membres, contribuant à la sécurité et à la transparence dans le secteur des activités subaquatiques et du sport fédéral.',

        'user_obligations' => 'Obligations de l\'utilisateur',
        'user_obligations_intro' => 'L\'utilisateur s\'engage à :',
        'obligation_lawful_use' => 'Utiliser le Portail conformément à la loi et aux présentes Conditions',
        'obligation_true_info' => 'Fournir des informations véridiques et à jour',
        'obligation_respect_ip' => 'Respecter les droits de propriété intellectuelle',
        'obligation_security' => 'Ne pas compromettre la sécurité du Portail',
        'obligation_no_illegal' => 'Ne pas utiliser le Portail à des fins illégales ou préjudiciables',
        'obligation_no_harmful' => 'Ne pas transmettre de contenu illégal, diffamatoire ou offensant',

        'prohibited_conduct' => 'Comportements interdits',
        'prohibited_conduct_intro' => 'Il est expressément interdit de :',
        'prohibited_unauthorized_access' => 'Accéder à des zones restreintes sans autorisation',
        'prohibited_malware' => 'Introduire des virus, des logiciels malveillants ou tout code malveillant',
        'prohibited_interference' => 'Perturber le fonctionnement normal du Portail',
        'prohibited_bots' => 'Utiliser des robots, des robots d\'indexation ou des outils automatisés pour extraire des données',
        'prohibited_impersonation' => 'Usurper l\'identité d\'une autre personne ou entité',
        'prohibited_illegal_activities' => 'Utiliser le Portail pour des activités illégales',

        'intellectual_property' => 'Propriété intellectuelle',
        'intellectual_property_text' => "Tout le contenu propre au déploiement présent sur le Portail, y compris les textes, graphiques, logos, icônes, images, extraits audio et vidéo et compilations de données, est la propriété de {$federationShortName} ou de ses concédants de licence. Le projet logiciel lui-même est concédé sous licence conformément à la licence du dépôt.",
        'intellectual_property_license' => 'L\'utilisateur se voit accorder une licence limitée, non exclusive et non transférable pour accéder au Portail et l\'utiliser à des fins personnelles et non commerciales, sous réserve du respect des présentes Conditions d\'utilisation.',

        'payments' => 'Paiements',
        'payments_intro' => 'Certains services mis à disposition via le Portail sont soumis à paiement :',
        'payments_prices' => 'Les prix sont ceux indiqués sur le Portail au moment de la transaction, y compris les taxes applicables lorsqu\'elles sont configurées',
        'payments_methods' => 'Les moyens de paiement acceptés sont ceux indiqués sur le Portail',
        'payments_confirmation' => 'Après confirmation du paiement, un reçu sera émis par e-mail',
        'payments_refunds' => 'La politique de remboursement applicable est celle indiquée pour chaque type de service',

        'liability_limitation' => 'Limitation de responsabilité',
        'liability_limitation_intro' => "{$federationShortName} ne saurait être tenu responsable de :",
        'liability_interruptions' => 'Interruptions ou défaillances du fonctionnement du Portail',
        'liability_errors' => 'Erreurs ou omissions dans le contenu du Portail',
        'liability_third_party' => 'Dommages causés par des tiers ou par une utilisation inappropriée',
        'liability_force_majeure' => 'Cas de force majeure ou cas fortuits',

        'warranty_exclusion' => 'Exclusion de garantie',
        'warranty_exclusion_text' => "Le Portail est fourni « en l'état » et « selon disponibilité ». {$federationShortName} ne garantit pas que le Portail soit exempt d'erreurs, de virus ou d'autres composants nuisibles, ni qu'il fonctionnera sans interruption. Dans toute la mesure permise par la loi, {$federationShortName} exclut toute garantie, expresse ou implicite.",

        'indemnification' => 'Indemnisation',
        'indemnification_text' => "L'utilisateur s'engage à indemniser et à dégager de toute responsabilité {$federationShortName}, ses dirigeants, employés et représentants, de toute réclamation, tout dommage, toute perte ou toute dépense résultant de la violation des présentes Conditions ou d'une utilisation inappropriée du Portail.",

        'third_party_links' => 'Liens vers des tiers',
        'third_party_links_text' => "Le Portail peut contenir des liens vers des sites web de tiers. {$federationShortName} ne contrôle pas ces sites web et n'est pas responsable de leur contenu ni de leurs pratiques en matière de confidentialité. L'inclusion de liens n'implique aucune association, aucun parrainage ni aucune approbation.",

        'suspension_termination' => 'Suspension et résiliation',
        'suspension_termination_intro' => "{$federationShortName} peut suspendre ou résilier l'accès de tout utilisateur au Portail, sans préavis, dans les situations suivantes :",
        'suspension_terms_violation' => 'Violation des présentes Conditions d\'utilisation',
        'suspension_illegal_acts' => 'Commission d\'actes illégaux',
        'suspension_harmful_conduct' => "Comportement portant préjudice à {$federationShortName} ou à d'autres utilisateurs",
        'suspension_user_request' => 'À la demande de l\'utilisateur lui-même',

        'terms_changes' => 'Modifications des conditions',
        'terms_changes_text' => "{$federationShortName} peut modifier les présentes Conditions d'utilisation. Les modifications seront publiées sur le Portail et prendront effet immédiatement après leur publication. L'utilisation continue du Portail après la publication des modifications vaut acceptation de celles-ci.",

        'applicable_law' => 'Droit applicable',
        'applicable_law_text' => 'Les présentes Conditions d\'utilisation doivent être adaptées par chaque déploiement au droit et aux tribunaux de sa juridiction d\'exploitation.',

        'dispute_resolution' => 'Règlement des litiges',
        'dispute_resolution_text' => 'En cas de litige, les parties s\'engagent à rechercher une solution amiable avant de saisir les tribunaux. L\'utilisateur peut recourir aux mécanismes alternatifs de règlement des litiges disponibles, y compris la plateforme européenne de règlement en ligne des litiges (https://ec.europa.eu/consumers/odr).',

        'severability' => 'Divisibilité',
        'severability_text' => 'Si une disposition des présentes Conditions est jugée invalide ou inapplicable, les dispositions restantes conservent leur pleine force et effet.',

        'contacts_intro' => 'Pour toute question relative aux présentes Conditions d\'utilisation, contactez-nous :',
        'privacy_policy_reference' => 'Pour toute information sur le traitement de vos données personnelles, veuillez consulter notre Politique de confidentialité.',
    ],
];
