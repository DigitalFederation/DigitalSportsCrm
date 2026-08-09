<?php

return [
    'title' => 'Gestion des permissions de routes',
    'route_permissions' => 'Permissions de routes',
    'scan_routes' => 'Analyser les routes',
    'assign_permissions' => 'Attribuer des permissions',
    'bulk_assign' => 'Attribution en masse',
    'routes_to_assign' => 'Routes auxquelles attribuer des permissions',
    'route_details' => 'Détails de la route',
    'permission_mapping' => 'Correspondance des permissions',

    // Route fields
    'route_name' => 'Nom de la route',
    'uri' => 'Motif d\'URI',
    'methods' => 'Méthodes HTTP',
    'controller' => 'Contrôleur',
    'middleware' => 'Middleware',
    'current_permission' => 'Permission actuelle',
    'assigned_permission' => 'Permission attribuée',
    'suggested_permissions' => 'Permissions suggérées',
    'module' => 'Module',
    'prefix' => 'Préfixe',
    'parameters' => 'Paramètres',
    'status' => 'Statut',
    'uncategorized' => 'Non catégorisé',

    // Filters
    'filter_by_module' => 'Filtrer par module',
    'all_modules' => 'Tous les modules',
    'all_permissions' => 'Toutes les permissions',
    'filter_by_prefix' => 'Filtrer par préfixe',
    'filter_by_permission' => 'Filtrer par statut de permission',
    'has_permission' => 'A une permission',
    'no_permission' => 'Aucune permission',
    'all_routes' => 'Toutes les routes',
    'search_routes' => 'Rechercher des routes...',

    // Statistics
    'total_routes' => 'Nombre total de routes',
    'routes_with_permissions' => 'Routes avec permissions',
    'routes_without_permissions' => 'Routes sans permissions',
    'percentage_protected' => 'Couverture de protection',
    'dynamic_mappings' => 'Correspondances dynamiques',
    'active_mappings' => 'Correspondances actives',

    // Actions
    'scan' => 'Analyser',
    'assign' => 'Attribuer',
    'assign_permission' => 'Attribuer une permission',
    'edit_permission_assignment' => 'Modifier l\'attribution de permission',
    'current_permissions' => 'Permissions actuelles',
    'no_permissions_assigned' => 'Aucune permission attribuée',
    'add_permission' => 'Ajouter une permission',
    'confirm_remove_permission' => 'Êtes-vous sûr de vouloir retirer cette permission ?',
    'remove' => 'Retirer',
    'activate' => 'Activer',
    'deactivate' => 'Désactiver',
    'select_all' => 'Tout sélectionner',
    'deselect_all' => 'Tout désélectionner',
    'apply_suggestions' => 'Appliquer les suggestions',
    'create_permission' => 'Créer une permission',
    'export_mappings' => 'Exporter les correspondances',

    // Route groups
    'grouped_by_module' => 'Routes groupées par module',
    'module_statistics' => 'Statistiques par module',
    'route_group' => 'Groupe de routes',

    // Permission assignment
    'select_permission' => 'Sélectionner une permission',
    'no_permission_assigned' => 'Aucune permission attribuée',
    'permission_exists' => 'La permission existe',
    'permission_not_exists' => 'La permission n\'existe pas',
    'create_and_assign' => 'Créer et attribuer',
    'active' => 'Active',
    'inactive' => 'Inactive',

    // Bulk operations
    'bulk_operations' => 'Opérations groupées',
    'selected_routes' => 'Routes sélectionnées',
    'bulk_assign_permissions' => 'Attribuer des permissions en masse',
    'apply_to_selected' => 'Appliquer à la sélection',
    'preview_changes' => 'Prévisualiser les modifications',

    // Impact preview
    'impact_preview' => 'Aperçu de l\'impact',
    'new_mappings' => 'Nouvelles correspondances',
    'updated_mappings' => 'Correspondances mises à jour',
    'removed_mappings' => 'Correspondances supprimées',
    'affected_routes' => 'Routes affectées',
    'affected_permissions' => 'Permissions affectées',

    // Messages
    'messages' => [
        'no_routes_selected' => 'Aucune route sélectionnée. Veuillez sélectionner au moins une route.',
        'permission_updated' => 'Permission de route mise à jour avec succès.',
        'permission_assigned' => 'Permission attribuée avec succès.',
        'permission_removed' => 'Permission de route retirée avec succès.',
        'bulk_assign_success' => 'Attribution groupée terminée : :created créées, :updated mises à jour.',
        'scan_complete' => 'Analyse des routes terminée. :count routes trouvées.',
        'export_success' => 'Permissions de routes exportées avec succès.',
        'no_routes_found' => 'Aucune route ne correspond aux critères.',
        'confirm_remove' => 'Êtes-vous sûr de vouloir supprimer cette correspondance de permission ?',
        'try_adjusting_filters' => 'Essayez d\'ajuster vos filtres ou vos critères de recherche.',
        'routes_selected' => 'routes sélectionnées',
        'assigning' => 'Attribution en cours...',
        'assigned' => 'Attribuée',
    ],

    // Errors
    'errors' => [
        'bulk_assign_failed' => 'Échec de l\'attribution groupée : :error',
        'assignment_failed' => 'Échec de l\'attribution : :error',
        'permission_update_failed' => 'Échec de la mise à jour de la permission de route : :error',
        'scan_failed' => 'Échec de l\'analyse des routes : :error',
        'export_failed' => 'Échec de l\'export : :error',
    ],

    // Help text
    'help' => [
        'route_scanning' => 'L\'analyse examine toutes les routes enregistrées dans l\'application afin d\'identifier celles qui ont des permissions et celles qui en ont besoin.',
        'permission_suggestions' => 'Les suggestions sont basées sur les conventions de nommage des routes et les opérations CRUD courantes.',
        'dynamic_mappings' => 'Les correspondances dynamiques vous permettent d\'attribuer des permissions aux routes sans modifier le code.',
        'bulk_assignment' => 'Sélectionnez plusieurs routes et attribuez-leur la même permission en une seule fois.',
        'protection_coverage' => 'Affiche le pourcentage de routes bénéficiant d\'une protection par permission.',
    ],
];
