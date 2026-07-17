<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'entity_created' => [
        'subject' => 'Bem-vindo ao :app',
        'greeting' => 'Saudações, :name!',
        'line1' => 'Foi criada uma conta para a sua entidade.',
        'line2' => 'Para gerir o perfil da sua entidade e explorar as funcionalidades da nossa plataforma, por favor defina a sua senha.',
        'action' => 'Definir Senha',
        'line3' => 'Após definir a sua senha, terá acesso completo ao seu painel de controlo.',
        'line4' => 'Aguardamos a sua participação ativa.',
        'line5' => 'Obrigado por fazer parte do :app. Se tiver alguma dúvida, não hesite em contactar-nos.',
        'salutation' => 'Saudações Subaquáticas, A Equipa :app',
    ],

    'welcome_email' => [
        'title' => 'Email de Boas-vindas',
        'user_email' => 'Email do utilizador',
        'sent_status' => 'Enviado',
        'not_sent_status' => 'Não enviado',
        'send_button' => 'Enviar Email de Boas-vindas',
        'resend_button' => 'Reenviar Email de Boas-vindas',
        'confirm_send' => 'Tem a certeza que deseja enviar o email de boas-vindas?',
        'description' => 'Este email contém um link para o utilizador definir a sua senha e ativar a sua conta.',
        'sent' => 'Email de boas-vindas enviado com sucesso.',
        'failed' => 'Falha ao enviar o email de boas-vindas.',
        'no_user' => 'Não existe conta de utilizador associada.',
    ],

    // Payment notifications
    'payment_made' => 'Foi efetuado um pagamento de :value.',

    // Event notifications
    'event_enrollment_confirmed' => 'A sua inscrição no evento foi confirmada.',
    'event_registration_confirmed' => 'O seu registo no evento foi confirmado.',

    // Request notifications
    'request_approved' => 'O seu pedido para aderir a :federation foi aprovado.',
    'federation_request_approved' => "O seu pedido para aderir ao {$portalName} foi aprovado.",
    'association_request_accepted' => 'Pedido de associação aceite com sucesso.',
    'error_accepting_request' => 'Erro ao aceitar o pedido.',
    'request_join_accepted' => 'O pedido de adesão de :name foi aceite.',
    'request_rejected' => 'Pedido de adesão rejeitado com sucesso.',
    'error_rejecting_request' => 'Erro ao rejeitar o pedido.',
    'request_deleted' => 'Pedido de adesão eliminado com sucesso.',

    // Document notifications
    'document_created' => [
        'subject' => 'Notificação de Criação de Documento',
        'greeting' => 'Notificação',
        'line' => "O documento :invoice esta disponivel no {$portalName}. Clique no botao abaixo para aceder ao {$portalName}, onde podera consultar o estado do documento no menu Pagamentos.",
        'action' => 'Abrir Documento',
    ],

    'admin_license_attributed' => [
        'subject' => 'Nova licença solicitada',
        'greeting' => 'Notificação',
        'line_intro' => 'Foi solicitada uma nova licença.',
        'line_license' => '**Nome da Licença:** :name',
        'line_holder' => '**Nome do Titular:** :holder',
        'line_federation' => '**Nome da Federação:** :federation',
        'action' => 'Ver Detalhes',
    ],

    'membership_create' => [
        'intro' => 'Foi atribuída uma nova filiação. Ficará ativa após a confirmação do pagamento.',
        'action' => 'Abrir filiação',
        'outro' => 'Obrigado por utilizar a nossa aplicação!',
        'database' => 'Foi atribuída uma nova filiação. Ficará ativa após a confirmação do pagamento.',
    ],

    'entity_approval' => [
        'subject' => 'Aprovação de Entidade Necessária',
        'greeting' => 'Olá :name,',
        'line_intro' => 'Existe uma nova entidade a aguardar a sua aprovação.',
        'line_entity' => 'Nome da Entidade: :entity',
        'action' => 'Ver Entidade',
        'line_review' => 'Por favor, reveja os detalhes da entidade e prossiga com o processo de aprovação.',
        'salutation_regards' => 'Com os melhores cumprimentos,',
        'salutation_team' => 'Equipa :app',
        'database' => 'Uma nova entidade requer a sua aprovação.',
    ],

    'entity_member_accepted' => [
        'subject' => 'Novo membro aceite: :name',
        'greeting' => 'Olá!',
        'line_accepted' => ':name aceitou o convite para ser membro de :entity.',
        'line_active' => 'Este membro está agora ativo na sua entidade.',
        'action' => 'Ver Membros',
        'salutation' => 'Cumprimentos,<br>Equipa :app',
        'database' => ':name aceitou o convite para ser membro.',
    ],

    'entity_member_invitation' => [
        'subject' => 'Convite para ser membro de :entity',
        'greeting' => 'Olá!',
        'line_invited' => ':inviter convidou-o para ser membro da sua entidade.',
        'line_instructions' => 'Para aceitar este convite, aceda à plataforma e navegue para \'Entidades\' no menu lateral.',
        'action' => 'Ver Convite',
        'line_ignore' => 'Se não esperava este convite, pode ignorar este email.',
        'salutation' => 'Cumprimentos,<br>Equipa :app',
        'database' => 'A entidade :entity convidou-o para ser membro.',
    ],

    'entity_request' => [
        'database_title' => 'Novo Pedido de Entidade',
        'database_message' => 'Tem um novo pedido de :name para aderir.',
    ],

    'export_ready' => [
        'line_intro' => 'A sua exportação está pronta para descarregar. Verifique o email para obter o link.',
        'action' => 'Descarregar Exportação',
        'database' => 'A sua exportação está pronta para descarregar.',
    ],

    'federation_join_request' => [
        'database' => ':name pediu para aderir à Federação.',
    ],

    'individual_request_license' => [
        'line' => 'Existe uma nova licença de :type para aprovar.',
        'database' => 'Existe uma nova licença de :type para aprovar.',
    ],

    'instructor_new_certification' => [
        'line' => 'Existe uma nova certificação para aprovar.',
        'action' => 'Abrir',
        'database' => 'Existe uma nova certificação para aprovar.',
    ],

    'invite_individual_professional' => [
        'subject' => 'Convite para ser :role',
        'greeting' => 'Olá :name!',
        'line_invited' => 'Foi convidado(a) para ser :role de :entity.',
        'action' => 'Ver o convite',
        'line_thanks' => 'Obrigado por considerar o nosso convite!',
        'salutation' => 'Cumprimentos, :app',
        'database' => 'Foi convidado(a) para ser :role de :entity.',
    ],

    'membership_activation' => [
        'line_activated' => 'A filiação :name foi ativada com sucesso.',
        'action' => 'Abrir filiação',
        'salutation' => $primaryShortName,
        'database' => 'A filiação :name foi ativada com sucesso.',
    ],

    'membership_expiration' => [
        'line_expires' => 'A sua filiação :name irá expirar a :date.',
        'action' => 'Abrir filiação',
        'outro' => 'Obrigado por utilizar a nossa aplicação!',
    ],

    'official_document_activated' => [
        'database' => 'O documento :name foi aprovado.',
    ],

    'official_document_created' => [
        'database' => 'O Documento Oficial :name foi enviado.',
    ],

    'official_document_deleted' => [
        'database' => 'O documento :name foi eliminado.',
    ],

    'report_generated' => [
        'line_ready' => 'O seu relatório está pronto.',
        'action' => 'Descarregar relatório',
        'line_auth' => 'Tem de estar autenticado para descarregar o relatório.',
        'database' => 'O seu relatório está pronto para descarregar. Clique aqui para descarregar.',
    ],
];
