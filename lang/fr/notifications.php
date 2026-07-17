<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'entity_created' => [
        'subject' => 'Bienvenue sur :app',
        'greeting' => 'Bonjour, :name !',
        'line1' => 'Un compte a été créé pour votre entité.',
        'line2' => 'Pour gérer le profil de votre entité et découvrir les fonctionnalités de notre plateforme, veuillez définir votre mot de passe.',
        'action' => 'Définir votre mot de passe',
        'line3' => 'Une fois votre mot de passe défini, vous aurez un accès complet à votre tableau de bord.',
        'line4' => 'Nous nous réjouissons de votre participation active.',
        'line5' => 'Merci de faire partie de :app. Si vous avez des questions, n\'hésitez pas à nous contacter.',
        'salutation' => 'Cordialement, L\'équipe :app',
    ],

    'welcome_email' => [
        'title' => 'E-mail de bienvenue',
        'user_email' => 'E-mail de l\'utilisateur',
        'sent_status' => 'Envoyé',
        'not_sent_status' => 'Non envoyé',
        'send_button' => 'Envoyer l\'e-mail de bienvenue',
        'resend_button' => 'Renvoyer l\'e-mail de bienvenue',
        'confirm_send' => 'Êtes-vous sûr de vouloir envoyer l\'e-mail de bienvenue ?',
        'description' => 'Cet e-mail contient un lien permettant à l\'utilisateur de définir son mot de passe et d\'activer son compte.',
        'sent' => 'E-mail de bienvenue envoyé avec succès.',
        'failed' => 'Échec de l\'envoi de l\'e-mail de bienvenue.',
        'no_user' => 'Aucun compte utilisateur associé.',
    ],

    // Payment notifications
    'payment_made' => 'Un paiement de :value a été effectué.',

    // Event notifications
    'event_enrollment_confirmed' => 'Votre inscription à l\'événement a été confirmée.',
    'event_registration_confirmed' => 'Votre enregistrement à l\'événement a été confirmé.',

    // Request notifications
    'request_approved' => 'Votre demande d\'adhésion à :federation a été approuvée.',
    'federation_request_approved' => "Votre demande d\'adhésion à {$portalName} a été approuvée.",
    'association_request_accepted' => 'Demande d\'association acceptée avec succès.',
    'error_accepting_request' => 'Erreur lors de l\'acceptation de la demande.',
    'request_join_accepted' => 'La demande d\'adhésion de :name a été acceptée.',
    'request_rejected' => 'Demande individuelle rejetée avec succès.',
    'error_rejecting_request' => 'Échec du rejet de la demande individuelle.',
    'request_deleted' => 'Demande individuelle supprimée avec succès.',

    // Document notifications
    'document_created' => [
        'subject' => 'Notification de création de document',
        'greeting' => 'Notification',
        'line' => "Le document :invoice est disponible sur {$portalName}. Cliquez sur le bouton ci-dessous pour accéder à {$portalName}, où vous pourrez vérifier le statut du document dans le menu Paiements.",
        'action' => 'Ouvrir le document',
    ],

    'admin_license_attributed' => [
        'subject' => 'Nouvelle licence demandée',
        'greeting' => 'Notification',
        'line_intro' => 'Une nouvelle licence a été demandée.',
        'line_license' => '**Nom de la licence :** :name',
        'line_holder' => '**Nom du titulaire :** :holder',
        'line_federation' => '**Nom de la fédération :** :federation',
        'action' => 'Voir les détails',
    ],

    'membership_create' => [
        'intro' => 'Une nouvelle adhésion a été attribuée. Elle deviendra active après confirmation du paiement.',
        'action' => 'Ouvrir l\'adhésion',
        'outro' => 'Merci d\'utiliser notre application !',
        'database' => 'Une nouvelle adhésion a été attribuée. Elle deviendra active après confirmation du paiement.',
    ],

    'entity_approval' => [
        'subject' => 'Approbation d\'entité requise',
        'greeting' => 'Bonjour :name,',
        'line_intro' => 'Une nouvelle entité est en attente de votre approbation.',
        'line_entity' => 'Nom de l\'entité : :entity',
        'action' => 'Voir l\'entité',
        'line_review' => 'Veuillez examiner les détails de l\'entité et poursuivre le processus d\'approbation.',
        'salutation_regards' => 'Cordialement,',
        'salutation_team' => 'L\'équipe :app',
        'database' => 'Une nouvelle entité requiert votre approbation.',
    ],

    'entity_member_accepted' => [
        'subject' => 'Nouveau membre accepté : :name',
        'greeting' => 'Bonjour !',
        'line_accepted' => ':name a accepté l\'invitation à devenir membre de :entity.',
        'line_active' => 'Ce membre est désormais actif au sein de votre entité.',
        'action' => 'Voir les membres',
        'salutation' => 'Cordialement,<br>L\'équipe :app',
        'database' => ':name a accepté l\'invitation à devenir membre.',
    ],

    'entity_member_invitation' => [
        'subject' => 'Invitation à devenir membre de :entity',
        'greeting' => 'Bonjour !',
        'line_invited' => ':inviter vous a invité à devenir membre de son entité.',
        'line_instructions' => 'Pour accepter cette invitation, connectez-vous à la plateforme et accédez à « Entités » dans le menu latéral.',
        'action' => 'Voir l\'invitation',
        'line_ignore' => 'Si vous n\'attendiez pas cette invitation, vous pouvez ignorer cet e-mail.',
        'salutation' => 'Cordialement,<br>L\'équipe :app',
        'database' => 'L\'entité :entity vous a invité à devenir membre.',
    ],

    'entity_request' => [
        'database_title' => 'Nouvelle demande d\'entité',
        'database_message' => 'Vous avez une nouvelle demande d\'adhésion de :name.',
    ],

    'export_ready' => [
        'line_intro' => 'Votre export est prêt à être téléchargé. Consultez votre e-mail pour obtenir le lien.',
        'action' => 'Télécharger l\'export',
        'database' => 'Votre export est prêt à être téléchargé.',
    ],

    'federation_join_request' => [
        'database' => ':name a demandé à rejoindre la fédération.',
    ],

    'individual_request_license' => [
        'line' => 'Il y a une nouvelle licence de type :type à approuver.',
        'database' => 'Il y a une nouvelle licence de type :type à approuver.',
    ],

    'instructor_new_certification' => [
        'line' => 'Il y a une nouvelle certification à approuver.',
        'action' => 'Ouvrir',
        'database' => 'Il y a une nouvelle certification à approuver.',
    ],

    'invite_individual_professional' => [
        'subject' => 'Invitation à devenir :role',
        'greeting' => 'Bonjour :name !',
        'line_invited' => 'Vous avez été invité à devenir :role de :entity.',
        'action' => 'Consulter l\'invitation',
        'line_thanks' => 'Merci de prendre en considération notre invitation !',
        'salutation' => 'Cordialement, :app',
        'database' => 'Vous avez été invité à devenir :role de :entity.',
    ],

    'membership_activation' => [
        'line_activated' => 'L\'adhésion :name a été activée avec succès.',
        'action' => 'Ouvrir l\'adhésion',
        'salutation' => $primaryShortName,
        'database' => 'L\'adhésion :name a été activée avec succès.',
    ],

    'membership_expiration' => [
        'line_expires' => 'Votre adhésion :name expirera le :date.',
        'action' => 'Ouvrir l\'adhésion',
        'outro' => 'Merci d\'utiliser notre application !',
    ],

    'official_document_activated' => [
        'database' => 'Le document :name a été approuvé.',
    ],

    'official_document_created' => [
        'database' => 'Le document officiel :name a été envoyé.',
    ],

    'official_document_deleted' => [
        'database' => 'Le document :name a été supprimé.',
    ],

    'report_generated' => [
        'line_ready' => 'Votre rapport est prêt.',
        'action' => 'Télécharger le rapport',
        'line_auth' => 'Vous devez être authentifié pour télécharger le rapport.',
        'database' => 'Le téléchargement de votre rapport est prêt. Cliquez ici pour le télécharger.',
    ],
];
