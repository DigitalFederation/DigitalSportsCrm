<?php

return [
    'title' => 'Berechtigungsverwaltung',
    'permission' => 'Berechtigung',
    'permissions' => 'Berechtigungen',
    'create_permission' => 'Berechtigung erstellen',
    'edit_permission' => 'Berechtigung bearbeiten',
    'delete_permission' => 'Berechtigung löschen',
    'permission_details' => 'Berechtigungsdetails',
    'bulk_create' => 'Berechtigungen im Stapel erstellen',
    'import_permissions' => 'Berechtigungen importieren',
    'export_permissions' => 'Berechtigungen exportieren',

    // Form fields
    'name' => 'Berechtigungsname',
    'name_help' => 'Kleinbuchstaben mit Bindestrichen verwenden (z. B. manage-users)',
    'display_name' => 'Anzeigename',
    'description' => 'Beschreibung',
    'category' => 'Kategorie',
    'guard' => 'Guard',
    'guard_name' => 'Guard-Name',
    'roles_using' => 'Rollen, die diese Berechtigung verwenden',
    'routes_using' => 'Routen, die diese Berechtigung verwenden',
    'created_by' => 'Erstellt von',
    'created_at' => 'Erstellt am',
    'updated_at' => 'Aktualisiert am',

    // Categories
    'uncategorized' => 'Nicht kategorisiert',
    'all_categories' => 'Alle Kategorien',
    'select_category' => 'Kategorie auswählen',
    'new_category' => 'Neue Kategorie',

    // Filters
    'filter_by_category' => 'Nach Kategorie filtern',
    'filter_by_usage' => 'Nach Verwendung filtern',
    'has_roles' => 'Hat Rollen',
    'no_roles' => 'Keine Rollen',
    'search_permissions' => 'Berechtigungen suchen...',

    // Statistics
    'total_permissions' => 'Berechtigungen gesamt',
    'system_permissions' => 'Systemberechtigungen',
    'custom_permissions' => 'Benutzerdefinierte Berechtigungen',
    'permissions_with_roles' => 'Berechtigungen mit Rollen',
    'unused_permissions' => 'Ungenutzte Berechtigungen',
    'permissions_with_routes' => 'Berechtigungen mit Routen',

    // Actions
    'actions' => 'Aktionen',
    'view' => 'Anzeigen',
    'edit' => 'Bearbeiten',
    'delete' => 'Löschen',
    'cancel' => 'Abbrechen',
    'save' => 'Speichern',
    'create' => 'Erstellen',
    'back_to_list' => 'Zurück zu den Berechtigungen',
    'confirm_delete' => 'Löschen bestätigen',

    // Bulk operations
    'bulk_operations' => 'Stapelvorgänge',
    'add_permission_line' => 'Berechtigung hinzufügen',
    'remove_line' => 'Entfernen',
    'default_category' => 'Standardkategorie',
    'default_guard' => 'Standard-Guard',
    'apply_defaults' => 'Standardwerte anwenden',

    // Import/Export
    'select_file' => 'CSV-Datei auswählen',
    'download_template' => 'Vorlage herunterladen',
    'import' => 'Importieren',
    'export' => 'Exportieren',

    // Status
    'system_permission' => 'Systemberechtigung',
    'protected' => 'Geschützt',
    'deletable' => 'Löschbar',
    'in_use' => 'In Verwendung',
    'not_used' => 'Nicht verwendet',

    // Impact analysis
    'deletion_impact' => 'Auswirkung des Löschens',
    'affected_roles' => 'Betroffene Rollen',
    'affected_users' => 'Betroffene Benutzer',
    'affected_routes' => 'Betroffene Routen',
    'roles_list' => 'Rollen mit dieser Berechtigung',

    // Messages
    'messages' => [
        'permission_created_successfully' => 'Berechtigung erfolgreich erstellt.',
        'permission_updated_successfully' => 'Berechtigung erfolgreich aktualisiert.',
        'permission_deleted_successfully' => 'Berechtigung erfolgreich gelöscht.',
        'bulk_create_success' => ':count Berechtigungen erfolgreich erstellt.',
        'bulk_create_partial' => ':created Berechtigungen erstellt, :failed fehlgeschlagen.',
        'import_success' => 'Import abgeschlossen: :created erstellt, :skipped übersprungen.',
        'no_permissions_found' => 'Keine Berechtigungen gefunden.',
        'no_permissions_added' => 'Noch keine Berechtigungen hinzugefügt.',
        'confirm_delete_message' => 'Sind Sie sicher, dass Sie diese Berechtigung löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.',
    ],

    // Errors
    'errors' => [
        'permission_already_exists' => 'Eine Berechtigung mit diesem Namen existiert bereits.',
        'cannot_modify_system_permission' => 'Systemberechtigungen können nicht geändert werden.',
        'cannot_delete_system_permission' => 'Systemberechtigungen können nicht gelöscht werden.',
        'permission_used_by_protected_roles' => 'Diese Berechtigung wird von geschützten Rollen verwendet: :roles',
        'permission_used_in_routes' => 'Diese Berechtigung wird in :count Route(n) verwendet.',
        'permission_creation_failed' => 'Berechtigung konnte nicht erstellt werden: :error',
        'permission_update_failed' => 'Berechtigung konnte nicht aktualisiert werden: :error',
        'permission_deletion_failed' => 'Berechtigung konnte nicht gelöscht werden: :error',
        'bulk_create_failed' => 'Stapelerstellung fehlgeschlagen: :error',
        'import_failed' => 'Import fehlgeschlagen: :error',
        'invalid_permission_name' => 'Der Berechtigungsname darf nur aus Kleinbuchstaben mit Bindestrichen bestehen.',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Der Berechtigungsname ist erforderlich.',
        'name_unique' => 'Dieser Berechtigungsname existiert bereits.',
        'name_format' => 'Der Berechtigungsname muss aus Kleinbuchstaben mit Bindestrichen bestehen (z. B. manage-users).',
        'description_too_long' => 'Die Beschreibung darf 1000 Zeichen nicht überschreiten.',
        'category_too_long' => 'Die Kategorie darf 100 Zeichen nicht überschreiten.',
    ],

    // Help text
    'help' => [
        'naming_convention' => 'Kleinbuchstaben mit Bindestrichen zwischen den Wörtern verwenden (z. B. manage-users, view-reports)',
        'categories' => 'Kategorien helfen bei der Organisation von Berechtigungen. Gängige Kategorien: Benutzer, Rollen, Inhalte, Einstellungen, Berichte',
        'system_permissions' => 'Systemberechtigungen sind Kernberechtigungen, die nicht geändert oder gelöscht werden können.',
        'bulk_create' => 'Erstellen Sie mehrere Berechtigungen auf einmal. Jede Zeile erstellt eine neue Berechtigung.',
        'import_format' => 'CSV-Format: name, category, description, guard_name',
        'guard_cannot_be_changed' => 'Der Guard kann aus Sicherheitsgründen nach der Erstellung nicht geändert werden.',
    ],
];
