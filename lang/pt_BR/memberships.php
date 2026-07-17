<?php

return [
    // Subscription Creation
    'subscription_created_successfully' => 'Subscrição criada com sucesso. Por favor, proceda ao pagamento.',
    'subscription_created_pending_payment' => 'Subscrição criada com sucesso. Por favor, proceda ao pagamento.',
    'insurance_subscription_created_pending_payment' => 'Subscrição de seguro criada com sucesso! Por favor, complete o pagamento para ativar a sua cobertura de seguro.',
    'subscription_created_free' => 'Subscrição do pacote de adesão realizada com sucesso.',
    'subscription_creation_error' => 'Ocorreu um erro ao processar a sua subscrição. Por favor, tente novamente.',
    'subscription_already_pending' => 'Já tem uma subscrição pendente para este pacote.',
    'subscription_already_pending_payment' => 'Já tem uma subscrição pendente para este pacote. Por favor, complete o pagamento para ativá-la.',

    // Document Generation
    'affiliation_description' => 'Filiação: :name - :federation',
    'insurance_description' => 'Seguro: :name',
    'subscription_document_notes' => 'Subscrição do pacote: :package',
    'bulk_subscription_document_note' => 'Subscrição em massa para :count membros - Pacote: :package',

    // Document Observer
    'activating_subscription_after_payment' => 'Ativando subscrição após pagamento',
    'subscription_activated' => 'Subscrição ativada',

    // Payment Flow
    'payment_required' => 'Pagamento necessário para completar a subscrição',
    'proceed_to_payment' => 'Por favor, proceda ao pagamento para ativar a sua subscrição',

    // Validation Messages
    'package_selection_required' => 'É necessário selecionar um pacote de adesão.',
    'package_selection_invalid' => 'O pacote de adesão selecionado não é válido.',
    'invalid_member_type' => 'Tipo de membro inválido para subscrição.',
    'no_validation_affiliation_for_insurance' => 'É necessária uma filiação de validação ativa para subscrever pacotes apenas de seguro.',
    'no_active_affiliation_found' => 'Nenhuma filiação ativa encontrada. Uma filiação de validação é necessária.',
    'duplicate_affiliation_plans' => 'Já tem uma subscrição ativa para os seguintes planos de filiação: :plans',
    'all_affiliation_plans_already_active' => 'Já tem uma subscrição ativa para todos os planos de filiação neste pacote: :plans',
    'duplicate_insurance_plans' => 'Já tem uma subscrição ativa ou pendente para os seguintes planos de seguro: :plans',
    'insufficient_privileges_for_request_type' => 'Privilégios insuficientes para este tipo de pedido.',
    'validation_plan_required_for_non_validation_packages' => 'O indivíduo deve ter um plano de validação ativo para subscrever este pacote de filiação.',

    // Renewal
    'subscription_renewed_successfully' => 'Subscrição renovada com sucesso.',

    // Individual Profile Messages
    'complete_profile_before_managing_subscriptions' => 'Por favor, complete o seu perfil individual antes de gerir subscrições.',

    // Affiliation Plan Business Scenarios
    'business_scenarios' => [
        'direct_individual' => [
            'label' => 'Subscrição Direta Individual',
            'description' => 'Indivíduos subscrevem diretamente este plano',
            'example' => 'Exemplo: Filiação anual pessoal, tarifas estudante',
        ],
        'entity_for_individuals' => [
            'label' => 'Entidade subscreve para Indivíduos',
            'description' => 'Entidades (clubes, escolas) subscrevem este plano PARA os seus membros individuais',
            'example' => 'Exemplo: Clube paga filiações de atletas, centro de mergulho paga certificações de alunos',
        ],
        'direct_entity' => [
            'label' => 'Subscrição Direta de Entidade',
            'description' => 'Entidades subscrevem este plano para si próprias (filiação institucional)',
            'example' => 'Exemplo: Filiação institucional de clube, certificação de centro de mergulho',
        ],
        'flexible' => [
            'label' => 'Plano Flexível',
            'description' => 'Pode ser usado tanto por indivíduos como por entidades com preçários diferentes',
            'example' => 'Exemplo: Plano premium com tarifas individuais e institucionais',
        ],
    ],

    // Form Labels
    'choose_business_scenario' => 'Escolha o Cenário de Negócio',
    'business_scenario_help' => 'Selecione que tipo de plano de subscrição pretende criar. Isto determina quem pode subscrever e como funciona o preçário.',
    'plan_name' => 'Nome do Plano',
    'plan_name_help' => 'Escolha um nome claro e descritivo',
    'select_federation' => 'Selecione federação...',
    'pricing' => 'Preçário',
    'fee_individual_member' => 'Taxa cobrada por membro individual',
    'fee_individual_subscription' => 'Taxa quando subscrito por indivíduos',
    'fee_entity_institution' => 'Taxa cobrada à entidade (instituição)',
    'fee_entity_subscription' => 'Taxa quando subscrito por entidades',
    'free_plan_option' => 'Este é um plano gratuito (definir taxas para €0)',
    'immediate_availability' => 'Deixe vazio para disponibilidade imediata',
    'no_expiration' => 'Deixe vazio para sem expiração',
    'description_help' => 'Forneça informações detalhadas sobre o que este plano inclui, requisitos, benefícios, etc.',
    'pdf_documents' => 'Documentos PDF',
    'upload_documents_help' => 'Carregue termos, condições ou outros documentos relevantes. Máx. 10MB cada.',
    'current_attachments' => 'Anexos Atuais',
    'uncheck_remove_files' => 'Desmarque para remover arquivos',
    'plan_summary' => 'Resumo do Plano',
    'usage' => 'Utilização',
    'create_plan_help' => 'Crie um novo plano de filiação escolhendo o cenário de negócio que melhor descreve como este plano deve funcionar. O formulário irá guiá-lo através das configurações apropriadas.',
    'edit_plan_help' => 'Edite os detalhes deste plano de filiação. O cenário de negócio determina a estrutura do plano e as opções de preçário.',
    'complete_profile_before_selecting_subscription' => 'Por favor, complete o seu perfil individual antes de selecionar uma subscrição.',
    'complete_profile_before_purchasing_subscription' => 'Por favor, complete o seu perfil individual antes de adquirir uma subscrição.',
    'complete_profile_before_viewing_history' => 'Por favor, complete o seu perfil individual antes de ver o histórico de subscrições.',
    'please_login_to_continue' => 'Por favor, faça login para continuar.',
    'profile_issue_contact_support' => 'Houve um problema com o seu perfil. Por favor, contacte o suporte.',
    'subscription_not_eligible_for_renewal' => 'Esta subscrição não é elegível para renovação.',
    'renewal_error_try_again' => 'Ocorreu um erro ao renovar a sua subscrição. Por favor, tente novamente.',
    'duplicate_affiliation_plans_error' => 'Já tem uma subscrição ativa para um ou mais planos de filiação neste pacote.',

    // Official Document Requirements
    'missing_official_documents' => 'Não pode subscrever este pacote porque requer documentos oficiais que não carregou ou que não estão ativos.',
    'insurance_requires_document' => 'Obrigatório: :document para :insurance.',

    // Validation Plan
    'validation_plan' => 'Plano de Validação',
    'validation_plan_help' => 'Ativar privilégios avançados para subscritores deste plano',
    'validation_plan_enables' => 'Planos de validação permitem',
    'insurance_requests' => 'Solicitar apólices de seguro',
    'license_requests' => 'Solicitar licenças e certificações',
    'entity_member_licenses' => 'Para entidades: Solicitar licenças para os seus membros',

    // Validation Plan Error Messages
    'insurance_subscription_not_authorized' => 'Subscrição de seguro não autorizada: :reason',
    'license_request_not_authorized' => 'Pedido de licença não autorizado: :reason',
    'entity_member_insurance_not_authorized' => 'Atribuição de seguro a membro da entidade não autorizada: :reason',
    'entity_member_license_not_authorized' => 'Pedido de licença para membro da entidade não autorizado: :reason',

    // Validation Plan Privilege Messages
    'validation_plan_no_insurance_privileges' => 'O seu plano de adesão atual não inclui privilégios para solicitar seguros',
    'validation_plan_no_license_privileges' => 'O seu plano de adesão atual não inclui privilégios para solicitar licenças',
    'validation_plan_no_entity_member_licenses' => 'O seu plano de adesão atual não permite solicitar licenças para membros da entidade',
    'validation_plan_no_entity_member_subscriptions' => 'O seu plano de adesão atual não permite subscrever membros em pacotes',

    // Validation Plan UI Messages
    'validation_plan_required' => 'Plano de Validação Necessário',
    'access_restricted' => 'Acesso Restrito',
    'contact_federation_validation_plan' => 'Por favor, atualize o seu plano de filiação para ativar as funcionalidades de subscrição de membros.',
    'validation_plan_required_message' => 'É necessário um plano de validação para subscrever pacotes de serviços a membros.',
    'no_active_affiliation_found' => 'Nenhuma filiação ativa encontrada',
    'entity_member_subscriptions_not_authorized' => 'Não pode subscrever membros em pacotes. :reason',
    'invalid_member_type' => 'Tipo de membro inválido',
    'insufficient_privileges_for_request_type' => 'Privilégios insuficientes para este tipo de pedido',

    // Membership states
    'states' => [
        'active' => 'Ativo',
        'pending' => 'Pendente',
        'expired' => 'Expirado',
        'canceled' => 'Cancelado',
    ],

    // Member subscription states
    'subscription_states' => [
        'active' => 'Ativa',
        'pending' => 'Pendente',
        'pending_payment' => 'Pagamento Pendente',
        'expired' => 'Expirada',
    ],

    // Subscription page
    'affiliations' => 'Filiações',
    'active_affiliations' => 'Filiações Ativas',
    'included_plans' => 'Planos Incluídos',
    'affiliation_plans' => 'Planos de Filiação',

    // Member subscriptions
    'member_subscriptions' => [
        'created_successfully' => 'Subscrição de membro criada com sucesso.',
        'renewed_successfully' => 'Subscrição de membro renovada com sucesso.',
        'delete' => 'Apagar',
        'deleted_successfully' => 'Subscrição de membro apagada com sucesso.',
        'delete_failed' => 'Falha ao apagar a subscrição de membro. Por favor, tente novamente.',
        'confirm_delete_title' => 'Apagar Subscrição de Membro',
        'confirm_delete_warning' => 'Esta ação irá apagar permanentemente a subscrição do membro e todas as filiações e seguros relacionados. Esta ação não pode ser revertida.',
        'will_delete_related' => 'Isto irá apagar :affiliations filiação(ões) e :insurances seguro(s)',
        'delete_confirm' => 'Apagar Subscrição',
        'change_status' => 'Alterar Estado',
        'change_status_title' => 'Alterar Estado da Subscrição',
        'change_status_warning' => 'Isto irá apenas alterar o estado da subscrição. Documentos de pagamento, filiações e seguros NÃO serão afetados.',
        'new_status' => 'Novo Estado',
        'update_status' => 'Atualizar Estado',
        'status_updated_successfully' => 'Estado da subscrição de membro atualizado com sucesso.',
        'status_update_failed' => 'Falha ao atualizar o estado da subscrição de membro.',
        'pending_payment' => 'Pagamento Pendente',
    ],

    // Notifications
    'subscription_activated_notification' => 'A sua subscrição de :package foi ativada e é válida até :date.',

    // Table headers
    'title' => 'Quotizações',
    'name' => 'Nome',
    'plans' => 'Planos',
    'status' => 'Estado',
    'expiration_date' => 'Data de expiração',
    'organizations_membership_association' => 'Associação de Quotização de Organizações',
];
