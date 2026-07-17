<?php

$primaryName = config('branding.primary.name', 'Example Federation');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'payment_documents' => 'Documentos de Pagamento',
    'payment_documents_disclaimer' => 'Estes documentos sao informativos, nao tem validade legal, para cada documento devera ser emitida uma fatura - recibo num programa de contabilidade certificado.',
    'invoices' => 'Faturas',
    'create_manual_order' => 'Criar Encomenda Manual',
    'latest_documents' => 'Documentos Recentes',
    'filtered_results' => 'Resultados Filtrados',
    'entities' => 'Entidades',
    'member' => 'Membro',

    // Table headers
    'number' => 'Número',
    'type' => 'Tipo',
    'document_name' => 'Nome do Documento',
    'status' => 'Estado',
    'issue_date' => 'Data de Emissão',
    'expiration_date' => 'Data de Expiração',
    'total' => 'Total',
    'id' => 'ID',
    'download' => 'Descarregar',
    'category' => 'Categoria',

    // Document detail page
    'document_detail' => 'Detalhe do Documento',
    'payment' => 'Pagamento',
    'select_method' => 'Selecione um método',
    'proceed_to_payment' => 'Proceder ao Pagamento',

    // Document info labels
    'number_label' => 'Número',
    'type_label' => 'Tipo',
    'date_label' => 'Data',
    'recipient' => 'Destinatário',
    'vat_number' => 'NIF',
    'city' => 'Cidade',
    'address' => 'Morada',
    'postal_code' => 'Código Postal',
    'country' => 'País',

    // Table columns
    'product' => 'Produto',
    'qty' => 'Qtd',
    'unit_price' => 'Preço Unitário',
    'amount' => 'Valor',
    'subtotal' => 'Subtotal',
    'amount_paid' => 'Valor Pago',
    'remaining_balance' => 'Saldo Restante',

    // Payment status
    'document_is_paid' => 'Este documento já está pago',
    'find_details_below' => 'Encontre os detalhes abaixo',
    'view_moloni_invoice' => 'Ver Fatura/Recibo',
    'document_type' => 'Tipo de documento',
    'created_at' => 'Criado em',
    'transactions' => 'Transações',
    'transaction_status' => 'Estado',
    'transaction_date' => 'Data',
    'transaction_info' => 'Info',
    'associated_documents' => 'Documentos associados',

    // Filters
    'year' => 'Ano',
    'document_number' => 'Número do Documento',
    'filter_cmas_code_help' => 'Pesquisar pelo código internacional do proprietário',
    'filter_member_placeholder' => 'Nome do membro',
    'organization' => 'Organização',
    'national_organization' => 'Organização Nacional',
    'date_from' => 'Data Início',
    'date_to' => 'Data Fim',
    'payment_date' => 'Data de Pagamento',

    // Index page filters
    'filters' => [
        'category' => 'Categoria',
        'status' => 'Estado',
        'type' => 'Tipo',
    ],

    // Index page table
    'table' => [
        'number' => '# Número',
        'date' => 'Data',
        'type' => 'Tipo',
        'status' => 'Estado',
        'total' => 'Total',
    ],

    // Document manual create
    'attention' => 'Atenção',
    'document_no' => 'Nº Documento',
    'due_date' => 'Data de Vencimento',
    'federation' => 'Federação',
    'entity' => 'Entidade',
    'individual' => 'Individual',
    'manual_entry' => 'Entrada Manual',
    'select_federation' => 'Selecionar Federação',
    'select_federation_option' => '-- Selecionar Federação --',
    'select_entity' => 'Selecionar Entidade',
    'select_entity_option' => '-- Selecionar Entidade --',
    'search_individual' => 'Pesquisar Individual',
    'search_individual_placeholder' => 'Introduza Nº Filiado, nome ou email',
    'active_member' => 'Membro Ativo',
    'birth_date' => 'Data de Nascimento',
    'manual_customer_entry' => 'Entrada Manual de Cliente',
    'customer_name' => 'Nome do Cliente',
    'document_state' => 'Estado do Documento',
    'description' => 'Descrição',
    'delete' => 'Eliminar',
    'add_invoice_items' => 'Adicionar Itens de Fatura',
    'document_line' => 'Linha de Documento',
    'products' => 'Produtos',
    'select_product' => '-- Selecionar Produto --',
    'or' => 'OU',
    'product_service' => 'Produto/Serviço',
    'vat_percentage' => 'IVA %',
    'add_item' => 'Adicionar Item',
    'notes' => 'Notas',
    'save_document' => 'Guardar Documento',

    // Moloni invoice
    'create_moloni_invoice' => 'Criar Fatura no Moloni',
    'create_moloni_invoice_description' => 'Marque esta opção para criar automaticamente uma fatura no Moloni para este pagamento.',

    // Owner type categories (for document filters)
    'categories' => [
        'License' => 'Licença',
        'Membership' => 'Subscrição',
        'Document' => 'Documento',
        'Certification' => 'Certificação',
        'Registration' => 'Inscrição',
        'Manual Order' => 'Manual',
        'Insurance' => 'Seguro',
    ],

    // Document states
    'states' => [
        'paid' => 'Pago',
        'draft' => 'Rascunho',
        'pending' => 'Pendente',
        'canceled' => 'Cancelado',
        'partially_paid' => 'Parcialmente Pago',
        'void' => 'Anulado',
    ],

    // Action messages
    'edit_draft_only' => 'A edicao so e permitida para documentos no estado Rascunho.',
    'notification_sent' => 'Notificacao enviada.',
    'document_canceled_successfully' => 'Documento cancelado com sucesso.',
    'not_cancellable_state' => 'O documento nao esta num estado cancelavel.',
    'has_associated_payments' => 'O documento nao pode ser eliminado porque tem pagamentos associados.',
    'no_invoices_found' => 'Nenhuma fatura encontrada com os criterios especificados.',
    'export_failed' => 'Falha ao gerar exportacao. Por favor tente novamente ou contacte o suporte.',

    // Confirmations
    'confirm_delete_warning' => 'Tem a certeza que deseja apagar este documento? Esta acao e irreversivel e ira apagar todos os dados associados.',
    'confirm_cancel_warning' => 'Tem a certeza que deseja cancelar este documento?',
    'document_deleted_successfully' => 'Documento apagado com sucesso.',

    // Buttons
    'resend_notification' => 'Reenviar notificacao',
    'delete_document' => 'Eliminar Documento',

    // Filter labels
    'document_period' => 'Periodo do Documento',

    // Invoice/Order PDF labels
    'pdf' => [
        'name' => 'Nome',
        'city' => 'Cidade',
        'address' => 'Morada',
        'date' => 'Data',
        'vat_number' => 'NIF',
        'postal_code' => 'Codigo Postal',
        'member_number' => 'N. Filiado',
        'country' => 'Pais',
        'notes' => 'Notas',
        'description' => 'DESCRICAO',
        'qty' => 'QTD.',
        'unit_price' => 'PRECO UNIT.',
        'total' => 'TOTAL',
        'subtotal' => 'Subtotal',
        'tax' => 'IVA',
        'order_disclaimer' => 'Este documento nao constitui uma fatura nem um recibo. O documento fiscal valido sera emitido apos a confirmacao do pagamento, atraves de programa de faturacao certificado nos termos da legislacao em vigor.',
    ],

    // Invoice PDF compliance text
    'invoice_compliance_en' => "Entities and individuals hereby undertake to comply with and strictly enforce {$primaryShortName} rules, as well as to urge their members to adopt an underwater environmental friendly attitude.",
    'invoice_compliance_pt' => "As entidades e individuos comprometem-se por este documento a aplicar e fazer aplicar rigorosamente as regras de {$primaryShortName} e a incentivar os seus membros a adotar uma atitude respeitosa pelo ambiente subaquatico.",
];
