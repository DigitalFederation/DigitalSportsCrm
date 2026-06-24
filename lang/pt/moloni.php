<?php

return [
    // Page title
    'title' => 'Integração Moloni',

    // Connection status
    'connection_status' => 'Estado da Ligação',
    'connected' => 'Ligado',
    'not_connected' => 'Desligado',
    'token_expires' => 'Token expira',
    'minutes_remaining' => 'minutos restantes',

    // Buttons
    'authorize' => 'Autorizar com Moloni',
    'disconnect' => 'Desligar',
    'test_connection' => 'Testar Ligação',
    'sync_now' => 'Sincronizar Agora',
    'save' => 'Guardar Configuração',

    // Sync
    'sync_data' => 'Sincronizar Dados do Moloni',
    'last_sync' => 'Última sincronização',
    'no_sync_yet' => 'Ainda não foram sincronizados dados. Clique em "Sincronizar Agora" para obter dados do Moloni.',
    'sync_required' => 'Sincronização Necessária',
    'sync_data_first' => 'Por favor sincronize os dados do Moloni primeiro para preencher as opções de configuração.',

    // Configuration
    'configuration' => 'Configuração de Faturas',
    'document_set' => 'Série de Documentos',
    'default_tax' => 'Taxa de IVA',
    'exempt_tax' => 'Taxa Isenta (0% IVA)',
    'for_exempt_products' => 'para produtos isentos',
    'exempt_tax_help' => 'Selecione a taxa de 0% a usar para produtos isentos de IVA. Necessário quando os planos têm taxa de IVA a 0%.',
    'no_exempt_tax_available' => 'Nenhuma taxa de 0% configurada no Moloni. Crie uma taxa de 0% na sua conta Moloni e sincronize os dados para ativar esta opção.',
    'exemption_reason' => 'Motivo de Isenção',
    'required_for_exempt' => 'obrigatório para produtos isentos',
    'exemption_reason_help' => 'Código legal de isenção (ex: M07 para Artigo 9.º do CIVA). Obrigatório pelo Moloni para produtos sem IVA.',
    'product_category' => 'Categoria de Produtos',
    'payment_method' => 'Método de Pagamento',
    'unit' => 'Unidade de Medida',
    'select_option' => 'Selecione uma opção...',
    'optional' => 'opcional',
    'category_help' => 'Apenas necessário para criar novos produtos no Moloni. Não é necessário se os produtos já existirem (correspondidos por referência).',
    'auto_detect' => 'Detetar automaticamente do pagamento',
    'payment_method_help' => 'Deixe vazio para detetar automaticamente com base no método de pagamento do documento (Transferência Bancária, Multibanco, etc.).',
    'unit_help' => 'Apenas necessário para criar novos produtos no Moloni. Não é necessário se os produtos já existirem (correspondidos por referência).',

    // Status
    'status' => 'Estado da Integração',
    'ready' => 'Pronto',
    'incomplete' => 'Configuração Incompleta',
    'invoices_will_be_generated' => 'As faturas serão geradas automaticamente para documentos pagos.',
    'complete_configuration' => 'Por favor complete a configuração para ativar a geração automática de faturas.',

    // Logs
    'recent_logs' => 'Registos de Sincronização',
    'no_logs' => 'Sem registos de sincronização disponíveis.',
    'type' => 'Tipo',
    'date' => 'Data',
    'duration' => 'Duração',
    'details' => 'Detalhes',

    // Messages
    'connected_successfully' => 'Ligação ao Moloni efetuada com sucesso!',
    'disconnected_successfully' => 'Desligado do Moloni.',
    'connection_successful' => 'Teste de ligação bem sucedido!',
    'connection_test_failed' => 'Teste de ligação falhou. Por favor verifique as credenciais.',
    'connection_failed' => 'Ligação falhou: :error',
    'sync_completed' => 'Dados sincronizados com sucesso. :count items obtidos.',
    'sync_failed' => 'Sincronização falhou: :error',
    'settings_saved' => 'Configurações guardadas com sucesso.',
    'authorization_denied' => 'Autorização negada: :error',
    'no_authorization_code' => 'Nenhum código de autorização recebido do Moloni.',
    'disconnect_confirm' => 'Tem a certeza que pretende desligar do Moloni? Isto irá remover os tokens armazenados.',

    // Warnings
    'integration_disabled' => 'Integração Desativada',
    'enable_in_env' => 'A integração Moloni está atualmente desativada. Defina MOLONI_ENABLED=true no ficheiro .env para ativar.',
    'missing_credentials' => 'Credenciais em Falta',
    'add_credentials_to_env' => 'Por favor adicione MOLONI_CLIENT_ID e MOLONI_CLIENT_SECRET ao ficheiro .env.',

    // New fields
    'company' => 'Empresa',
    'maturity_date' => 'Prazo de Pagamento',
    'days' => 'dias',

    // Invoices
    'recent_invoices' => 'Faturas Recentes',
    'no_invoices' => 'Ainda não foram geradas faturas.',
    'failed_invoices' => 'Faturas com Erro',
    'document' => 'Documento',
    'moloni_number' => 'Número Moloni',
    'moloni_status' => 'Estado',
    'total' => 'Total',
    'error' => 'Erro',
    'actions' => 'Ações',
    'retry' => 'Tentar Novamente',

    // Manual operations
    'invoice_created' => 'Fatura :number criada com sucesso.',
    'invoice_not_created' => 'A fatura não pôde ser criada (Moloni não configurado ou documento não elegível).',
    'invoice_creation_failed' => 'Falha ao criar fatura: :error',
    'customer_synced' => 'Cliente sincronizado com sucesso. ID Moloni: :id',
    'customer_sync_failed' => 'Falha ao sincronizar cliente: :error',

    // PDF and status
    'download_pdf' => 'Descarregar PDF',
    'refresh_status' => 'Atualizar',
    'pdf_not_available' => 'PDF não disponível para esta fatura.',
    'invoice_not_found' => 'Nenhuma fatura Moloni encontrada para este documento.',
    'pdf_download_failed' => 'Falha ao descarregar PDF: :error',
    'status_refreshed' => 'Estado da fatura :number atualizado com sucesso.',
    'status_refresh_failed' => 'Falha ao atualizar estado: :error',
    'view_in_moloni' => 'Ver no Moloni',

    // Customer management
    'synced_customers' => 'Clientes Sincronizados',
    'no_customers' => 'Ainda não foram sincronizados clientes.',
    'customer_name' => 'Nome',
    'customer_vat' => 'NIF/NIPC',
    'customer_type' => 'Tipo',
    'moloni_id' => 'ID Moloni',
    'individual' => 'Pessoa Singular',
    'entity' => 'Entidade',
    'sync_customer_button' => 'Sincronizar Cliente',

    // Bulk operations
    'retry_selected' => 'Tentar Selecionados',
    'select_all' => 'Selecionar Todos',
    'bulk_retry_success' => ':count faturas reenviadas com sucesso.',
    'bulk_retry_partial' => ':success faturas com sucesso, :failed faturas falharam.',
    'bulk_retry_failed' => ':count faturas falharam ao tentar novamente.',
    'no_invoices_selected' => 'Por favor selecione pelo menos uma fatura para tentar novamente.',

    // Product reference
    'product_reference' => 'Referência Moloni',
    'product_reference_help' => 'Código de referência único para associar este plano a um produto Moloni. Se definido, o mesmo produto será reutilizado em todas as faturas.',

    // Document series per type
    'document_series_by_type' => 'Série de Documentos por Tipo',
    'document_series_by_type_description' => 'Configure séries de documentos diferentes para cada tipo de documento. Deixe vazio para usar a série padrão acima.',
    'owner_type_license' => 'Licenças',
    'owner_type_membership' => 'Quotas de Entidades',
    'owner_type_member_subscription' => 'Filiações Individuais',
    'owner_type_certification' => 'Certificações',
    'owner_type_enrollment' => 'Inscrições de Entidades (Eventos)',
    'owner_type_individual_enrollment' => 'Inscrições de Staff/Oficiais',
    'owner_type_athlete_enrollment' => 'Inscrições de Atletas (Competições)',
    'owner_type_insurance' => 'Seguros',
    'use_default' => 'Usar Padrão',

    // Document type
    'document_type' => 'Tipo de Documento',
    'invoice_fatura' => 'Fatura (FT)',
    'invoice_receipt_fatura_recibo' => 'Fatura-Recibo (FR)',
    'document_type_help' => 'Fatura-Recibo combina fatura + pagamento. Requer que a série de documentos tenha Fatura-Recibo ativada no Moloni.',

    // Document status (draft vs finalized)
    'document_status' => 'Estado do Documento',
    'status_finalized' => 'Finalizado (Fechado)',
    'status_draft' => 'Rascunho',
    'document_status_help' => 'Documentos em rascunho requerem finalização manual no Moloni antes de se tornarem documentos fiscais válidos. Use para revisão antes de fechar.',

    // Missing invoices
    'missing_invoices' => 'Documentos Sem Fatura',
    'documents' => 'documentos',
    'create_invoices' => 'Criar Faturas',
    'create_invoice' => 'Criar Fatura',
    'owner' => 'Titular',
    'paid_date' => 'Data de Pagamento',
    'no_owner' => 'Sem titular',
    'showing_first_50' => 'A mostrar os primeiros 50 de :count documentos. Os restantes serão mostrados após estes serem processados.',
    'no_missing_invoices' => 'Todos os documentos pagos têm as suas faturas Moloni criadas.',

    // Failure notification
    'notification_invoice_failed_subject' => 'Falha na Criação de Fatura Moloni',
    'notification_invoice_failed_greeting' => 'Alerta de Geração de Faturas',
    'notification_invoice_failed_intro' => 'O sistema não conseguiu criar uma fatura Moloni para o documento :document após várias tentativas.',
    'notification_invoice_failed_error' => 'Erro: :error',
    'notification_invoice_failed_attempts' => 'O sistema tentou :attempts vezes antes de desistir.',
    'notification_invoice_failed_action' => 'Ver Configurações Moloni',
    'notification_invoice_failed_document_link' => 'Pode ver o documento em: :url',
    'notification_invoice_failed_database' => 'Falha ao criar fatura Moloni para o documento :document',

    // Invoice generation rules
    'invoice_generation_rules' => 'Regras de Geração de Faturas',
    'invoice_generation_rules_description' => 'Selecione quais tipos de detalhe de documento devem gerar faturas Moloni. Tipos não selecionados não criarão faturas.',
    'invoice_generation_rules_saved' => 'Regras de geração de faturas guardadas com sucesso.',
    'save_invoice_rules' => 'Guardar Regras de Faturação',
    'require_all_details_enabled' => 'Exigir que todos os tipos de detalhe estejam ativados',
    'require_all_details_enabled_help' => 'Se ativado, as faturas só serão criadas quando TODOS os tipos de detalhe no documento estiverem ativados. Se desativado, as faturas são criadas quando QUALQUER tipo ativado estiver presente.',

    // Committee-based document series
    'committee_document_series' => 'Séries de Documentos por Comité',
    'committee_document_series_description' => 'Selecione a série de documentos para licenças e certificações com base no seu comité. Esta configuração tem prioridade sobre o mapeamento por tipo abaixo.',
    'committee_diving' => 'Comité de Mergulho',
    'committee_scientific' => 'Comité Científico',
    'committee_sport' => 'Comité Desportivo',
    'committee_divingservices' => 'Comité Serviços de Mergulho',

    // Warnings and validation
    'warning' => 'Aviso',
    'document_set_not_in_cache' => 'A série de documentos configurada (ID: :id) não existe nos dados sincronizados.',
    'sync_to_refresh' => 'Clique em "Sincronizar" para atualizar as séries de documentos disponíveis do Moloni.',
    'not_in_cache' => 'Fora dos dados sincronizados',
    'no_at_codes' => 'Sem códigos AT - inválida para faturas',

    // Activity log
    'activity_log_description' => 'Atividade recente de faturas e sincronização',
    'invoice_created_title' => 'Fatura Criada',
    'invoice_failed_title' => 'Falha na Fatura',
    'sync_completed_title' => 'Sincronização Concluida',
    'sync_failed_title' => 'Falha na Sincronização',
    'success' => 'Sucesso',
    'failed' => 'Falhou',
    'view_document' => 'Ver Documento',
    'companies_synced' => 'empresas',
    'series_synced' => 'series',
    'taxes_synced' => 'taxas',
    'categories_synced' => 'categorias',
];
