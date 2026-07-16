<?php

return [
    'title' => 'Gestion des permissions',
    'permission' => 'Permission',
    'permissions' => 'Permissions',
    'create_permission' => 'Créer une permission',
    'edit_permission' => 'Modifier la permission',
    'delete_permission' => 'Supprimer la permission',
    'permission_details' => 'Détails de la permission',
    'bulk_create' => 'Créer des permissions en masse',
    'import_permissions' => 'Importer des permissions',
    'export_permissions' => 'Exporter des permissions',

    // Form fields
    'name' => 'Nom de la permission',
    'name_help' => 'Utilisez des minuscules avec des traits d\'union (par ex. manage-users)',
    'display_name' => 'Nom d\'affichage',
    'description' => 'Description',
    'category' => 'Catégorie',
    'guard' => 'Guard',
    'guard_name' => 'Nom du guard',
    'roles_using' => 'Rôles utilisant cette permission',
    'routes_using' => 'Routes utilisant cette permission',
    'created_by' => 'Créé par',
    'created_at' => 'Créé le',
    'updated_at' => 'Mis à jour le',

    // Categories
    'uncategorized' => 'Non catégorisé',
    'all_categories' => 'Toutes les catégories',
    'select_category' => 'Sélectionner une catégorie',
    'new_category' => 'Nouvelle catégorie',

    // Filters
    'filter_by_category' => 'Filtrer par catégorie',
    'filter_by_usage' => 'Filtrer par utilisation',
    'has_roles' => 'A des rôles',
    'no_roles' => 'Aucun rôle',
    'search_permissions' => 'Rechercher des permissions...',

    // Statistics
    'total_permissions' => 'Nombre total de permissions',
    'system_permissions' => 'Permissions système',
    'custom_permissions' => 'Permissions personnalisées',
    'permissions_with_roles' => 'Permissions avec rôles',
    'unused_permissions' => 'Permissions inutilisées',
    'permissions_with_routes' => 'Permissions avec routes',

    // Actions
    'actions' => 'Actions',
    'view' => 'Voir',
    'edit' => 'Modifier',
    'delete' => 'Supprimer',
    'cancel' => 'Annuler',
    'save' => 'Enregistrer',
    'create' => 'Créer',
    'back_to_list' => 'Retour aux permissions',
    'confirm_delete' => 'Confirmer la suppression',

    // Bulk operations
    'bulk_operations' => 'Opérations groupées',
    'add_permission_line' => 'Ajouter une permission',
    'remove_line' => 'Supprimer',
    'default_category' => 'Catégorie par défaut',
    'default_guard' => 'Guard par défaut',
    'apply_defaults' => 'Appliquer les valeurs par défaut',

    // Import/Export
    'select_file' => 'Sélectionner un fichier CSV',
    'download_template' => 'Télécharger le modèle',
    'import' => 'Importer',
    'export' => 'Exporter',

    // Status
    'system_permission' => 'Permission système',
    'protected' => 'Protégée',
    'deletable' => 'Supprimable',
    'in_use' => 'Utilisée',
    'not_used' => 'Non utilisée',

    // Impact analysis
    'deletion_impact' => 'Impact de la suppression',
    'affected_roles' => 'Rôles affectés',
    'affected_users' => 'Utilisateurs affectés',
    'affected_routes' => 'Routes affectées',
    'roles_list' => 'Rôles ayant cette permission',

    // Messages
    'messages' => [
        'permission_created_successfully' => 'Permission créée avec succès.',
        'permission_updated_successfully' => 'Permission mise à jour avec succès.',
        'permission_deleted_successfully' => 'Permission supprimée avec succès.',
        'bulk_create_success' => ':count permissions créées avec succès.',
        'bulk_create_partial' => ':created permissions créées, :failed échouées.',
        'import_success' => 'Import terminé : :created créées, :skipped ignorées.',
        'no_permissions_found' => 'Aucune permission trouvée.',
        'no_permissions_added' => 'Aucune permission ajoutée pour le moment.',
        'confirm_delete_message' => 'Êtes-vous sûr de vouloir supprimer cette permission ? Cette action est irréversible.',
    ],

    // Errors
    'errors' => [
        'permission_already_exists' => 'Une permission portant ce nom existe déjà.',
        'cannot_modify_system_permission' => 'Les permissions système ne peuvent pas être modifiées.',
        'cannot_delete_system_permission' => 'Les permissions système ne peuvent pas être supprimées.',
        'permission_used_by_protected_roles' => 'Cette permission est utilisée par des rôles protégés : :roles',
        'permission_used_in_routes' => 'Cette permission est utilisée dans :count route(s).',
        'permission_creation_failed' => 'Échec de la création de la permission : :error',
        'permission_update_failed' => 'Échec de la mise à jour de la permission : :error',
        'permission_deletion_failed' => 'Échec de la suppression de la permission : :error',
        'bulk_create_failed' => 'Échec de la création groupée : :error',
        'import_failed' => 'Échec de l\'import : :error',
        'invalid_permission_name' => 'Le nom de la permission doit être en minuscules avec des traits d\'union uniquement.',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Le nom de la permission est requis.',
        'name_unique' => 'Ce nom de permission existe déjà.',
        'name_format' => 'Le nom de la permission doit être en minuscules avec des traits d\'union (par ex. manage-users).',
        'description_too_long' => 'La description ne peut pas dépasser 1000 caractères.',
        'category_too_long' => 'La catégorie ne peut pas dépasser 100 caractères.',
    ],

    // Help text
    'help' => [
        'naming_convention' => 'Utilisez des lettres minuscules avec des traits d\'union entre les mots (par ex. manage-users, view-reports)',
        'categories' => 'Les catégories aident à organiser les permissions. Catégories courantes : Utilisateurs, Rôles, Contenu, Paramètres, Rapports',
        'system_permissions' => 'Les permissions système sont des permissions essentielles qui ne peuvent pas être modifiées ni supprimées.',
        'bulk_create' => 'Créez plusieurs permissions à la fois. Chaque ligne créera une nouvelle permission.',
        'import_format' => 'Format CSV : name, category, description, guard_name',
        'guard_cannot_be_changed' => 'Le guard ne peut pas être modifié après la création pour des raisons de sécurité.',
    ],
];
