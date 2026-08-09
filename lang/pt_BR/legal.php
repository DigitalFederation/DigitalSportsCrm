<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$federationName = $brand['name'];
$federationShortName = $brand['short_name'];
$portalName = $brand['portal_name'];
$internationalName = $internationalBrand['name'];

return [
    // Common
    'privacy_policy' => 'Politica de Privacidade',
    'privacy_policy_title' => 'POLITICA DE PRIVACIDADE',
    'terms_of_use' => 'Termos de Uso',
    'terms_of_use_title' => 'TERMOS DE USO',
    'last_update' => 'Ultima atualizacao',
    'entity' => 'Entidade',
    'address' => 'Endereço',
    'email' => 'Email',
    'contacts' => 'Contatos',
    'federation_full_name' => "{$federationName} ({$federationShortName})",

    // Privacy Policy
    'privacy' => [
        'responsible_entity' => 'Entidade Responsavel',
        'responsible_entity_text' => "{$federationName} ({$federationShortName}) e a entidade responsavel pelo tratamento dos dados pessoais recolhidos atraves deste Portal. As instalacoes publicas devem adaptar este texto a lei de protecao de dados e jurisdicao aplicaveis.",
        'dpo' => 'Encarregado de Protecao de Dados',
        'dpo_department' => 'Departamento Administrativo e Financeiro',

        'legal_framework' => 'Enquadramento Legal',
        'legal_framework_intro' => "O tratamento de dados pessoais pela {$federationShortName} rege-se pela seguinte legislacao:",
        'gdpr_reference' => 'Regulamento (UE) 2016/679 do Parlamento Europeu e do Conselho (Regulamento Geral sobre a Protecao de Dados - RGPD)',
        'law_58_2019' => 'Lei nacional aplicavel de execucao da protecao de dados, quando relevante',
        'law_41_2004' => 'Lei aplicavel sobre comunicacoes eletronicas e privacidade, quando relevante',

        'collected_data' => 'Dados Pessoais Recolhidos',
        'collected_data_intro' => "No ambito das suas atividades, a {$federationShortName} recolhe e trata as seguintes categorias de dados pessoais:",

        'identification_data' => 'Dados de Identificacao',
        'full_name' => 'Nome completo',
        'birth_date' => 'Data de nascimento',
        'gender' => 'Genero',
        'nationality' => 'Nacionalidade',
        'tax_number' => 'Numero de Identificacao Fiscal (NIF)',
        'id_document' => 'Numero e tipo de documento de identificacao',
        'photo' => 'Fotografia',

        'contact_data' => 'Dados de Contato',
        'full_address' => 'Endereço completa',
        'email_address' => 'Endereco de correio eletronico',
        'phone_number' => 'Numero de telefone/telemovel',

        'sports_data' => 'Dados Desportivos',
        'certifications_brevets' => 'Certificacoes e brevets obtidos',
        'federative_licenses' => 'Licencas federativas',
        'entity_affiliations' => 'Filiacoes a entidades (clubes, escolas, centros de mergulho)',
        'event_participation' => 'Participacao em eventos e competicoes',
        'sports_results' => 'Resultados desportivos',

        'health_data' => 'Dados de Saude (Categoria Especial)',
        'health_data_text' => 'Para efeitos de emissao de licencas e seguros desportivos, podera ser necessario o tratamento de dados relativos a aptidao medica para a pratica desportiva. Estes dados sao tratados com medidas de seguranca reforcadas e apenas com o consentimento explicito do titular.',

        'processing_purposes' => 'Finalidades do Tratamento',
        'processing_purposes_intro' => 'Os dados pessoais sao tratados para as seguintes finalidades:',
        'purpose_member_management' => 'Registro e gestao de membros individuais e entidades filiadas',
        'purpose_license_management' => 'Emissao, renovacao e gestao de licencas federativas',
        'purpose_certification_management' => 'Emissao e gestao de certificacoes e brevets de mergulho',
        'purpose_event_management' => 'Organizacao e gestao de eventos, competicoes e formacoes',
        'purpose_insurance_management' => 'Contratacao e gestao de seguros desportivos',
        'purpose_institutional_communication' => 'Comunicacao institucional e divulgacao de atividades',
        'purpose_legal_obligations' => 'Cumprimento de obrigacoes legais e regulamentares',
        'purpose_statistics' => 'Elaboracao de estatisticas anonimizadas',

        'legal_basis' => 'Fundamento Juridico',
        'legal_basis_intro' => "O tratamento de dados pessoais pela {$federationShortName} tem os seguintes fundamentos juridicos:",
        'consent' => 'Consentimento',
        'consent_text' => 'Quando o titular dos dados da o seu consentimento para o tratamento para uma ou mais finalidades especificas (Art. 6., n. 1, alinea a) do RGPD)',
        'contract_execution' => 'Execucao de Contrato',
        'contract_execution_text' => 'Quando o tratamento e necessario para a execucao de um contrato no qual o titular dos dados e parte, como a filiacao na federacao (Art. 6., n. 1, alinea b) do RGPD)',
        'legal_obligation' => 'Obrigacao Legal',
        'legal_obligation_text' => "Quando o tratamento e necessario para o cumprimento de uma obrigacao juridica a que a {$federationShortName} esta sujeita (Art. 6., n. 1, alinea c) do RGPD)",
        'legitimate_interest' => 'Interesse Legitimo',
        'legitimate_interest_text' => "Quando o tratamento e necessario para efeito dos interesses legitimos prosseguidos pela {$federationShortName}, desde que nao prevaleam os interesses ou direitos e liberdades fundamentais do titular (Art. 6., n. 1, alinea f) do RGPD)",

        'data_sharing' => 'Partilha de Dados',
        'data_sharing_intro' => 'Os dados pessoais poderao ser partilhados com as seguintes entidades, quando necessario para as finalidades indicadas:',
        'cmas' => $internationalName,
        'cmas_reason' => 'para emissao de certificacoes internacionais',
        'public_sports_authority' => 'Autoridade publica desportiva competente',
        'public_sports_authority_reason' => 'para cumprimento de obrigacoes legais',
        'cop' => 'Comite olimpico ou desportivo nacional, quando aplicavel',
        'cop_reason' => 'no ambito da atividade federativa',
        'insurers' => 'Seguradoras',
        'insurers_reason' => 'para contratacao de seguros desportivos',
        'affiliated_entities' => 'Entidades filiadas (clubes, escolas, centros de mergulho)',
        'affiliated_entities_reason' => 'para gestao de membros',
        'public_authorities' => 'Autoridades publicas',
        'public_authorities_reason' => 'quando legalmente exigido',
        'data_sharing_compliance' => "A {$federationShortName} exige que todas as entidades com quem partilha dados cumpram as obrigacoes de protecao de dados aplicaveis.",

        // Public disclosure of professional members
        'public_disclosure' => 'Publicacao de Dados de Membros Profissionais',
        'public_disclosure_intro' => "No ambito das suas atribuicoes como federacao desportiva e para fins de transparencia e verificacao publica de qualificacoes profissionais, a {$federationShortName} pode publicar em paginas publicas do Portal dados selecionados de membros individuais titulares de licencas ou certificacoes profissionais:",
        'public_disclosure_photo' => 'Fotografia',
        'public_disclosure_name' => 'Nome completo',
        'public_disclosure_birth_date' => 'Data de nascimento',
        'public_disclosure_entity' => 'Entidade de filiacao (clube/escola/centro de mergulho)',
        'public_disclosure_license_status' => 'Estado da licenca profissional',
        'public_disclosure_mandatory' => 'Esta publicacao constitui condicao necessaria para a emissao e manutencao de licencas profissionais, tendo como fundamento juridico:',
        'public_disclosure_contract' => 'A execucao do contrato de filiacao e licenciamento profissional (Art. 6., n. 1, alinea b) do RGPD)',
        'public_disclosure_legal_obligation' => 'O cumprimento de obrigacoes legais aplicaveis, quando relevante (Art. 6., n. 1, alinea c) do RGPD)',
        'public_disclosure_legitimate_interest' => "O interesse legitimo da {$federationShortName} em promover a transparencia e permitir a verificacao publica das qualificacoes dos profissionais (Art. 6., n. 1, alinea f) do RGPD)",
        'public_disclosure_no_removal' => 'A publicacao destes dados e obrigatoria para todos os detentores de licencas profissionais, nao sendo possivel solicitar a sua remocao enquanto a licenca estiver ativa.',

        'international_transfers' => 'Transferencias Internacionais',
        'international_transfers_text' => "Alguns dados poderao ser transferidos para fora do Espaco Economico Europeu, incluindo para {$internationalName} para emissao de certificacoes internacionais. As instalacoes publicas devem configurar garantias adequadas para a sua jurisdicao.",

        'retention_period' => 'Prazo de Conservacao',
        'retention_period_intro' => 'Os dados pessoais sao conservados durante o periodo necessario para as finalidades para que foram recolhidos:',
        'active_member_data' => 'Dados de membros ativos',
        'active_member_data_text' => 'durante a vigencia da filiacao e pelo periodo legalmente exigido apos a cessacao',
        'legal_obligation_data' => 'Dados necessarios para cumprimento de obrigacoes legais',
        'legal_obligation_data_text' => 'pelo prazo legalmente estabelecido',
        'financial_data' => 'Dados financeiros e fiscais',
        'financial_data_text' => 'pelo prazo exigido pela lei fiscal e contabilistica aplicavel',

        'data_subject_rights' => 'Direitos dos Titulares',
        'data_subject_rights_intro' => 'Nos termos do RGPD, os titulares dos dados tem os seguintes direitos:',
        'right_access' => 'Direito de Acesso',
        'right_access_text' => 'Direito de obter confirmacao sobre se os seus dados estao a ser tratados e, em caso afirmativo, aceder aos mesmos',
        'right_rectification' => 'Direito de Retificacao',
        'right_rectification_text' => 'Direito de exigir a retificacao de dados inexatos ou incompletos',
        'right_erasure' => 'Direito ao Apagamento',
        'right_erasure_text' => 'Direito de solicitar o apagamento dos dados, quando aplicavel',
        'right_portability' => 'Direito a Portabilidade',
        'right_portability_text' => 'Direito de receber os dados num formato estruturado e de uso corrente',
        'right_objection' => 'Direito de Oposicao',
        'right_objection_text' => 'Direito de se opor ao tratamento dos dados em determinadas circunstancias',
        'right_restriction' => 'Direito a Limitacao',
        'right_restriction_text' => 'Direito de solicitar a limitacao do tratamento em determinadas circunstancias',
        'right_withdraw_consent' => 'Direito de Retirar o Consentimento',
        'right_withdraw_consent_text' => 'Quando o tratamento se baseia no consentimento, o titular pode retira-lo a qualquer momento',
        'exercise_rights_text' => 'Para exercer qualquer destes direitos, contacte-nos atraves do email configurado ou por correio para a endereço indicada.',

        'data_security' => 'Seguranca dos Dados',
        'data_security_text' => "A {$federationShortName} implementa medidas tecnicas e organizativas apropriadas para proteger os dados pessoais contra destruicao acidental ou ilicita, perda, alteracao, divulgacao ou acesso nao autorizados. Estas medidas podem incluir encriptacao de dados, controlos de acesso, backups regulares e formacao dos colaboradores.",

        'cookies' => 'Cookies',
        'cookies_text' => 'Este Portal utiliza cookies para melhorar a experiencia do usuário e garantir o funcionamento adequado dos servicos. Para mais informacoes sobre os cookies utilizados, consulte a nossa Politica de Cookies.',

        'complaints' => 'Reclamacoes',
        'complaints_intro' => 'Sem prejuizo de qualquer outra via de recurso administrativo ou judicial, o titular dos dados tem o direito de apresentar uma reclamacao junto da autoridade de controlo competente:',
        'cnpd' => 'Comissao Nacional de Protecao de Dados (CNPD)',

        'policy_changes' => 'Alteracoes a Politica',
        'policy_changes_text' => "A {$federationShortName} pode alterar esta Politica de Privacidade. As alteracoes serao publicadas neste Portal e, quando significativas, comunicadas aos titulares dos dados por email quando exigido.",

        'contacts_intro' => 'Para qualquer questao relacionada com a protecao de dados pessoais, contacte-nos:',
    ],

    // Terms of Use
    'terms' => [
        'general_provisions' => 'Disposicoes Gerais',
        'general_provisions_text' => "Os presentes Termos de Uso regulam o acesso e utilizacao do {$portalName}, operado por {$federationName} ({$federationShortName}). Ao aceder e utilizar este Portal, o usuário aceita estes Termos de Uso.",

        'definitions' => 'Definicoes',
        'portal' => 'Portal',
        'portal_definition' => "Plataforma digital da {$federationShortName} acessivel atraves da internet",
        'user' => 'Usuário',
        'user_definition' => 'Qualquer pessoa que aceda ao Portal',
        'member' => 'Membro',
        'member_definition' => "Pessoa singular registada na {$federationShortName}",
        'entity_definition' => "Organizacao filiada na {$federationShortName}",
        'services' => 'Servicos',
        'services_definition' => 'Conjunto de funcionalidades disponibilizadas atraves do Portal',

        'acceptance' => 'Aceitacao dos Termos',
        'acceptance_text' => "A utilizacao deste Portal implica a aceitacao dos presentes Termos de Uso. Se nao concordar com estes termos, devera abster-se de utilizar o Portal. A {$federationShortName} pode modificar estes Termos, sendo as alteracoes eficazes apos a sua publicacao no Portal.",

        'services_description' => 'Descricao dos Servicos',
        'services_description_intro' => "O Portal {$portalName} disponibiliza os seguintes servicos:",
        'service_profile_management' => 'Registro e gestao de perfil de membros e entidades',
        'service_license_acquisition' => 'Aquisicao e renovacao de licencas federativas',
        'service_certification_management' => 'Gestao de certificacoes e brevets de mergulho',
        'service_event_registration' => 'Inscricao em eventos, competicoes e formacoes',
        'service_document_access' => 'Acesso e download de documentos oficiais',
        'service_payment_processing' => 'Processamento de pagamentos',
        'service_insurance_management' => 'Gestao de seguros desportivos',
        'service_institutional_info' => 'Consulta de informacoes institucionais',

        'user_registration' => 'Registro de Usuários',
        'user_registration_intro' => 'Para aceder a determinadas funcionalidades do Portal, e necessario efetuar registro. Ao registar-se, o usuário compromete-se a:',
        'registration_true_info' => 'Fornecer informacoes verdadeiras, precisas, atuais e completas',
        'registration_keep_updated' => 'Manter os seus dados atualizados',
        'registration_credentials' => 'Manter a confidencialidade das suas credenciais de acesso',
        'registration_notify' => "Notificar imediatamente a {$federationShortName} em caso de uso nao autorizado da sua conta",

        // Public disclosure of professional members
        'public_disclosure' => 'Publicacao de Dados de Membros Profissionais',
        'public_disclosure_intro' => "Ao adquirir uma licenca ou certificacao profissional, o usuário reconhece que a {$federationShortName} pode publicar em paginas publicas do Portal dados selecionados necessarios para verificacao publica:",
        'public_disclosure_photo' => 'Fotografia',
        'public_disclosure_name' => 'Nome completo',
        'public_disclosure_birth_date' => 'Data de nascimento',
        'public_disclosure_entity' => 'Entidade de filiacao',
        'public_disclosure_license_status' => 'Estado da licenca profissional',
        'public_disclosure_mandatory' => 'Esta publicacao constitui condicao obrigatoria para a emissao e manutencao de licencas profissionais, nao sendo possivel solicitar a sua remocao enquanto a licenca estiver ativa.',
        'public_disclosure_purpose' => 'A publicacao destina-se a permitir a verificacao publica das qualificacoes profissionais dos membros, contribuindo para a seguranca e transparencia no sector das actividades subaquaticas e desporto federado.',

        'user_obligations' => 'Obrigacoes do Usuário',
        'user_obligations_intro' => 'O usuário compromete-se a:',
        'obligation_lawful_use' => 'Utilizar o Portal em conformidade com a lei e os presentes Termos',
        'obligation_true_info' => 'Fornecer informacoes verdadeiras e atualizadas',
        'obligation_respect_ip' => 'Respeitar os direitos de propriedade intelectual',
        'obligation_security' => 'Nao comprometer a seguranca do Portal',
        'obligation_no_illegal' => 'Nao utilizar o Portal para fins ilicitos ou prejudiciais',
        'obligation_no_harmful' => 'Nao transmitir conteudos ilegais, difamatorios ou ofensivos',

        'prohibited_conduct' => 'Condutas Proibidas',
        'prohibited_conduct_intro' => 'E expressamente proibido:',
        'prohibited_unauthorized_access' => 'Aceder a areas restritas sem autorizacao',
        'prohibited_malware' => 'Introduzir virus, malware ou qualquer codigo malicioso',
        'prohibited_interference' => 'Interferir com o funcionamento normal do Portal',
        'prohibited_bots' => 'Utilizar robots, crawlers ou ferramentas automatizadas para extrair dados',
        'prohibited_impersonation' => 'Fazer-se passar por outra pessoa ou entidade',
        'prohibited_illegal_activities' => 'Utilizar o Portal para atividades ilegais',

        'intellectual_property' => 'Propriedade Intelectual',
        'intellectual_property_text' => "Todo o conteudo especifico da instalacao do Portal, incluindo textos, graficos, logotipos, icones, imagens, clips de audio e video e compilacoes de dados, e propriedade da {$federationShortName} ou dos seus licenciadores. O projeto de software e licenciado de acordo com a licenca do repositorio.",
        'intellectual_property_license' => 'E concedida ao usuário uma licenca limitada, nao exclusiva e nao transferivel para aceder e utilizar o Portal para fins pessoais e nao comerciais, desde que respeite estes Termos de Uso.',

        'payments' => 'Pagamentos',
        'payments_intro' => 'Alguns servicos disponibilizados atraves do Portal estao sujeitos a pagamento:',
        'payments_prices' => 'Os precos sao os indicados no Portal no momento da transacao, incluindo impostos aplicaveis quando configurados',
        'payments_methods' => 'Os metodos de pagamento aceites sao os indicados no Portal',
        'payments_confirmation' => 'Apos confirmacao do pagamento, sera emitido comprovativo por email',
        'payments_refunds' => 'A politica de reembolsos aplicavel e a indicada para cada tipo de servico',

        'liability_limitation' => 'Limitacao de Responsabilidade',
        'liability_limitation_intro' => "A {$federationShortName} nao sera responsavel por:",
        'liability_interruptions' => 'Interrupcoes ou falhas no funcionamento do Portal',
        'liability_errors' => 'Erros ou omissoes no conteudo do Portal',
        'liability_third_party' => 'Danos causados por terceiros ou por utilizacao indevida',
        'liability_force_majeure' => 'Eventos de forca maior ou caso fortuito',

        'warranty_exclusion' => 'Exclusao de Garantias',
        'warranty_exclusion_text' => "O Portal e disponibilizado \"tal como esta\" e \"conforme disponivel\". A {$federationShortName} nao garante que o Portal esteja livre de erros, virus ou outros componentes nocivos, nem que funcione de forma ininterrupta. Na medida maxima permitida pela lei, a {$federationShortName} exclui todas as garantias, expressas ou implicitas.",

        'indemnification' => 'Indemnizacao',
        'indemnification_text' => "O usuário compromete-se a indemnizar e isentar a {$federationShortName}, os seus dirigentes, colaboradores e representantes de quaisquer reclamacoes, danos, perdas ou despesas resultantes da violacao destes Termos ou da utilizacao indevida do Portal.",

        'third_party_links' => 'Ligacoes a Terceiros',
        'third_party_links_text' => "O Portal pode conter ligacoes para websites de terceiros. A {$federationShortName} nao controla esses websites e nao e responsavel pelo seu conteudo ou praticas de privacidade. A inclusao de ligacoes nao implica qualquer associacao, patrocinio ou endosso.",

        'suspension_termination' => 'Suspensao e Cessacao',
        'suspension_termination_intro' => "A {$federationShortName} pode suspender ou cessar o acesso de qualquer usuário ao Portal, sem aviso previo, nas seguintes situacoes:",
        'suspension_terms_violation' => 'Violacao destes Termos de Uso',
        'suspension_illegal_acts' => 'Pratica de atos ilicitos',
        'suspension_harmful_conduct' => "Condutas que prejudiquem a {$federationShortName} ou outros usuários",
        'suspension_user_request' => 'A pedido do proprio usuário',

        'terms_changes' => 'Alteracoes aos Termos',
        'terms_changes_text' => "A {$federationShortName} pode modificar estes Termos de Uso. As alteracoes serao publicadas no Portal e entram em vigor imediatamente apos a publicacao. A continuacao da utilizacao do Portal apos a publicacao de alteracoes constitui aceitacao das mesmas.",

        'applicable_law' => 'Lei Aplicavel',
        'applicable_law_text' => 'Estes Termos de Uso devem ser adaptados por cada instalacao a lei e aos tribunais da sua jurisdicao operacional.',

        'dispute_resolution' => 'Resolucao de Litigios',
        'dispute_resolution_text' => 'Em caso de litigio, as partes comprometem-se a procurar uma solucao amigavel antes de recorrer aos tribunais. O usuário pode recorrer aos mecanismos de resolucao alternativa de litigios disponiveis, incluindo a plataforma europeia de resolucao de litigios em linha (https://ec.europa.eu/consumers/odr).',

        'severability' => 'Divisibilidade',
        'severability_text' => 'Se alguma disposicao destes Termos for considerada invalida ou inexequivel, as restantes disposicoes manterao a sua plena validade e eficacia.',

        'contacts_intro' => 'Para questoes relacionadas com estes Termos de Uso, contacte-nos:',
        'privacy_policy_reference' => 'Para informacoes sobre o tratamento dos seus dados pessoais, consulte a nossa Politica de Privacidade.',
    ],
];
