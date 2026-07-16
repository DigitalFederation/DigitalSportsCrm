<?php

return [
    'title' => 'Routen-Berechtigungsverwaltung',
    'route_permissions' => 'Routen-Berechtigungen',
    'scan_routes' => 'Routen scannen',
    'assign_permissions' => 'Berechtigungen zuweisen',
    'bulk_assign' => 'Im Stapel zuweisen',
    'routes_to_assign' => 'Routen zur Berechtigungszuweisung',
    'route_details' => 'Routendetails',
    'permission_mapping' => 'Berechtigungszuordnung',

    // Route fields
    'route_name' => 'Routenname',
    'uri' => 'URI-Muster',
    'methods' => 'HTTP-Methoden',
    'controller' => 'Controller',
    'middleware' => 'Middleware',
    'current_permission' => 'Aktuelle Berechtigung',
    'assigned_permission' => 'Zugewiesene Berechtigung',
    'suggested_permissions' => 'Vorgeschlagene Berechtigungen',
    'module' => 'Modul',
    'prefix' => 'Präfix',
    'parameters' => 'Parameter',
    'status' => 'Status',
    'uncategorized' => 'Nicht kategorisiert',

    // Filters
    'filter_by_module' => 'Nach Modul filtern',
    'all_modules' => 'Alle Module',
    'all_permissions' => 'Alle Berechtigungen',
    'filter_by_prefix' => 'Nach Präfix filtern',
    'filter_by_permission' => 'Nach Berechtigungsstatus filtern',
    'has_permission' => 'Hat Berechtigung',
    'no_permission' => 'Keine Berechtigung',
    'all_routes' => 'Alle Routen',
    'search_routes' => 'Routen suchen...',

    // Statistics
    'total_routes' => 'Routen gesamt',
    'routes_with_permissions' => 'Routen mit Berechtigungen',
    'routes_without_permissions' => 'Routen ohne Berechtigungen',
    'percentage_protected' => 'Schutzabdeckung',
    'dynamic_mappings' => 'Dynamische Zuordnungen',
    'active_mappings' => 'Aktive Zuordnungen',

    // Actions
    'scan' => 'Scannen',
    'assign' => 'Zuweisen',
    'assign_permission' => 'Berechtigung zuweisen',
    'edit_permission_assignment' => 'Berechtigungszuweisung bearbeiten',
    'current_permissions' => 'Aktuelle Berechtigungen',
    'no_permissions_assigned' => 'Keine Berechtigungen zugewiesen',
    'add_permission' => 'Berechtigung hinzufügen',
    'confirm_remove_permission' => 'Sind Sie sicher, dass Sie diese Berechtigung entfernen möchten?',
    'remove' => 'Entfernen',
    'activate' => 'Aktivieren',
    'deactivate' => 'Deaktivieren',
    'select_all' => 'Alle auswählen',
    'deselect_all' => 'Auswahl aufheben',
    'apply_suggestions' => 'Vorschläge anwenden',
    'create_permission' => 'Berechtigung erstellen',
    'export_mappings' => 'Zuordnungen exportieren',

    // Route groups
    'grouped_by_module' => 'Nach Modul gruppierte Routen',
    'module_statistics' => 'Modulstatistiken',
    'route_group' => 'Routengruppe',

    // Permission assignment
    'select_permission' => 'Berechtigung auswählen',
    'no_permission_assigned' => 'Keine Berechtigung zugewiesen',
    'permission_exists' => 'Berechtigung existiert',
    'permission_not_exists' => 'Berechtigung existiert nicht',
    'create_and_assign' => 'Erstellen & zuweisen',
    'active' => 'Aktiv',
    'inactive' => 'Inaktiv',

    // Bulk operations
    'bulk_operations' => 'Stapelvorgänge',
    'selected_routes' => 'Ausgewählte Routen',
    'bulk_assign_permissions' => 'Berechtigungen im Stapel zuweisen',
    'apply_to_selected' => 'Auf Auswahl anwenden',
    'preview_changes' => 'Vorschau der Änderungen',

    // Impact preview
    'impact_preview' => 'Auswirkungsvorschau',
    'new_mappings' => 'Neue Zuordnungen',
    'updated_mappings' => 'Aktualisierte Zuordnungen',
    'removed_mappings' => 'Entfernte Zuordnungen',
    'affected_routes' => 'Betroffene Routen',
    'affected_permissions' => 'Betroffene Berechtigungen',

    // Messages
    'messages' => [
        'no_routes_selected' => 'Keine Routen ausgewählt. Bitte wählen Sie mindestens eine Route aus.',
        'permission_updated' => 'Routen-Berechtigung erfolgreich aktualisiert.',
        'permission_assigned' => 'Berechtigung erfolgreich zugewiesen.',
        'permission_removed' => 'Routen-Berechtigung erfolgreich entfernt.',
        'bulk_assign_success' => 'Stapelzuweisung abgeschlossen: :created erstellt, :updated aktualisiert.',
        'scan_complete' => 'Routenscan abgeschlossen. :count Routen gefunden.',
        'export_success' => 'Routen-Berechtigungen erfolgreich exportiert.',
        'no_routes_found' => 'Keine Routen gefunden, die den Kriterien entsprechen.',
        'confirm_remove' => 'Sind Sie sicher, dass Sie diese Berechtigungszuordnung entfernen möchten?',
        'try_adjusting_filters' => 'Versuchen Sie, Ihre Filter oder Suchkriterien anzupassen.',
        'routes_selected' => 'Routen ausgewählt',
        'assigning' => 'Wird zugewiesen...',
        'assigned' => 'Zugewiesen',
    ],

    // Errors
    'errors' => [
        'bulk_assign_failed' => 'Stapelzuweisung fehlgeschlagen: :error',
        'assignment_failed' => 'Zuweisung fehlgeschlagen: :error',
        'permission_update_failed' => 'Routen-Berechtigung konnte nicht aktualisiert werden: :error',
        'scan_failed' => 'Routenscan fehlgeschlagen: :error',
        'export_failed' => 'Export fehlgeschlagen: :error',
    ],

    // Help text
    'help' => [
        'route_scanning' => 'Beim Scannen werden alle registrierten Routen der Anwendung analysiert, um festzustellen, welche Berechtigungen haben und welche benötigt werden.',
        'permission_suggestions' => 'Vorschläge basieren auf Routen-Benennungsmustern und gängigen CRUD-Operationen.',
        'dynamic_mappings' => 'Dynamische Zuordnungen ermöglichen es Ihnen, Routen Berechtigungen zuzuweisen, ohne den Code zu ändern.',
        'bulk_assignment' => 'Wählen Sie mehrere Routen aus und weisen Sie ihnen allen gleichzeitig dieselbe Berechtigung zu.',
        'protection_coverage' => 'Zeigt den Prozentsatz der Routen an, die über einen Berechtigungsschutz verfügen.',
    ],
];
