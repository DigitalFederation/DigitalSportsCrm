<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'files' => 'Fichiers',
    'administrative_files' => 'Fichiers administratifs',
    'no_files_uploaded' => 'Aucun document téléchargé',

    // Filters
    'filters' => [
        'file_name' => 'Nom du fichier',
        'category' => 'Catégorie',
        'language' => 'Langue',
        'date' => 'Date',
    ],

    // Actions
    'upload_new_file' => 'Télécharger un nouveau fichier',

    // Recipient types
    'recipients' => [
        'all' => 'Tous',
        'all_federations' => 'Toutes les fédérations',
        'all_entities' => 'Toutes les entités',
        'all_individuals' => 'Tous les individus',
        'all_entities_&_individuals' => 'Toutes les entités et tous les individus',
        'individual' => 'Individu',
        'entity' => 'Entité',
        'federation' => 'Fédération',
    ],

    // Categories
    'categories' => [
        'Administrativo' => 'Administratif',
        'Circular' => 'Circulaire',
        'Ofício' => 'Lettre officielle',
        'Parecer' => 'Avis',
        'Manual' => 'Manuel',
        'Tabela' => 'Tableau',
        'Regulamento' => 'Règlement',
        'Norma' => 'Norme',
        'Programa' => 'Programme',
        'Formulário' => 'Formulaire',
        'Ata' => 'Procès-verbal',
        'Material Pedagógico' => 'Matériel pédagogique',
    ],

    // Filter labels
    'roles' => 'Rôles',
    'licenses' => 'Licences',
    'certifications' => 'Certifications',
    'federations' => 'Fédérations',

    // Committee names for file titles
    'committees' => [
        'SPORT' => 'Sport',
        'SCIENTIFIC' => 'Scientifique',
        'DIVING' => 'Plongée',
        'DIVINGSERVICES' => 'Services de plongée',
    ],

    // Title format: "{committee} {files}"
    'committee_files_title' => 'Fichiers :committee',

    // Admin-specific committee file titles
    'admin_committee_titles' => [
        'SPORT' => 'Documents sportifs',
        'SCIENTIFIC' => 'Documents de plongée scientifique internationale',
        'DIVING' => 'Documents de plongée internationale',
        'DIVINGSERVICES' => 'Documents de plongée récréative',
    ],

    // Entity-specific committee file titles
    'entity_committee_titles' => [
        'SPORT' => 'Fichiers sportifs',
        'SCIENTIFIC' => 'Fichiers de plongée scientifique internationale',
        'DIVING' => 'Fichiers de plongée internationale',
        'DIVINGSERVICES' => 'Fichiers des services de plongée',
    ],

    // Primary federation general files (no committee)
    'federation_files' => "Fichiers {$primaryShortName}",

    // Table headers
    'table' => [
        'document' => 'Document',
        'category' => 'Catégorie',
        'language' => 'Langue',
        'date' => 'Date',
        'recipient' => 'Destinataire',
        'organization' => 'Organisation',
    ],

    // Messages
    'edit_file' => 'Modifier le fichier',
    'updated_success' => 'Fichier mis à jour avec succès.',
    'click_to_upload' => 'Cliquez ici pour en télécharger un maintenant',
    'download_started' => 'Téléchargement démarré !',
    'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cette pièce jointe ?',
];
