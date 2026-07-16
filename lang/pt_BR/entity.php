<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Títulos de página
    'members_list' => 'Lista de Membros',
    'member_list' => 'Lista de Membros',
    'entities' => 'Entidades Coletivas',
    'entity_detail' => 'Detalhe da Entidade',
    'entities_to_approve' => 'Entidades para Aprovar',
    'create_entity' => 'Criar Entidade',
    'create_entity_account' => 'Criar conta de Entidade',
    'edit_entity_record' => 'Editar registro da Entidade',

    // Ações
    'create_individual' => 'Criar Individual',
    'individuals_to_approve' => 'Individuais para Aprovar',
    'invite_member' => 'Convidar Membro',
    'submit_request' => 'Enviar Pedido',
    'approve_entity' => 'Aprovar entidade',
    'accept_request' => 'Aceitar este pedido?',
    'view_all' => 'Ver Todos',
    'see_all_instructors' => 'Ver todos os instrutores',
    'open_url' => 'Abrir url',

    // Cabeçalhos de tabela
    'gender' => 'Sexo',
    'id_number' => 'Nº ID',
    'national_affiliation' => "Filiacao {$primaryShortName}",
    'table_name' => 'Nome',
    'table_country' => 'País',
    'table_national_fed_nr' => 'Nr. Fed. Nacional',
    'table_cmas_zone' => 'Zona Internacional',
    'table_sub_region' => 'Sub-região',
    'table_actions' => 'Ações',
    'table_nationality' => 'Nacionalidade',
    'table_email' => 'Email',
    'table_requested' => 'Solicitado',
    'table_federation' => 'Organização',
    'table_type' => 'Tipo',
    'table_status' => 'Estado de Membro',
    'table_national_number' => 'Número Nacional',
    'table_number' => 'Número',
    'table_date' => 'Data',
    'table_total' => 'Total',
    'table_zone_or_association' => 'Zona ou Associação Territorial',

    // Rótulos de formulário
    'name' => 'Nome da Entidade',
    'given_name' => 'Nome Próprio',
    'family_name' => 'Apelido',
    'nationality' => 'Nacionalidade',
    'federation' => 'Federação',
    'birthdate' => 'Data de Nascimento',
    'member_number' => 'Nº Filiado',
    'affiliation_status' => 'Estado Filiação',
    'affiliation_active' => 'Ativo',
    'affiliation_inactive' => 'Inativo',
    'valid_member_code' => 'Código de membro válido',

    // Secções do formulário
    'information' => 'Informação',
    'entity_logo' => 'Logótipo da Entidade',
    'club_school_center_name' => 'Nome do Clube/Escola/Centro',
    'legal_name' => 'Nome de Registro Fiscal',
    'responsible_person_name' => 'Nome da pessoa responsável',
    'nif' => 'NIF',
    'national_fed_nr' => 'Nr. Fed. Nacional',
    'affiliate_nr' => 'N.º de Filiado',
    'hq_location' => 'Localização da Sede',
    'district' => 'Distrito',
    'zones' => 'Zonas',
    'no_zones_assigned' => 'Sem zonas atribuídas',
    'address' => 'Endereço',
    'location' => 'Localidade',
    'zip_code' => 'Código Postal',
    'country' => 'País',
    'select_option' => '-- Selecione uma opção --',
    'public_contacts' => 'Contatos Públicos',
    'contact_email' => 'Email de Contato',
    'website' => 'Website',
    'phone_number' => 'Número de Telefone',
    'social_media_links' => 'Redes Sociais',
    'facebook_url' => 'URL do Facebook',
    'x_url' => 'URL do X',
    'instagram_url' => 'URL do Instagram',
    'linkedin_url' => 'URL do LinkedIn',

    // Termos e políticas
    'terms_policies' => 'Termos e Políticas',
    'terms_confirm' => 'Confirmo que a entidade aceita os',
    'terms_of_service' => 'Termos de Serviço',
    'and' => 'e',
    'privacy_policy' => 'Política de Privacidade',
    'data_sharing_confirm' => 'Confirmo que a entidade consente a partilha de dados com terceiros autorizados conforme descrito na',
    'data_sharing_policy' => 'Política de Partilha de Dados',
    'save_record' => 'Guardar registro',

    // Secção de login do usuário
    'user_login_information' => 'Informação de login do usuário',
    'user_login_info_description' => 'Após escolher o email do usuário, será enviado um email para que a pessoa possa registar as suas credenciais.',
    'user_login_email' => 'Email de login do usuário',
    'confirm_user_login_email' => 'Confirmar email de login do usuário',
    'confirm_email_address' => 'Confirmar o endereço de email',
    'email_credential_hint' => 'Credencial de email para o usuário fazer login',
    'entity_creation_info' => 'Quando um registro de Entidade é criado, um usuário é também automaticamente associado a este registro. Será enviado um email para o endereço escolhido para que o usuário possa registar as suas credenciais. Depois disso, a pessoa pode fazer login na plataforma.',

    // Conteúdo do modal
    'member_invitation_form' => 'Formulário de Convite de Membro',
    'member_request' => 'Convite de Membro',
    'member_request_description' => 'Pode usar este formulario para convidar membros atraves do ID Pessoal ou do Numero de Filiado. Devera solicitar um destes ao membro para enviar este convite.',
    'or_separator' => 'OU',

    // Atribuição de zona
    'zone_auto_assigned' => 'A zona será automaticamente atribuída com base na sua associação.',
    'zone_will_be' => 'Zona',
    'zone_edit_restricted' => "Apenas {$primaryShortName} ou Admin podem editar este campo.",

    // Aprovação de entidade
    'approval_national_federation_message' => 'Está prestes a aprovar esta entidade. Um número de filiado será atribuído automaticamente.',
    'approval_association_message' => 'Está prestes a aprovar esta entidade para a sua associação.',
    'member_number_auto_generated' => 'O número de filiado será gerado automaticamente após a aprovação.',
    'member_number_primary_federation_only' => "Nota: Apenas {$primaryShortName} pode atribuir o numero de federacao nacional. A entidade sera aprovada para a sua associacao sem numero de filiado.",

    // Página de visualização
    'tax_identification_number' => 'Número de Identificação Fiscal',
    'hq_address_city_postal' => 'Endereço da Sede, Cidade, Código Postal',
    'individuals' => 'Individuais',
    'diving_certifications' => 'Certificações de Mergulho',
    'scientific_certifications' => 'Certificações Científicas',
    'diving_licenses' => 'Licenças do Prestador de Serviços de Mergulho',
    'scientific_licenses' => 'Licenças Científicas',
    'sport_licenses' => 'Licenças Desportivas',
    'instructors' => 'Instrutores',
    'active' => 'ativos',
    'no_instructors_yet' => 'Ainda sem instrutores',
    'federations' => 'Federação(ões)',
    'associations' => 'Associações',
    'federation_and_associations' => 'Federação & Associações',
    'no_individuals_yet' => 'Ainda sem individuais',
    'local_federation' => 'Associação',
    'main_federation' => 'Federação Principal',
    'no_federation_memberships' => 'Sem filiações em federações',
    'no_association_memberships' => 'Sem filiações em associações',
    'table_association' => 'Associação',
    'association_type_territorial' => 'Territorial',
    'association_type_nacional' => 'Nacional',
    'association_type_modalidade' => 'Modalidade',

    // Documentos
    'documents_invoices' => 'Documentos e Faturas',
    'view' => 'Ver',
    'no_documents_found' => 'Sem Documentos',
    'no_documents_description' => 'Ainda não foram gerados documentos ou faturas para esta entidade.',
    'showing_last_documents' => 'A mostrar os ultimos :count documentos',

    // Mensagens
    'invalid_cmas_code' => 'O N ID e invalido. Por favor, confirme a informacao fornecida.',
    'invalid_member_number' => 'O numero de filiado e invalido. Por favor, confirme a informacao fornecida.',
    'member_must_have_federation' => 'Este membro deve ser um filiado com estado de ativa ou pendente e nao confirmar se ja nao e membro da sua entidade ou tem um pedido pendente.',
    'invitation_sent_success' => 'O convite de membro foi enviado com sucesso. Por favor, aguarde que o membro reveja o seu pedido.',
    'error_creating_record' => 'Erro ao criar este registro: :error',

    // Abas do Perfil da Entidade
    'no_certifications_message' => 'Esta entidade ainda não tem certificações atribuídas.',
    'no_licenses_message' => 'Esta entidade ainda não tem licenças atribuídas.',

    // Filiação em federação
    'designation' => 'Designação',
    'member_approved' => 'Membro Aprovado',
    'member_pending_approval' => 'Pendente Aprovação',
    'federation_membership_info' => 'Esta tabela apresenta o seu estado de membro na Federação e Associações.',

    // Painel da Entidade
    'dashboard' => [
        'entity_profile' => 'Perfil da Entidade',
        'members_to_approve' => 'Membros por Aprovar',
        'no_pending_members' => 'Sem pedidos de membros pendentes',
        'entity_affiliations' => 'Filiações da Entidade',
        'no_affiliations' => 'Sem filiações encontradas',
        'no_sport_licenses' => 'Sem licenças de desporto',
        'no_diving_licenses' => 'Sem licenças de mergulho',
        'no_entity_found' => 'Entidade Não Encontrada',
        'no_entity_associated' => 'Nenhuma entidade está associada à sua conta.',
    ],

    // Mensagens de erro
    'committee_not_found' => 'A comissão necessária para o tipo de entidade :type não está configurada. Por favor contacte o suporte.',

    // Mapa
    'get_directions' => 'Obter Direções',

    // International Portal
    'cmas_portal_access' => 'Acesso ao Portal Internacional',
    'has_cmas_portal_account' => 'Tem Conta no Portal Internacional',
    'cmas_portal_description' => 'Marque esta caixa se a entidade tem uma conta no Portal Internacional',

    // Gestao da Pagina Publica
    'public_page' => [
        'title' => 'Gestao da Pagina Publica',
        'subtitle' => 'Gerir o perfil publico e conteudo da sua organizacao',
        'view_public_page' => 'Ver Pagina Publica',
        'tabs' => [
            'general' => 'Configuracoes Gerais',
            'featured_locations' => 'Locais em Destaque',
            'courses' => 'Cursos de Mergulho',
        ],
        'background_image' => 'Imagem de Fundo do Perfil',
        'current_background' => 'Fundo Atual',
        'current_image' => 'Imagem atual',
        'confirm_remove_background' => 'Tem a certeza que deseja remover a imagem de fundo?',
        'background_removed' => 'Imagem de fundo removida com sucesso.',
        'upload_file' => 'Carregar arquivo',
        'or_drag_drop' => 'ou arrastar e largar',
        'image_requirements' => 'PNG, JPG, WEBP ate 2MB',
        'preview' => 'Pre-visualizacao',
        'public_description' => 'Descricao Publica',
        'description_help' => 'Esta descricao sera exibida na sua pagina de perfil publica.',
        'save_settings' => 'Guardar Configuracoes',
        'settings_saved' => 'Configuracoes guardadas com sucesso.',
        'featured_locations' => [
            'title' => 'Locais de Mergulho em Destaque',
            'description' => 'Selecione os locais de mergulho que deseja destacar no seu perfil publico.',
            'select_locations' => 'Selecionar Locais',
            'no_locations_selected' => 'Nenhum local de mergulho selecionado.',
            'selected_preview' => 'Pre-visualizacao dos Locais Selecionados',
            'save_locations' => 'Guardar Locais em Destaque',
            'locations_saved' => 'Locais em destaque atualizados com sucesso.',
            'create_new' => 'Criar Novo Local',
        ],
    ],
];
