<?php

return [
    'title' => 'Berufliche Rollen',
    'create' => 'Berufliche Rolle erstellen',
    'edit' => 'Berufliche Rolle bearbeiten',
    'edit_title' => 'Rolle bearbeiten',
    'information_title' => 'Informationen',
    'update' => 'Berufliche Rolle aktualisieren',

    // Fields
    'name' => 'Name',
    'code' => 'Code',
    'role_type' => 'Rollentyp',
    'committee' => 'Ausschuss',
    'committee_all' => 'Alle',

    // Hints
    'code_hint' => 'Eindeutiger Bezeichner in Großbuchstaben (z. B. FINSWIMMINGCOACH)',
    'role_type_hint' => 'Kategorie, zu der diese Rolle gehört (wirkt sich auf die Berechtigung zur Veranstaltungsanmeldung aus)',

    // Filters
    'filter_by_name' => 'Nach Namen filtern...',

    // Info boxes
    'info_title' => 'Informationen zu beruflichen Rollen',
    'info_body' => 'Berufliche Rollen definieren, welche Tätigkeiten eine Person ausüben kann (z. B. Trainer, Schiedsrichter, Kampfrichter). Der Rollentyp bestimmt, wie das System diese Rolle bei der Veranstaltungsanmeldung und bei Berechtigungsprüfungen behandelt.',
    'edit_info_title' => 'Informationen',
    'edit_info_body' => 'Berufliche Rollen definieren die Tätigkeiten, die eine Person innerhalb des Verbands ausüben kann. Der Rollentyp bestimmt die Berechtigung für Veranstaltungen: ATHLETE für Wettkämpfer, COACH für Mannschaftstrainer, TECHNICAL_OFFICIAL für Schiedsrichter und Kampfrichter, INSTRUCTOR für Kursausbilder, DIVER für Freizeit-/Berufstaucher, STAFF für die operative Unterstützung des Verbands und FEDERATION_STAFF für administrative Rollen des Verbands.',

    // Success messages
    'created_successfully' => 'Berufliche Rolle erfolgreich erstellt.',
    'updated_successfully' => 'Berufliche Rolle erfolgreich aktualisiert.',
    'deleted_successfully' => 'Berufliche Rolle erfolgreich gelöscht.',
    'role_assigned_successfully' => 'Berufliche Rolle erfolgreich zugewiesen.',
    'role_removed_successfully' => 'Berufliche Rolle erfolgreich entfernt.',

    // Error messages
    'cannot_delete_has_individuals' => 'Diese berufliche Rolle kann nicht gelöscht werden, da sie Einzelpersonen zugewiesen ist.',
    'cannot_delete_has_certifications' => 'Diese berufliche Rolle kann nicht gelöscht werden, da sie mit Zertifizierungen verknüpft ist.',
    'cannot_delete_has_licenses' => 'Diese berufliche Rolle kann nicht gelöscht werden, da sie mit Lizenzen verknüpft ist.',
    'delete_failed' => 'Berufliche Rolle konnte nicht gelöscht werden. Bitte versuchen Sie es erneut.',

    // Role types
    'role_types' => [
        'ATHLETE' => 'Athlet',
        'COACH' => 'Trainer',
        'TECHNICAL_OFFICIAL' => 'Technischer Funktionär',
        'INSTRUCTOR' => 'Ausbilder',
        'LEADER' => 'Leiter',
        'DIVER' => 'Taucher',
        'STAFF' => 'Mitarbeiter',
        'DIVINGPROFESSIONAL' => 'Tauchprofi',
        'FEDERATION_STAFF' => 'Verbandsmitarbeiter',
    ],

    // Actions
    'manage_roles' => 'Rollen verwalten',
];
