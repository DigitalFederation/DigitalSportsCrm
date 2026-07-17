<?php

return [
    'title' => 'Problemas de Integrações',
    'subtitle' => 'Visao consolidada dos erros de integração Moloni e Easypay',

    // Statistics
    'total_errors' => 'Total de Erros',
    'errors_today' => 'Erros Hoje',
    'last_30_days' => 'Ultimos 30 dias',
    'last' => 'Ultimo',

    // Error types
    'moloni_error_types' => 'Tipos de Erro Moloni',
    'easypay_error_types' => 'Tipos de Erro Easypay',

    // Filters
    'integration' => 'Integração',
    'from_date' => 'Data Inicial',
    'to_date' => 'Data Final',

    // Table
    'recent_errors' => 'Erros Recentes',
    'showing_count' => 'A mostrar :count erros',
    'type' => 'Tipo',
    'error_message' => 'Mensagem de Erro',
    'reference' => 'Referencia',
    'date' => 'Data',
    'retry' => 'Tentar Novamente',

    // Empty state
    'no_errors' => 'Sem Erros de Integração',
    'no_errors_description' => 'Todas as integrações estao a funcionar corretamente no periodo selecionado.',

    // Navigation
    'moloni_settings' => 'Definicoes Moloni',
    'webhook_logs' => 'Logs de Webhook',

    // Troubleshooting
    'troubleshooting_title' => 'Dicas de Resolucao de Problemas',
    'troubleshooting_moloni_auth' => 'Erros de autenticação Moloni: Verifique se a conexao Moloni ainda esta ativa nas Definicoes Moloni.',
    'troubleshooting_moloni_config' => 'Erros de fatura Moloni: Verifique se a serie de documentos, imposto e outras configurações estao corretamente definidas.',
    'troubleshooting_easypay_webhook' => 'Erros de webhook Easypay: Verifique se a transação existe e se o estado do pagamento esta correto.',
    'troubleshooting_easypay_transaction' => 'Erros de transação Easypay: Verifique o estado do documento e a configuração de pagamento.',
];
