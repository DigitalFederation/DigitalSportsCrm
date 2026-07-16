<?php

$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalName = config('branding.international.name', 'International Federation');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    // Page titles
    'licenses' => 'Licenças',
    'my_licenses_description' => 'Aqui pode consultar todas as suas licenças e adquirir novas licenças de membro',
    'view_my_licenses' => 'Ver As Minhas Licenças',
    'no_federation_association_description' => 'Não está associado a nenhuma federação. Por favor, contacte o administrador da sua federação para estabelecer esta associação antes de adquirir licenças.',
    'no_international_license_access_description' => 'Não está associado a uma federação que tenha acordos de licenças internacionais. Apenas membros de federações com acordos internacionais podem adquirir estas licenças.',

    // Tab sections
    'basic_information' => 'Informação Básica',
    'roles_permissions' => 'Funções e Permissões',
    'requirements' => 'Requisitos',
    'pricing' => 'Preços',
    'availability' => 'Disponibilidade',
    'advanced_settings' => 'Configurações Avançadas',

    // Document requirements sections
    'diving_professionals' => 'Profissionais de Mergulho',

    // Purchase page titles and headers
    'Purchase License' => 'Adquirir Licença',
    'Manage Licenses' => 'Gerir Licenças',
    'Manage Licenses for' => 'Gerir Licenças para',
    'License Purchased Successfully!' => 'Licença Adquirida com Sucesso!',
    'Purchase Successful!' => 'Aquisição Realizada com Sucesso!',
    'Purchase Successful' => 'Aquisição Realizada',
    'order_details' => 'Detalhes do Pedido',

    // Page descriptions
    'Select and purchase a license for yourself' => 'Selecione e adquira uma licença para si',
    'Purchase licenses for your entity or members' => 'Adquira licenças para a sua entidade ou membros',

    // Information messages
    'Information' => 'Informação',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. Please ensure your profile information is complete before proceeding.' => 'Selecione uma licença e proceda ao pagamento. A sua licença será ativada automaticamente após confirmação do pagamento. Certifique-se de que as informações do seu perfil estão completas antes de prosseguir.',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. For group purchases, you can select multiple members to receive the same license.' => 'Selecione uma licença e proceda ao pagamento. A sua licença será ativada automaticamente após confirmação do pagamento. Para aquisições em grupo, pode selecionar múltiplos membros para receberem a mesma licença.',

    // Form labels and options
    'Select Federation' => 'Selecionar Federação',
    'Select a federation...' => 'Selecione uma federação...',
    'Select License' => 'Selecionar Licença',
    'Purchase Type' => 'Tipo de Aquisição',
    'Individual License' => 'Licença Individual',
    'Group Purchase' => 'Aquisição em Grupo',
    'Select Member' => 'Selecionar Membro',
    'Select Members' => 'Selecionar Membros',
    'Select a member...' => 'Selecione um membro...',

    // Purchase type descriptions
    'Purchase license for one specific member' => 'Adquirir licença para um membro específico',
    'Purchase licenses for multiple members' => 'Adquirir licenças para múltiplos membros',

    // License information
    'License' => 'Licença',
    'License Code' => 'Código da Licença',
    'License Holder' => 'Titular da Licença',
    'License Information' => 'Informações da Licença',
    'per license' => 'por licença',
    'license' => 'Licença',
    'start_date' => 'Data de Início',
    'expiry_date' => 'Data de Expiração',
    'status' => 'Estado',

    // Purchase summary
    'Purchase Summary' => 'Resumo do registo',
    'Purchase Details' => 'Detalhes do registo',
    'Entity' => 'Entidade',
    'Federation' => 'Federação',
    'Number of Members' => 'Número de Membros',
    'Price per License' => 'Preço por Licença',
    'Total' => 'Total',
    'Total Amount' => 'Valor Total',
    'Total Paid' => 'Total Pago',

    // Status and dates
    'Status' => 'Estado',
    'Active' => 'Ativo',
    'Payment Confirmed' => 'Pagamento Confirmado',
    'Issue Date' => 'Data de Emissão',
    'Expiration Date' => 'Data de Expiração',
    'Today' => 'Hoje',
    'Permanent' => 'Permanente',

    // International and codes
    'Pending Assignment' => 'Atribuição Pendente',
    'Order Number' => 'Número do Pedido',

    // Success messages
    'Your license has been activated and is ready to use' => 'A sua licença foi ativada e está pronta a usar',
    'Your license purchase has been completed successfully' => 'A aquisição da sua licença foi concluída com sucesso',
    'All selected members have been automatically licensed' => 'Todos os membros selecionados foram automaticamente licenciados',
    'Your entity license has been automatically activated' => 'A licença da sua entidade foi automaticamente ativada',

    // Certificate information
    'Your License Certificate' => 'O Seu Certificado de Licença',
    'Your license certificate is now available for download' => 'O seu certificado de licença está agora disponível para download',
    'License certificates are now available for download' => 'Os certificados de licença estão agora disponíveis para download',
    'A confirmation email has been sent to your registered email address' => 'Foi enviado um email de confirmação para o seu endereço de email registado',
    'You will receive email confirmation shortly' => 'Receberá uma confirmação por email em breve',

    // Next steps and information
    'What happens next?' => 'O que acontece a seguir?',
    'Important Information' => 'Informação Importante',
    'Remember to renew before expiration date' => 'Lembre-se de renovar antes da data de expiração',

    // Action buttons
    'View My Licenses' => 'Ver as Minhas Licenças',
    'Download Invoice' => 'Descarregar Fatura',
    'Download Certificate' => 'Descarregar Certificado',
    'Back to Dashboard' => 'Voltar ao Painel',

    // Error messages
    'no_license_purchase_found' => 'Nenhuma aquisição de licença encontrada.',
    'entity_license_required_for_members' => 'A sua entidade deve ter uma licença de entidade ativa antes de poder adquirir licenças para membros. Por favor, adquira primeiro uma licença de entidade.',
    'entity_sport_license_required' => 'A sua entidade deve ter uma licença de entidade ativa para esta modalidade antes de poder adquirir licenças de membros para a mesma. Por favor, adquira primeiro uma licença de entidade para esta modalidade.',
    'No licenses available' => 'Nenhuma licença disponível',
    'There are no licenses available for purchase in this federation at the moment.' => 'Não existem licenças disponíveis para aquisição nesta federação no momento.',
    'There are no licenses available for entity purchase at the moment.' => 'Não existem licenças disponíveis para aquisição de entidade no momento.',
    'No Federation Association' => 'Sem Associação à Federação',
    'You are not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'Não está associado a nenhuma federação. Contacte o administrador da sua federação para estabelecer esta associação antes de adquirir licenças.',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'A sua entidade não está associada a nenhuma federação. Contacte o administrador da sua federação para estabelecer esta associação antes de adquirir licenças.',
    'No federation' => 'Sem federação',

    // Dynamic messages with parameters
    'Purchase for' => 'Adquirir por',
    'Purchase for €:amount' => 'Adquirir por €:amount',
    'Purchase for :amount' => 'Adquirir por :amount',
    'Request Free License' => 'Solicitar Licença Gratuita',
    ':count members selected' => ':count membros selecionados',
    'This license certifies you for: :role' => 'Esta licença certifica-o para: :role',
    'Valid for sport: :sport' => 'Válido para desporto: :sport',
    'members' => 'membros',
    'Members' => 'Membros',

    // Federation License Manager
    'Select which licenses this federation can offer to its member entities.' => 'Selecione quais licenças esta federação pode oferecer às suas entidades membros.',
    'Search Licenses' => 'Pesquisar Licenças',
    'Search by name or code...' => 'Pesquisar por nome ou código...',
    'Filter by Committee' => 'Filtrar por Comité',
    'All Committees' => 'Todos os Comités',
    'selected' => 'selecionado(s)',
    'International' => 'Internacional',
    'No licenses found matching your filters.' => 'Nenhuma licença encontrada com os seus filtros.',
    'No licenses available.' => 'Nenhuma licença disponível.',
    'license(s) selected' => 'licença(s) selecionada(s)',
    'Cancel' => 'Cancelar',
    'Save Changes' => 'Guardar Alterações',
    'Licenses updated successfully!' => 'Licenças atualizadas com sucesso!',

    // Table headers and search
    'Search licenses...' => 'Pesquisar licenças...',
    'Search members...' => 'Pesquisar membros...',
    'License Name' => 'Nome da Licença',
    'Code' => 'Código',
    'Price' => 'Preço',
    'Name' => 'Nome',
    'Email' => 'Email',
    'Member Code' => 'Nº ID',
    'Active Licenses' => 'Licenças Ativas',
    'No licenses found matching your search.' => 'Nenhuma licença encontrada correspondente à sua pesquisa.',
    'No members found matching your search.' => 'Nenhum membro encontrado correspondente à sua pesquisa.',
    'Selected' => 'Selecionado',

    // License type selection
    'Who is this license for?' => 'Para quem é esta licença?',
    'Entity License' => 'Licença de Entidade',
    'Member Licenses' => 'Licenças de Membros',
    'Purchase a license for your entity (club, school, center)' => 'Adquirir uma licença para a sua entidade (clube, escola, centro)',
    'Purchase licenses for your entity members' => 'Adquirir licenças para os membros da sua entidade',
    'License Type' => 'Tipo de Licença',

    // Success page translations
    'License Purchased' => 'Licença Adquirida',
    'License Purchased Successfully!' => 'Licença Adquirida com Sucesso!',
    'Your license has been activated and is ready to use' => 'A sua licença foi ativada e está pronta a usar',
    'Your license purchase is being processed. You will receive a confirmation once payment is complete.' => 'A sua aquisição de licença está a ser processada. Receberá uma confirmação assim que o pagamento for concluído.',
    'License Information' => 'Informações da Licença',
    'License Holder' => 'Titular da Licença',
    'Pending Assignment' => 'Atribuição Pendente',
    'Issue Date' => 'Data de Emissão',
    'Expiration Date' => 'Data de Expiração',
    'Today' => 'Hoje',
    'Permanent' => 'Permanente',
    'Total Paid' => 'Total Pago',
    'Status' => 'Estado',
    'Active' => 'Ativo',
    'Pending Payment' => 'Pagamento Pendente',
    'Your License Certificate' => 'O Seu Certificado de Licença',
    'Your license certificate is now available for download' => 'O seu certificado de licença está agora disponível para download',
    'Your license certificate will be available after payment confirmation' => 'O seu certificado de licença estará disponível após confirmação do pagamento',
    'You can view and manage your license in the "My Licenses" section' => 'Pode visualizar e gerir a sua licença na secção "As Minhas Licenças"',
    'A confirmation email has been sent to your registered email address' => 'Foi enviado um email de confirmação para o seu endereço de email registado',
    'You will receive a confirmation email once payment is processed' => 'Receberá um email de confirmação assim que o pagamento for processado',
    'Important Information' => 'Informação Importante',
    'This license certifies you for: :role' => 'Esta licença certifica-o para: :role',
    'Valid for sport: :sport' => 'Válido para desporto: :sport',
    'Remember to renew before expiration date' => 'Lembre-se de renovar antes da data de expiração',
    'View My Licenses' => 'Ver as Minhas Licenças',
    'Download Invoice' => 'Descarregar Fatura',
    'Download Certificate' => 'Descarregar Certificado',
    'Back to Dashboard' => 'Voltar ao Painel',
    'Complete Payment' => 'Completar Pagamento',
    'Payment Required' => 'Pagamento Necessário',
    'Please complete your payment to activate your license' => 'Por favor complete o seu pagamento para ativar a sua licença',
    'License Purchase Initiated!' => 'Aquisição de Licença Iniciada!',
    'Your license purchase is being processed. You will receive a confirmation once payment is complete.' => 'A sua aquisição de licença está a ser processada. Receberá uma confirmação assim que o pagamento for concluído.',
    'Your license is pending payment to be activated' => 'A sua licença está pendente de pagamento para ser ativada',
    'Please complete the payment to activate your license and download the certificate' => 'Por favor complete o pagamento para ativar a sua licença e descarregar o certificado',
    'An invoice has been generated and is available for download' => 'Foi gerada uma fatura e está disponível para download',
    'You can view and manage your license in the My Licenses section' => 'Pode visualizar e gerir a sua licença na secção As Minhas Licenças',
    'Complete Payment' => 'Completar Pagamento',
    'Payment integration coming soon' => 'Integração de pagamento em breve',
    'There are no licenses available for purchase at the moment.' => 'Não existem licenças disponíveis para aquisição no momento.',

    // New shopping cart UI translations
    'Select a License' => 'Selecionar uma Licença',
    'Choose a license from the list to see purchase details' => 'Escolha uma licença da lista para ver os detalhes da aquisição',
    'Selected License' => 'Licença Selecionada',
    'licenses found' => 'licenças encontradas',
    'Sort by Name' => 'Ordenar por Nome',
    'Sort by Price' => 'Ordenar por Preço',
    'Sort by Committee' => 'Ordenar por Comité',
    'Secure Payment Processing' => 'Processamento de Pagamento Seguro',

    // Free license translations
    'Free' => 'Grátis',
    'Request Free License' => 'Solicitar Licença Grátis',
    'Price information not available' => 'Informação de preço não disponível',
    'Please contact support for pricing details' => 'Por favor contacte o suporte para detalhes de preços',

    // Affiliation requirement translations
    'Active Affiliation Required' => 'Filiação Ativa Necessária',
    'Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.' => 'A sua entidade deve ter a filiação ativa para subscrever licenças. Certifique-se que a sua entidade tem a filiação regularizada.',
    'You must have an active affiliation (membership package) to purchase licenses. Please ensure your individual membership is active and paid before proceeding.' => 'Deve ter uma filiação ativa (pacote de associação) para adquirir licenças. Certifique-se de que a sua associação individual está ativa e paga antes de prosseguir.',

    // New translations for URL-based license type selection
    'Purchase Licenses for Members' => 'Adquirir Licenças para Membros',
    'Purchase Entity License' => 'Adquirir Licença de Entidade',
    'Select members and purchase licenses on their behalf' => 'Selecione membros e adquira licenças em seu nome',
    'Purchase a license for your organization' => 'Adquira uma licença para a sua organização',
    'Switch to Entity License Purchase' => 'Mudar para Aquisição de Licença de Entidade',
    'Switch to Member License Purchase' => 'Mudar para Aquisição de Licença de Membros',

    // Member affiliation requirement translations
    'members_must_have_active_affiliations' => 'Os membros devem ter filiações ativas para poder adquirir licenças. Por favor, certifique-se de que todos os membros tem filiações ativas antes de submeter o pedido.',

    // Duplicate license prevention
    'already_has_license' => 'Já possui uma licença deste tipo com estado :status. Não é possível adquirir uma nova licença enquanto a existente estiver ativa ou pendente.',
    'missing_required_documents_detailed' => 'Não é possível solicitar esta licença. Os seguintes documentos obrigatórios estão em falta: :documents. Por favor, carregue estes documentos na secção de Documentos Oficiais antes de solicitar esta licença.',
    'members_already_have_licenses' => 'Alguns membros já possuem licenças deste tipo ativas ou pendentes. Por favor, remova esses membros da seleção ou aguarde a expiração das licenças existentes.',
    'Entity License Purchase' => 'Aquisição de Licença de Entidade',
    'Member License Purchase' => 'Aquisição de Licença de Membros',
    'You are purchasing a license for your entity (club, school, center)' => 'Está a adquirir uma licença para a sua entidade (clube, escola, centro)',
    'You are purchasing licenses for your entity members' => 'Está a adquirir licenças para os membros da sua entidade',

    // Debug information messages
    'cannot_proceed_with_purchase' => 'Não é possível prosseguir com a aquisição:',
    'entity_no_active_affiliation' => 'A entidade não tem filiação ativa',
    'no_license_selected' => 'Nenhuma licença selecionada',
    'price_not_calculated' => 'Preço não calculado',
    'calculated_price' => 'preço calculado',
    'no_members_selected' => 'Nenhum membro selecionado',
    'no_members_for_entity' => 'Nenhum membro encontrado para esta entidade. Verifique se a sua entidade tem indivíduos associados.',
    'validation_plan' => 'Plano de validação',

    // Affiliation messages
    'Active Affiliation Required' => 'Filiação Ativa Necessária',
    'Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.' => 'A sua entidade deve ter uma filiação ativa (pacote de associação) para comprar licenças. Por favor, certifique-se de que a associação da sua entidade está ativa e paga antes de prosseguir.',

    // Certification requirement messages
    'missing_required_certifications' => 'Não possui as certificações obrigatórias para esta licença. Certificações em falta: :certifications',
    'members_missing_required_certifications' => 'Os seguintes membros não possuem as certificações obrigatórias: :members',
    'license_requirements' => 'Requisitos da Licença',
    'required_certifications' => 'Certificações obrigatórias',
    'required_documents' => 'Documentos obrigatórios',
    'member_missing_certifications' => 'Faltam certificações: :certifications',
    'member_missing_documents' => 'Faltam documentos: :documents',
    'member_must_have_active_affiliation' => 'O membro deve ter uma filiação ativa',
    'show_ineligible_members' => 'Mostrar membros não elegíveis',
    'hide_ineligible_members' => 'Esconder membros não elegíveis',
    'member_not_eligible' => 'Este membro não cumpre os requisitos',
    'no_eligible_members' => 'Nenhum membro elegível para esta licença',
    'some_members_ineligible' => ':eligible de :total membros são elegíveis para esta licença',

    // Sport entity license requirement
    'Your entity must have an active entity license for a sport before you can purchase member licenses for that sport.' => 'A sua entidade deve ter uma licença de entidade ativa para um desporto antes de poder adquirir licenças de membros para esse desporto.',

    // Additional translations for error messages
    'entity' => 'entidade',
    'individual' => 'individual',
    'license_cannot_be_purchased_by' => 'Esta licença não pode ser adquirida por :type',
    'license_request_not_authorized' => 'Pedido de licença não autorizado: :reason',
    'license_parameter_null' => 'Parâmetro de licença é nulo',
    'license_missing_properties' => 'A licença não possui as propriedades obrigatórias (id ou license_code)',
    'cannot_determine_federation' => 'Não é possível determinar a federação para a aquisição da licença',
    'license_price_not_configured' => 'Preço da licença não configurado para este tipo de comprador',

    // License fields
    'license_type' => 'Tipo de Licença',
    'license_number' => 'Número da Licença',
    'valid_until' => 'Válido Até',
    'acceptance_date' => 'Data de Aceitação',
    'issue_date' => 'Data de Emissão',
    'expiration_date' => 'Data de Expiração',

    // Error messages for purchase flow
    'This license is not free' => 'Esta licença não é gratuita.',
    'This license cannot be purchased with this method' => 'Esta licença não pode ser adquirida com este método.',

    // License status messages
    'Your profile already has this Active License' => 'O seu perfil já tem esta Licença Ativa',
    'Your license is pending payment' => 'A sua licença está pendente de pagamento',
    'Your license is pending admin validation' => 'A sua licença está pendente de validação',
    'Your license is pending technical director approval' => 'A sua licença está pendente de aprovação do Diretor Técnico',
    'Your license is being processed' => 'A sua licença está a ser processada',

    // New form translations
    'Search licenses' => 'Pesquisar Licenças',
    'Sport Committee' => 'Modalidade',
    'All Sports' => 'Todas as Modalidades',
    'Role' => 'Papel',
    'Select' => 'Selecionar',
    'Purchase' => 'Adquirir',
    'Request' => 'Solicitar',
    'Contact Support' => 'Contactar Suporte',
    'Membership Required' => 'Filiação Necessária',

    // Admin validation
    'license_pending_validation_requires_approval' => 'Licença está pendente de validação e requer aprovação do administrador.',
    'validate_and_approve' => 'Validar e Aprovar',
    'reject_validation' => 'Rejeitar Validação',

    // Entity pending licenses
    'entity_has_pending_licenses' => 'A sua entidade tem licenças pendentes aguardando pagamento',
    'invitations_available_after_payment' => 'Os convites para atletas e treinadores estarão disponíveis após o pagamento da licença ser concluído',
    'complete_payment_to_enable_invitations' => 'Complete o pagamento para ativar suas licenças e habilitar os recursos de convite',
    'pending_licenses_for_sports' => 'Licenças pendentes para: :sports',
    'license_approved_successfully' => 'Licença aprovada com sucesso.',
    'error_approving_license' => 'Erro ao aprovar licença: ',
    'license_not_in_approvable_state' => 'A licença não está num estado que permite aprovação',
    'license_validation_rejected' => 'Validação da licença rejeitada',
    'license_canceled' => 'Licença cancelada',
    'cannot_activate_unpaid_license' => 'Não é possível ativar a licença: o pagamento não foi concluído. Por favor, certifique-se de que o documento de pagamento associado está pago antes de ativar.',

    // License state translations
    'statuses' => [
        'ActiveLicenseAttributedState' => 'Ativo',
        'PendingLicenseAttributedState' => 'Pendente',
        'PendingTechnicalDirectorApprovalLicenseAttributedState' => 'Aguarda DT',
        'PendingValidationLicenseAttributedState' => 'Aguarda Admin',
        'CanceledLicenseAttributedState' => 'Cancelado',
        'SuspendedLicenseAttributedState' => 'Suspenso',
        'ExpiredLicenseAttributedState' => 'Expirado',
        'ProvisionalLicenseAttributedState' => 'Provisório',
    ],

    // State translations for the states themselves
    'states' => [
        'pending' => 'Pendente',
        'active' => 'Ativo',
        'expired' => 'Expirado',
        'suspended' => 'Suspenso',
        'canceled' => 'Cancelado',
        'provisional' => 'Provisório',
        'waiting_approval' => 'Aguardando Aprovação',
        'pending_validation' => 'Pendente de Validação',
        'pending_technical_director_approval' => 'Aguarda Aprovação do Diretor Técnico',
        'no_license' => 'Sem Licença',
    ],

    // International License Specific
    'Active Affiliation Required' => 'Filiação Ativa Necessária',
    'You must have an active affiliation (membership package) to purchase international licenses. Please ensure your individual membership is active and paid before proceeding.' => 'Deve ter uma filiação ativa (pacote de associação) para adquirir licenças internacionais. Certifique-se de que a sua associação individual está ativa e paga antes de prosseguir.',
    'Search international licenses' => 'Pesquisar licenças internacionais',
    'Search international licenses...' => 'Pesquisar licenças internacionais...',
    'international licenses found' => 'licenças internacionais encontradas',
    'International License' => 'Licença Internacional',
    'No international licenses available' => 'Sem licenças internacionais disponíveis',
    'No international licenses are currently available for your federation.' => 'Não existem licenças internacionais disponíveis para a sua federação.',
    'No international licenses match your search criteria.' => 'Nenhuma licença internacional corresponde aos seus critérios de pesquisa.',
    'Purchase International License' => 'Adquirir Licença Internacional',
    'International License Purchase Success' => 'Aquisição de Licença Internacional Bem-sucedida',
    'Purchase Initiated Successfully' => 'Compra Iniciada com Sucesso',
    'Your international license purchase has been initiated. Please complete the payment to activate your license.' => 'A sua compra de licença internacional foi iniciada. Complete o pagamento para ativar a sua licença.',
    'International License Details' => 'Detalhes da Licença Internacional',
    'View National Licenses' => 'Ver Licenças Nacionais',
    'Select and purchase an international license for yourself' => 'Selecione e adquira uma licença internacional para si',
    'No International License Access' => 'Sem Acesso a Licenças Internacionais',
    'Back to International Licenses' => 'Voltar às Licenças Internacionais',
    'View My International Licenses' => 'Ver Minhas Licenças Internacionais',
    'Purchase International Licenses for Members' => 'Adquirir Licenças Internacionais para Membros',
    'Select members and purchase international licenses on their behalf' => 'Selecione membros e adquira licenças internacionais em seu nome',
    'Purchase International Entity License' => 'Adquirir Licença Internacional de Entidade',
    'Purchase an international license for your organization' => 'Adquira uma licença internacional para a sua organização',
    'Switch to International Entity License Purchase' => 'Mudar para Aquisição de Licença Internacional de Entidade',
    'Switch to International Member License Purchase' => 'Mudar para Aquisição de Licença Internacional de Membros',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing international licenses.' => 'A sua entidade não está associada a nenhuma federação. Contacte o administrador da federação para estabelecer esta associação antes de adquirir licenças internacionais.',

    // Table headers
    'licenses_title' => 'Licenças',
    'name' => 'Nome',
    'license_name' => 'Nome da Licença',
    'year' => 'Ano',
    'actions' => 'Ações',
    'sport_commission' => 'Comissão Desportiva',
    'sport_categories' => 'Categorias Desportivas',
    'not_active' => 'Não Ativo',
    'assign_individual_license' => 'Atribuir Licença Individual',
    'assign_entity_license' => 'Atribuir Licença de Entidade',

    // Separated license page titles
    'Sport Club Licenses' => 'Licenças de Clube Desportivo',
    'Sport Licenses' => 'Licenças de Desporto',
    'International Entity Licenses' => "Licencas Entidade {$internationalName}",
    'International Professional Licenses' => "Licencas Profissional {$internationalName}",
    'Scientific Entity Licenses' => "Licencas Entidade {$internationalName}",
    'Scientific Professional Licenses' => "Licencas Profissional {$internationalName}",
    'Primary Diving Services Licenses' => "Licencas Servicos de Mergulho {$primaryShortName}",

    // Middleware error messages
    'entity_has_inactive_license' => 'A sua entidade tem uma licença de :committee, mas não está atualmente ativa. Por favor, certifique-se que a sua licença de :committee está ativa para aceder a esta funcionalidade.',
    'entity_needs_active_license' => 'A sua entidade precisa de uma licença de :committee ativa para aceder a esta funcionalidade. Por favor, contacte a sua federação para obter a licença necessária.',

    // License states
    'state_active' => 'Ativa',
    'state_pending' => 'Pendente',
    'state_canceled' => 'Cancelada',
    'state_provisional' => 'Provisória',
    'state_suspended' => 'Suspensa',
    'state_waiting_approval' => 'Aguarda Aprovação',
    'state_expired' => 'Expirada',
    'state_pending_validation' => 'Pendente de Validação',
    'state_pending_technical_director_approval' => 'Pendente de Aprovação do Diretor Técnico',

    // Payment status
    'payment_status' => 'Estado do Pagamento',
    'payment_status_paid' => 'Pago',
    'payment_status_pending_payment' => 'Pagamento Pendente',
    'payment_status_no_document' => 'Sem Documento',

    // Filter labels
    'filters' => [
        'first_name' => 'Nome',
        'surname' => 'Apelido',
        'member_number' => 'N. Filiado',
        'sport' => 'Modalidade',
        'entity_name' => 'Entidade',
    ],

    // Separated license purchase page titles and subtitles
    'Purchase Sport Club License' => 'Adquirir Licença de Clube Desportivo',
    'Purchase a sport license for your club' => 'Subscreva uma licença desportiva para o seu clube.',
    'Purchase Sport Licenses' => 'Adquirir Licenças de Desporto',
    'Select members and purchase sport licenses on their behalf' => 'Selecione membros e adquira licenças desportivas em seu nome',
    'Purchase International Entity License' => "Adquirir Licenca Entidade {$internationalName}",
    'Purchase an international license for your entity' => "Compre uma licenca {$internationalShortName} para a sua entidade",
    'Purchase International Professional Licenses' => "Adquirir Licencas Profissional {$internationalName}",
    'Select members and purchase international licenses on their behalf' => "Selecione membros e adquira licencas {$internationalShortName} em seu nome",
    'Purchase Scientific Entity License' => 'Adquirir Licença Entidade Científica',
    'Purchase a scientific license for your entity' => 'Compre uma licença científica para a sua entidade',
    'Purchase Scientific Professional Licenses' => 'Adquirir Licenças Profissional Científico',
    'Select members and purchase scientific licenses on their behalf' => 'Selecione membros e adquira licenças científicas em seu nome',
    'Purchase Primary Diving Services Licenses' => "Adquirir Licencas Servicos de Mergulho {$primaryShortName}",
    'Select members and purchase primary diving licenses on their behalf' => "Selecione membros e adquira licencas de mergulho {$primaryShortName} em seu nome",

    // Fallbacks genéricos por comité (usados quando um comité não define título/subtítulo de compra em config/committees.php).
    'Purchase :committee Entity License' => 'Adquirir Licença de Entidade :committee',
    'Purchase a :committee license for your entity' => 'Adquira uma licença :committee para a sua entidade',
    'Purchase :committee Licenses' => 'Adquirir Licenças :committee',
    'Select members and purchase :committee licenses on their behalf' => 'Selecione membros e adquira licenças :committee em seu nome',
    ':committee Entity Licenses' => 'Licenças de Entidade :committee',
    ':committee Professional Licenses' => 'Licenças Profissionais :committee',

    // Individual separated license purchase page titles
    'individual_sport_license_title' => 'Licenças de Desporto',
    'individual_sport_license_subtitle' => 'Adquirir licenças para árbitros e treinadores',
    'individual_national_diving_license_title' => "Profissional de Mergulho {$primaryShortName}",
    'individual_national_diving_license_subtitle' => "Adquirir licenca de profissional de mergulho {$primaryShortName}",
    'individual_cmas_diving_license_title' => "Profissional {$internationalShortName} Mergulho Recreativo",
    'individual_cmas_diving_license_subtitle' => "Adquirir licenca de profissional {$internationalShortName} mergulho recreativo",
    'individual_scientific_license_title' => "Profissional {$internationalShortName} Mergulho Cientifico",
    'individual_scientific_license_subtitle' => "Adquirir licenca de profissional {$internationalShortName} mergulho cientifico",

    // Individual separated licenses attributed page titles
    'individual_sport_licenses_title' => 'Licenças Desportivas',
    'individual_sport_licenses_subtitle' => 'As suas licenças desportivas de atleta, treinadores e oficiais técnicos',
    'individual_national_diving_licenses_title' => 'Licenças Profissionais de Mergulho',
    'individual_national_diving_licenses_subtitle' => 'As suas licenças profissionais de mergulho',
    'individual_national_diving_licenses_info' => 'Aqui poderá consultar e adquirir novas licenças profissionais de mergulho',
    'individual_cmas_diving_licenses_title' => "Licencas {$internationalName}",
    'individual_cmas_diving_licenses_subtitle' => '',
    'individual_cmas_diving_licenses_info' => "Aqui pode consultar todas as suas licencas anuais de profissional de mergulho {$internationalName}",
    'individual_scientific_licenses_title' => "Licencas {$internationalName}",
    'individual_scientific_licenses_subtitle' => '',
    'individual_scientific_licenses_info' => "Aqui pode consultar todas as suas licencas anuais de profissional de mergulho {$internationalName}",

    // Other individual license translations
    'individual_licenses_info' => 'Aqui pode consultar todas as suas licenças de atleta, treinadores e oficiais técnicos',
    'sport' => 'Modalidade',
    'category' => 'Categoria',

    // Federation separated license routes translations
    'federation_sport_entity_licenses_title' => 'Licenças de Clubes Desportivos',
    'federation_sport_entity_licenses_subtitle' => 'Licenças desportivas atribuídas a clubes da sua federação',
    'federation_sport_individual_licenses_title' => 'Licenças Desportivas Individuais',
    'federation_sport_individual_licenses_subtitle' => 'Licenças desportivas atribuídas a atletas e treinadores',
    'federation_national_diving_entity_licenses_title' => "Licencas de Centros de Mergulho {$primaryShortName}",
    'federation_national_diving_entity_licenses_subtitle' => "Licencas {$primaryShortName} atribuidas a centros de mergulho",
    'federation_national_diving_individual_licenses_title' => "Licencas de Profissionais de Mergulho {$primaryShortName}",
    'federation_national_diving_individual_licenses_subtitle' => "Licencas {$primaryShortName} atribuidas a profissionais de mergulho",
    'federation_cmas_diving_entity_licenses_title' => "Licencas de Centros {$internationalName}",
    'federation_cmas_diving_entity_licenses_subtitle' => 'Licenças internacionais atribuídas a centros de mergulho',
    'federation_cmas_diving_individual_licenses_title' => "Licencas de Profissionais {$internationalName}",
    'federation_cmas_diving_individual_licenses_subtitle' => 'Licenças internacionais atribuídas a profissionais de mergulho',
    'federation_scientific_entity_licenses_title' => 'Licenças de Centros de Mergulho Científico',
    'federation_scientific_entity_licenses_subtitle' => 'Licenças científicas atribuídas a centros de mergulho',
    'federation_scientific_individual_licenses_title' => 'Licenças de Profissionais de Mergulho Científico',
    'federation_scientific_individual_licenses_subtitle' => 'Licenças científicas atribuídas a profissionais de mergulho',

    // Admin separated license routes translations
    'admin_sport_entity_licenses_title' => 'Licenças de Clubes Desportivos',
    'admin_sport_entity_licenses_subtitle' => 'Todas as licenças desportivas atribuídas a clubes',
    'admin_sport_individual_licenses_title' => 'Licenças Desportivas Individuais',
    'admin_sport_individual_licenses_subtitle' => 'Todas as licenças desportivas atribuídas a atletas e treinadores',
    'admin_national_diving_entity_licenses_title' => "Licencas de Centros de Mergulho {$primaryShortName}",
    'admin_national_diving_entity_licenses_subtitle' => "Todas as licencas {$primaryShortName} atribuidas a centros de mergulho",
    'admin_national_diving_individual_licenses_title' => "Licencas de Profissionais de Mergulho {$primaryShortName}",
    'admin_national_diving_individual_licenses_subtitle' => "Todas as licencas {$primaryShortName} atribuidas a profissionais de mergulho",
    'admin_cmas_diving_entity_licenses_title' => 'Licenças de Entidades Internacionais',
    'admin_cmas_diving_entity_licenses_subtitle' => 'Todas as licenças internacionais atribuídas a entidades',
    'admin_cmas_diving_individual_licenses_title' => 'Licenças de Profissionais de Mergulho Recreativo Internacionais',
    'admin_cmas_diving_individual_licenses_subtitle' => 'Todas as licenças atribuídas a profissionais de mergulho recreativo internacionais',
    'admin_scientific_entity_licenses_title' => 'Licenças de Entidades Científicas',
    'admin_scientific_entity_licenses_subtitle' => 'Todas as licenças científicas atribuídas a entidades',
    'admin_scientific_individual_licenses_title' => 'Licenças de Profissionais de Mergulho Científico',
    'admin_scientific_individual_licenses_subtitle' => 'Todas as licenças científicas atribuídas a profissionais de mergulho',

    // Committee names (for translation)
    'Technical Committee' => 'Comité Técnico',
    'Scientific Committee' => 'Comité Científico',

    // International license field
    'is_international_label' => "Licenca {$internationalName}",
    'is_international_help' => "Se marcar esta opcao, esta licenca apenas ficara disponivel para instrutores/lideres e entidades {$internationalName}.",

    // International licenses page
    'international_licenses' => 'Licenças Internacionais',
    'cmas_international_licenses' => 'Licenças Internacionais',
    'international_licenses_description' => 'As suas licenças internacionais reconhecidas em todo o mundo',
    'view_national_licenses' => 'Ver Licenças Nacionais',
    'purchase_international_license' => 'Adquirir Licença Internacional',
    'license' => 'Licença',
    'federation' => 'Federação',
    'sport_category' => 'Modalidade/Categoria',
    'validity' => 'Validade',
    'international_code' => 'Codigo Internacional',
    'active' => 'Ativa',
    'pending' => 'Pendente',
    'cancelled' => 'Cancelada',
    'unknown' => 'Desconhecido',
    'view' => 'Ver',
    'documents' => 'Documentos',
    'no_international_licenses' => 'Sem licenças internacionais',
    'no_international_licenses_message' => 'Ainda não adquiriu nenhuma licença internacional.',

    // License purchase success page
    'License Purchase Initiated!' => 'Compra de Licença Iniciada!',
    'Your license purchase is being processed. You will receive a confirmation once payment is complete.' => 'A sua compra de licença esta a ser processada. Recebera uma confirmação assim que o pagamento for concluido.',
    'You can view and manage your license in the My Licenses section' => 'Pode ver e gerir a sua licença na seccao As Minhas Licenças',
    'Payment Required' => 'Pagamento Necessario',
    'Your license is pending payment to be activated' => 'A sua licença esta pendente de pagamento para ser ativada',
    'Please complete the payment to activate your license and download the certificate' => 'Por favor complete o pagamento para ativar a sua licença e descarregar o certificado',
    'An invoice has been generated and is available for download' => 'Foi gerada uma fatura e esta disponível para download',
    'Pending Payment' => 'Pagamento Pendente',
    'Complete Payment' => 'Completar Pagamento',
    'Payment integration coming soon' => 'Integração de pagamento brevemente disponível',

    // DIVINGSERVICES certification requirement
    'active_diving_certification_required' => 'Certificação de Mergulho Ativa Necessaria',
    'active_diving_certification_required_description' => 'Deve ter uma certificação de profissional de mergulho ativa para solicitar uma licença de profissional de mergulho.',

    // License detail page actions
    'pending_payment_message' => 'Licença pendente de confirmação de pagamento. Será ativada automaticamente após o processamento do pagamento.',
    'waiting_approval_message' => 'Licença aguarda aprovação.',
    'provisional_message' => 'Licença provisória que pode ser ativada.',
    'manually_activate' => 'Ativar Manualmente',
    'cancel_license' => 'Cancelar Licença',
    'suspend_license' => 'Suspender Licença',
    'reactivate_license' => 'Reativar Licença',
    'approve_license' => 'Aprovar Licença',
    'reject_license' => 'Rejeitar Licença',
    'activate_provisional' => 'Ativar Licença Provisória',
    'confirm_manual_activate' => 'Tem a certeza de que pretende ativar manualmente esta licença?',
    'confirm_cancel' => 'Tem a certeza de que pretende cancelar esta licença?',
    'confirm_suspend' => 'Tem a certeza de que pretende suspender esta licença?',
    'confirm_reactivate' => 'Tem a certeza de que pretende reativar esta licença?',
    'confirm_approve' => 'Tem a certeza de que pretende aprovar esta licença?',
    'confirm_reject' => 'Tem a certeza de que pretende rejeitar esta licença?',
    'confirm_activate_provisional' => 'Tem a certeza de que pretende ativar esta licença provisória?',
    'confirm_validate_approve' => 'Tem a certeza de que pretende validar e aprovar esta licença?',
    'confirm_reject_validation' => 'Tem a certeza de que pretende rejeitar a validação desta licença?',
];
