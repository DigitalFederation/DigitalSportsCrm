<?php

return [
    // Page titles
    'payment_methods' => 'Metodos de Pagamento',
    'payment_transactions' => 'Transações de Pagamento',
    'webhook_logs' => 'Registos de Webhook',
    'edit_method' => 'Editar Metodo de Pagamento',
    'transaction_details' => 'Detalhes da Transação',
    'webhook_log_details' => 'Detalhes do Registro de Webhook',

    // Navigation
    'manage_payment_methods' => 'Gerir Metodos de Pagamento',
    'view_transactions' => 'Ver Transações',
    'view_webhook_logs' => 'Ver Registos de Webhook',

    // Statistics
    'total_transactions' => 'Total de Transações',
    'total_webhooks' => 'Total de Webhooks',
    'total_amount' => 'Valor Total',
    'pending' => 'Pendente',
    'successful' => 'Sucesso',
    'failed' => 'Falhado',
    'success_rate' => 'Taxa de Sucesso',
    'avg_processing_time' => 'Tempo Medio de Processamento',
    'status_breakdown' => 'Distribuicao por Estado',
    'today' => 'Hoje',

    // Table headers
    'id' => 'ID',
    'name' => 'Nome',
    'driver' => 'Driver',
    'handler' => 'Handler',
    'status' => 'Estado',
    'instructions' => 'Instruções',
    'document' => 'Documento',
    'payment_method' => 'Metodo de Pagamento',
    'amount' => 'Valor',
    'date' => 'Data',
    'gateway' => 'Gateway',
    'request_id' => 'ID do Pedido',
    'transaction' => 'Transação',
    'processing_time' => 'Tempo de Processamento',

    // Form labels
    'instructions_help' => 'Instruções mostradas aos usuários ao selecionar este metodo de pagamento.',
    'enabled' => 'Ativo',
    'disabled' => 'Inativo',
    'technical_info' => 'Informação Técnica',
    'note' => 'Nota',
    'easypay_config_note' => 'As credenciais do EasyPay sao configuradas via variaveis de ambiente. Contacte um programador para atualizar as chaves API.',

    // Gateway status
    'configured' => 'Configurado',
    'not_configured' => 'Não Configurado',
    'mode' => 'Modo',
    'sandbox' => 'Sandbox',
    'production' => 'Producao',
    'webhook_secret' => 'Webhook Secret',
    'webhook_url' => 'URL do Webhook',
    'available_methods' => 'Metodos Disponíveis',

    // Actions
    'enable' => 'Ativar',
    'disable' => 'Desativar',

    // Transaction details
    'transaction_info' => 'Informação da Transação',
    'document_info' => 'Informação do Documento',
    'payment_data' => 'Dados de Pagamento',
    'comment' => 'Comentario',
    'created_at' => 'Criado Em',
    'updated_at' => 'Atualizado Em',
    'document_number' => 'Numero do Documento',
    'document_status' => 'Estado do Documento',
    'document_total' => 'Total do Documento',
    'owner' => 'Proprietario',
    'view_document' => 'Ver Documento',
    'no_document_associated' => 'Nenhum documento associado a esta transação.',

    // Webhook log details
    'request_info' => 'Informação do Pedido',
    'related_records' => 'Registos Relacionados',
    'ip_address' => 'Endereco IP',
    'received_at' => 'Recebido Em',
    'request_headers' => 'Cabecalhos do Pedido',
    'webhook_payload' => 'Payload do Webhook',
    'response_sent' => 'Resposta Enviada',
    'no_transaction' => 'Sem transação associada',
    'no_document' => 'Sem documento associado',

    // Filter labels
    'from_date' => 'Data Inicial',
    'to_date' => 'Data Final',

    // Empty states
    'no_transactions_found' => 'Nenhuma transação encontrada.',
    'no_webhook_logs_found' => 'Nenhum registro de webhook encontrado.',
];
