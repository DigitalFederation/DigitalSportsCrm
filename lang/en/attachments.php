<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'files' => 'Files',
    'administrative_files' => 'Administrative Files',
    'no_files_uploaded' => 'No documents uploaded',

    // Filters
    'filters' => [
        'file_name' => 'File Name',
        'category' => 'Category',
        'language' => 'Language',
        'date' => 'Date',
    ],

    // Actions
    'upload_new_file' => 'Upload new file',

    // Recipient types
    'recipients' => [
        'all' => 'All',
        'all_federations' => 'All Federations',
        'all_entities' => 'All Entities',
        'all_individuals' => 'All Individuals',
        'all_entities_&_individuals' => 'All Entities & Individuals',
        'individual' => 'Individual',
        'entity' => 'Entity',
        'federation' => 'Federation',
    ],

    // Categories
    'categories' => [
        'Administrativo' => 'Administrative',
        'Circular' => 'Circular',
        'Ofício' => 'Official Letter',
        'Parecer' => 'Opinion',
        'Manual' => 'Manual',
        'Tabela' => 'Table',
        'Regulamento' => 'Regulation',
        'Norma' => 'Standard',
        'Programa' => 'Program',
        'Formulário' => 'Form',
        'Ata' => 'Minutes',
        'Material Pedagógico' => 'Teaching Material',
    ],

    // Filter labels
    'roles' => 'Roles',
    'licenses' => 'Licenses',
    'certifications' => 'Certifications',
    'federations' => 'Federations',

    // Committee names for file titles
    'committees' => [
        'SPORT' => 'Sport',
        'SCIENTIFIC' => 'Scientific',
        'DIVING' => 'Diving',
        'DIVINGSERVICES' => 'Diving Services',
    ],

    // Title format: "{committee} {files}"
    'committee_files_title' => ':committee Files',

    // Admin-specific committee file titles
    'admin_committee_titles' => [
        'SPORT' => 'Sport Documents',
        'SCIENTIFIC' => 'International Scientific Diving Documents',
        'DIVING' => 'International Diving Documents',
        'DIVINGSERVICES' => 'Recreational Diving Documents',
    ],

    // Entity-specific committee file titles
    'entity_committee_titles' => [
        'SPORT' => 'Sport Files',
        'SCIENTIFIC' => 'International Scientific Diving Files',
        'DIVING' => 'International Diving Files',
        'DIVINGSERVICES' => 'Diving Services Files',
    ],

    // Primary federation general files (no committee)
    'federation_files' => "{$primaryShortName} Files",

    // Table headers
    'table' => [
        'document' => 'Document',
        'category' => 'Category',
        'language' => 'Language',
        'date' => 'Date',
        'recipient' => 'Recipient',
        'organization' => 'Organization',
    ],

    // Messages
    'edit_file' => 'Edit File',
    'updated_success' => 'File updated successfully.',
    'click_to_upload' => 'Click here to upload one now',
    'download_started' => 'Download started!',
    'confirm_delete' => 'Are you sure you want to delete this attachment?',
];
