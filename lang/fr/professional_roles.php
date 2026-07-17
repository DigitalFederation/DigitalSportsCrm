<?php

return [
    'title' => 'Rôles professionnels',
    'create' => 'Créer un rôle professionnel',
    'edit' => 'Modifier le rôle professionnel',
    'edit_title' => 'Modifier le rôle',
    'information_title' => 'Informations',
    'update' => 'Mettre à jour le rôle professionnel',

    // Fields
    'name' => 'Nom',
    'code' => 'Code',
    'role_type' => 'Type de rôle',
    'committee' => 'Comité',
    'committee_all' => 'Tous',

    // Hints
    'code_hint' => 'Identifiant unique en majuscules (par ex. FINSWIMMINGCOACH)',
    'role_type_hint' => 'Catégorie à laquelle appartient ce rôle (affecte l\'admissibilité à l\'inscription aux événements)',

    // Filters
    'filter_by_name' => 'Filtrer par nom...',

    // Info boxes
    'info_title' => 'Informations sur le rôle professionnel',
    'info_body' => 'Les rôles professionnels définissent les activités qu\'une personne peut exercer (par ex. entraîneur, arbitre, juge). Le type de rôle détermine la manière dont le système traite ce rôle pour l\'inscription aux événements et les vérifications d\'admissibilité.',
    'edit_info_title' => 'Informations',
    'edit_info_body' => 'Les rôles professionnels définissent les activités qu\'une personne peut exercer au sein de la fédération. Le type de rôle détermine l\'admissibilité aux événements : ATHLETE pour les compétiteurs, COACH pour les entraîneurs d\'équipe, TECHNICAL_OFFICIAL pour les arbitres et juges, INSTRUCTOR pour les moniteurs de cours, DIVER pour les plongeurs de loisir/professionnels, STAFF pour le soutien opérationnel de la fédération, et FEDERATION_STAFF pour les rôles administratifs de la fédération.',

    // Success messages
    'created_successfully' => 'Rôle professionnel créé avec succès.',
    'updated_successfully' => 'Rôle professionnel mis à jour avec succès.',
    'deleted_successfully' => 'Rôle professionnel supprimé avec succès.',
    'role_assigned_successfully' => 'Rôle professionnel attribué avec succès.',
    'role_removed_successfully' => 'Rôle professionnel retiré avec succès.',

    // Error messages
    'cannot_delete_has_individuals' => 'Impossible de supprimer ce rôle professionnel car il est attribué à des personnes.',
    'cannot_delete_has_certifications' => 'Impossible de supprimer ce rôle professionnel car il est lié à des certifications.',
    'cannot_delete_has_licenses' => 'Impossible de supprimer ce rôle professionnel car il est lié à des licences.',
    'delete_failed' => 'Échec de la suppression du rôle professionnel. Veuillez réessayer.',

    // Role types
    'role_types' => [
        'ATHLETE' => 'Athlète',
        'COACH' => 'Entraîneur',
        'TECHNICAL_OFFICIAL' => 'Officiel technique',
        'INSTRUCTOR' => 'Moniteur',
        'LEADER' => 'Guide',
        'DIVER' => 'Plongeur',
        'STAFF' => 'Personnel',
        'DIVINGPROFESSIONAL' => 'Professionnel de la plongée',
        'FEDERATION_STAFF' => 'Personnel de la fédération',
    ],

    // Actions
    'manage_roles' => 'Gérer les rôles',
];
