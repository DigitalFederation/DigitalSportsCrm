<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'files' => 'Dateien',
    'administrative_files' => 'Administrative Dateien',
    'no_files_uploaded' => 'Keine Dokumente hochgeladen',

    // Filters
    'filters' => [
        'file_name' => 'Dateiname',
        'category' => 'Kategorie',
        'language' => 'Sprache',
        'date' => 'Datum',
    ],

    // Actions
    'upload_new_file' => 'Neue Datei hochladen',

    // Recipient types
    'recipients' => [
        'all' => 'Alle',
        'all_federations' => 'Alle Verbände',
        'all_entities' => 'Alle Organisationen',
        'all_individuals' => 'Alle Personen',
        'all_entities_&_individuals' => 'Alle Organisationen & Personen',
        'individual' => 'Person',
        'entity' => 'Organisation',
        'federation' => 'Verband',
    ],

    // Categories
    'categories' => [
        'Administrativo' => 'Administrativ',
        'Circular' => 'Rundschreiben',
        'Ofício' => 'Amtliches Schreiben',
        'Parecer' => 'Stellungnahme',
        'Manual' => 'Handbuch',
        'Tabela' => 'Tabelle',
        'Regulamento' => 'Ordnung',
        'Norma' => 'Norm',
        'Programa' => 'Programm',
        'Formulário' => 'Formular',
        'Ata' => 'Protokoll',
        'Material Pedagógico' => 'Lehrmaterial',
    ],

    // Filter labels
    'roles' => 'Rollen',
    'licenses' => 'Lizenzen',
    'certifications' => 'Zertifizierungen',
    'federations' => 'Verbände',

    // Committee names for file titles
    'committees' => [
        'SPORT' => 'Sport',
        'SCIENTIFIC' => 'Wissenschaft',
        'DIVING' => 'Tauchen',
        'DIVINGSERVICES' => 'Tauchdienstleistungen',
    ],

    // Title format: "{committee} {files}"
    'committee_files_title' => ':committee Dateien',

    // Admin-specific committee file titles
    'admin_committee_titles' => [
        'SPORT' => 'Sportdokumente',
        'SCIENTIFIC' => 'Dokumente zum internationalen wissenschaftlichen Tauchen',
        'DIVING' => 'Dokumente zum internationalen Tauchen',
        'DIVINGSERVICES' => 'Dokumente zum Freizeittauchen',
    ],

    // Entity-specific committee file titles
    'entity_committee_titles' => [
        'SPORT' => 'Sportdateien',
        'SCIENTIFIC' => 'Dateien zum internationalen wissenschaftlichen Tauchen',
        'DIVING' => 'Dateien zum internationalen Tauchen',
        'DIVINGSERVICES' => 'Dateien zu Tauchdienstleistungen',
    ],

    // Primary federation general files (no committee)
    'federation_files' => "{$primaryShortName} Dateien",

    // Table headers
    'table' => [
        'document' => 'Dokument',
        'category' => 'Kategorie',
        'language' => 'Sprache',
        'date' => 'Datum',
        'recipient' => 'Empfänger',
        'organization' => 'Organisation',
    ],

    // Messages
    'edit_file' => 'Datei bearbeiten',
    'updated_success' => 'Datei erfolgreich aktualisiert.',
    'click_to_upload' => 'Klicken Sie hier, um jetzt eine hochzuladen',
    'download_started' => 'Download gestartet!',
    'confirm_delete' => 'Sind Sie sicher, dass Sie diesen Anhang löschen möchten?',
];
