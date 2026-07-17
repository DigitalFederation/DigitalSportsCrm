<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'files' => 'Archivos',
    'administrative_files' => 'Archivos administrativos',
    'no_files_uploaded' => 'No se ha subido ningún documento',

    // Filters
    'filters' => [
        'file_name' => 'Nombre del archivo',
        'category' => 'Categoría',
        'language' => 'Idioma',
        'date' => 'Fecha',
    ],

    // Actions
    'upload_new_file' => 'Subir nuevo archivo',

    // Recipient types
    'recipients' => [
        'all' => 'Todos',
        'all_federations' => 'Todas las federaciones',
        'all_entities' => 'Todas las entidades',
        'all_individuals' => 'Todas las personas',
        'all_entities_&_individuals' => 'Todas las entidades y personas',
        'individual' => 'Persona',
        'entity' => 'Entidad',
        'federation' => 'Federación',
    ],

    // Categories
    'categories' => [
        'Administrativo' => 'Administrativo',
        'Circular' => 'Circular',
        'Ofício' => 'Oficio',
        'Parecer' => 'Dictamen',
        'Manual' => 'Manual',
        'Tabela' => 'Tabla',
        'Regulamento' => 'Reglamento',
        'Norma' => 'Norma',
        'Programa' => 'Programa',
        'Formulário' => 'Formulario',
        'Ata' => 'Acta',
        'Material Pedagógico' => 'Material didáctico',
    ],

    // Filter labels
    'roles' => 'Roles',
    'licenses' => 'Licencias',
    'certifications' => 'Certificaciones',
    'federations' => 'Federaciones',

    // Committee names for file titles
    'committees' => [
        'SPORT' => 'Deporte',
        'SCIENTIFIC' => 'Científico',
        'DIVING' => 'Buceo',
        'DIVINGSERVICES' => 'Servicios de buceo',
    ],

    // Title format: "{committee} {files}"
    'committee_files_title' => 'Archivos de :committee',

    // Admin-specific committee file titles
    'admin_committee_titles' => [
        'SPORT' => 'Documentos de deporte',
        'SCIENTIFIC' => 'Documentos de buceo científico internacional',
        'DIVING' => 'Documentos de buceo internacional',
        'DIVINGSERVICES' => 'Documentos de buceo recreativo',
    ],

    // Entity-specific committee file titles
    'entity_committee_titles' => [
        'SPORT' => 'Archivos de deporte',
        'SCIENTIFIC' => 'Archivos de buceo científico internacional',
        'DIVING' => 'Archivos de buceo internacional',
        'DIVINGSERVICES' => 'Archivos de servicios de buceo',
    ],

    // Primary federation general files (no committee)
    'federation_files' => "Archivos de {$primaryShortName}",

    // Table headers
    'table' => [
        'document' => 'Documento',
        'category' => 'Categoría',
        'language' => 'Idioma',
        'date' => 'Fecha',
        'recipient' => 'Destinatario',
        'organization' => 'Organización',
    ],

    // Messages
    'edit_file' => 'Editar archivo',
    'updated_success' => 'Archivo actualizado correctamente.',
    'click_to_upload' => 'Haz clic aquí para subir uno ahora',
    'download_started' => '¡Descarga iniciada!',
    'confirm_delete' => '¿Estás seguro de que deseas eliminar este adjunto?',
];
