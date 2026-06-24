<?php

return [
    // Create individual form
    'create_individual' => 'Criar Conta de Membro Individual',
    'full_name' => 'Nome Completo',
    'sex' => 'Sexo',
    'male' => 'Masculino',
    'female' => 'Feminino',
    'vat_number' => 'Número de Identificação Fiscal (NIF)',
    'phone' => 'Telefone',

    // User login information section
    'user_login_information' => 'Informações de login do utilizador',
    'user_login_description' => 'Escolha o email para a autenticação deste utilizador. Será enviado um email para a pessoa registar as suas próprias credenciais.',
    'login_email' => 'Email de login',
    'email_credential_help' => 'Credencial de email para o Individual fazer login',

    // Form sections
    'personal_information' => 'Informação Pessoal',
    'social_media_optional' => 'Opcional - Adicione perfis de redes sociais',
    'address_placeholder' => 'Nome da rua, n.º de porta',
    'single_name_hint' => 'Colocar apenas um nome',
    'photo_max_size_hint' => 'As fotos têm de ter uma dimensão inferior a 2MB',

    // Terms and Privacy Policy acceptance (entity creating individual)
    'terms_privacy_title' => 'Aceitação de Termos de Uso e Política de Privacidade',
    'terms_privacy_text' => 'Confirmo que tenho autorização do membro individual para a criação da sua conta pessoal, ao qual dei conhecimento dos termos de uso e da política de privacidade do portal.',
    'terms_privacy_checkbox' => 'Confirmo que li e aceito as condições acima descritas.',
    'terms_privacy_required' => 'Deve confirmar que tem autorização do membro individual para criar a sua conta.',

    // Public registration form
    'registration_title' => 'Registo de Conta Individual',
    'individual_registration' => 'Registo Individual',
    'photo' => 'Fotografia',
    'first_name' => 'Nome próprio',
    'address' => 'Morada',
    'district' => 'Distrito',
    'location' => 'Localidade',
    'postal_code' => 'Código postal',
    'identification_document' => 'Documento de identificação',
    'document_type' => 'Tipo de documento',
    'document_number' => 'N.º do documento',
    'expiry_date' => 'Data de validade',
    'login_credentials' => 'Utilizador e dados de acesso',
    'login_credentials_description' => 'Precisa de criar uma conta para iniciar sessão na plataforma.',
    'password' => 'Palavra-passe',
    'confirm_password' => 'Confirmar palavra-passe',
    'terms_and_conditions' => 'Termos e condições',
    'terms_declaration_prefix' => 'Declaro que li e concordo com os',
    'terms_of_service' => 'Termos de Serviço',
    'terms_declaration_middle' => 'e com a',
    'privacy_policy' => 'Política de Privacidade',
    'data_sharing_declaration_prefix' => 'Autorizo a partilha dos meus dados com terceiros autorizados para os fins descritos na',
    'data_sharing_policy' => 'Política de Partilha de Dados',
    'submit_registration' => 'Submeter registo',

    // Document type options
    'doc_types' => [
        'identity_card' => 'Bilhete de Identidade',
        'citizen_card' => 'Cartão do Cidadão',
        'foreign_identity_card' => 'Bilhete de Identidade Estrangeiro',
        'permanent_residence_card' => 'Cartão de Residência Permanente',
        'passport' => 'Passaporte',
    ],

    // Profile controller messages
    'error_saving_data' => 'Erro ao guardar os dados, por favor contacte a administração.',
    'profile_updated_successfully' => 'Perfil atualizado com sucesso.',
    'invalid_file_upload' => 'Ficheiro de upload inválido.',
    'image_upload_failed' => 'Falha no upload da imagem - por favor tente uma imagem diferente ou comprima a atual.',

    // Validation messages
    'duplicate_individual_exists' => 'Já existe um indivíduo com o mesmo nome, apelido, data de nascimento e país.',
    'invalid_district' => 'O distrito selecionado é inválido.',
    'validation' => [
        'photo_required' => 'A fotografia é obrigatória.',
        'file_must_be_image' => 'O ficheiro deve ser uma imagem.',
        'photo_mimes' => 'A fotografia deve ser um ficheiro JPEG ou PNG.',
        'photo_max_size' => 'A fotografia não pode ter mais de 2MB.',
        'name_required' => 'O campo nome é obrigatório.',
        'surname_required' => 'O campo apelido é obrigatório.',
        'full_name_required' => 'O campo nome completo é obrigatório.',
        'birthdate_required' => 'O campo data de nascimento é obrigatório.',
        'country_required' => 'O campo país é obrigatório.',
        'district_required' => 'O campo distrito é obrigatório.',
        'district_invalid' => 'O distrito selecionado é inválido.',
        'gender_required' => 'O campo género é obrigatório.',
        'vat_number_required' => 'O campo NIF é obrigatório.',
        'doc_type_required' => 'O campo tipo de documento é obrigatório.',
        'doc_number_required' => 'O campo número do documento é obrigatório.',
        'doc_validity_required' => 'O campo data de validade do documento é obrigatório.',
        'email_already_registered' => 'Este endereço de email já está registado.',
        'terms_accepted' => 'Deve aceitar os termos de serviço.',
        'data_sharing_accepted' => 'Deve aceitar a política de partilha de dados.',
        'entity_invalid' => 'A entidade selecionada é inválida.',
    ],
];
