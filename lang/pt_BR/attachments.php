<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'files' => 'Arquivos',
    'administrative_files' => "Documentos de {$primaryShortName}",
    'no_files_uploaded' => 'Nenhum documento carregado',

    // Filters
    'filters' => [
        'file_name' => 'Nome do Arquivo',
        'category' => 'Categoria',
        'language' => 'Idioma',
        'date' => 'Data',
    ],

    // Actions
    'upload_new_file' => 'Carregar novo arquivo',

    // Recipient types
    'recipients' => [
        'all' => 'Todos',
        'all_federations' => 'Federações',
        'all_entities' => 'Entidades',
        'all_individuals' => 'Individuais',
        'all_entities_&_individuals' => 'Entidades e Individuais',
        'individual' => 'Individual',
        'entity' => 'Entidade',
        'federation' => 'Federação',
    ],

    // Categories
    'categories' => [
        'Administrativo' => 'Administrativo',
        'Circular' => 'Circular',
        'Ofício' => 'Ofício',
        'Parecer' => 'Parecer',
        'Manual' => 'Manual',
        'Tabela' => 'Tabela',
        'Regulamento' => 'Regulamento',
        'Norma' => 'Norma',
        'Programa' => 'Programa',
        'Formulário' => 'Formulário',
        'Ata' => 'Ata',
        'Material Pedagógico' => 'Material Pedagógico',
    ],

    // Filter labels
    'roles' => 'Funções',
    'licenses' => 'Licenças',
    'certifications' => 'Certificações',
    'federations' => 'Federações',

    // Committee names for file titles
    'committees' => [
        'SPORT' => 'Desportivo',
        'SCIENTIFIC' => 'Científico',
        'DIVING' => 'Mergulho',
        'DIVINGSERVICES' => 'Serviços de Mergulho',
    ],

    // Title format: "{files} {committee}"
    'committee_files_title' => 'Arquivos :committee',

    // Admin-specific committee file titles
    'admin_committee_titles' => [
        'SPORT' => 'Documentos de Desporto',
        'SCIENTIFIC' => 'Documentos de Mergulho Cientifico',
        'DIVING' => 'Documentos de Mergulho',
        'DIVINGSERVICES' => 'Documentos Mergulho Recreativo',
    ],

    // Entity-specific committee file titles
    'entity_committee_titles' => [
        'SPORT' => 'Arquivos de Desporto',
        'SCIENTIFIC' => 'Arquivos de Mergulho Científico',
        'DIVING' => 'Arquivos de Mergulho',
        'DIVINGSERVICES' => 'Arquivos de Serviços de Mergulho',
    ],

    // Primary federation general files (no committee)
    'federation_files' => "Documentos de {$primaryShortName}",

    // Table headers
    'table' => [
        'document' => 'Documento',
        'category' => 'Categoria',
        'language' => 'Idioma',
        'date' => 'Data',
        'recipient' => 'Destinatario',
        'organization' => 'Organizacao',
    ],

    // Messages
    'edit_file' => 'Editar Documento',
    'updated_success' => 'Documento atualizado com sucesso.',
    'click_to_upload' => 'Clique aqui para carregar um agora',
    'download_started' => 'Download iniciado!',
    'confirm_delete' => 'Tem a certeza que pretende eliminar este arquivo?',
];
