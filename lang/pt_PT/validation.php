<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'O campo :attribute deve ser aceite.',
    'accepted_if' => 'O campo :attribute deve ser aceite quando :other é :value.',
    'active_url' => 'O campo :attribute não é um URL válido.',
    'after' => 'O campo :attribute deve ser uma data posterior a :date.',
    'after_or_equal' => 'O campo :attribute deve ser uma data posterior ou igual a :date.',
    'alpha' => 'O campo :attribute deve conter apenas letras.',
    'alpha_dash' => 'O campo :attribute deve conter apenas letras, números, hífens e underscores.',
    'alpha_num' => 'O campo :attribute deve conter apenas letras e números.',
    'array' => 'O campo :attribute deve ser um array.',
    'before' => 'O campo :attribute deve ser uma data anterior a :date.',
    'before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a :date.',
    'between' => [
        'array' => 'O campo :attribute deve ter entre :min e :max itens.',
        'file' => 'O campo :attribute deve ter entre :min e :max kilobytes.',
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
        'string' => 'O campo :attribute deve ter entre :min e :max caracteres.',
    ],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação do campo :attribute não corresponde.',
    'current_password' => 'A palavra-passe está incorreta.',
    'date' => 'O campo :attribute não é uma data válida.',
    'date_equals' => 'O campo :attribute deve ser uma data igual a :date.',
    'date_format' => 'O campo :attribute não corresponde ao formato :format.',
    'declined' => 'O campo :attribute deve ser recusado.',
    'declined_if' => 'O campo :attribute deve ser recusado quando :other é :value.',
    'different' => 'Os campos :attribute e :other devem ser diferentes.',
    'digits' => 'O campo :attribute deve ter :digits dígitos.',
    'digits_between' => 'O campo :attribute deve ter entre :min e :max dígitos.',
    'dimensions' => 'O campo :attribute tem dimensões de imagem inválidas.',
    'distinct' => 'O campo :attribute tem um valor duplicado.',
    'doesnt_end_with' => 'O campo :attribute não pode terminar com: :values.',
    'doesnt_start_with' => 'O campo :attribute não pode começar com: :values.',
    'email' => 'O campo :attribute deve ser um endereço de email válido.',
    'ends_with' => 'O campo :attribute deve terminar com: :values.',
    'enum' => 'O valor selecionado para :attribute é inválido.',
    'exists' => 'O valor selecionado para :attribute é inválido.',
    'file' => 'O campo :attribute deve ser um ficheiro.',
    'filled' => 'O campo :attribute deve ter um valor.',
    'gt' => [
        'array' => 'O campo :attribute deve ter mais de :value itens.',
        'file' => 'O campo :attribute deve ser maior que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior que :value.',
        'string' => 'O campo :attribute deve ter mais de :value caracteres.',
    ],
    'gte' => [
        'array' => 'O campo :attribute deve ter :value ou mais itens.',
        'file' => 'O campo :attribute deve ser maior ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior ou igual a :value.',
        'string' => 'O campo :attribute deve ter :value ou mais caracteres.',
    ],
    'image' => 'O campo :attribute deve ser uma imagem.',
    'in' => 'O valor selecionado para :attribute é inválido.',
    'in_array' => 'O campo :attribute não existe em :other.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'ip' => 'O campo :attribute deve ser um endereço IP válido.',
    'ipv4' => 'O campo :attribute deve ser um endereço IPv4 válido.',
    'ipv6' => 'O campo :attribute deve ser um endereço IPv6 válido.',
    'json' => 'O campo :attribute deve ser uma string JSON válida.',
    'lt' => [
        'array' => 'O campo :attribute deve ter menos de :value itens.',
        'file' => 'O campo :attribute deve ser menor que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor que :value.',
        'string' => 'O campo :attribute deve ter menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'O campo :attribute não deve ter mais de :value itens.',
        'file' => 'O campo :attribute deve ser menor ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor ou igual a :value.',
        'string' => 'O campo :attribute não deve ter mais de :value caracteres.',
    ],
    'mac_address' => 'O campo :attribute deve ser um endereço MAC válido.',
    'max' => [
        'array' => 'O campo :attribute não deve ter mais de :max itens.',
        'file' => 'O campo :attribute não deve ser maior que :max kilobytes.',
        'numeric' => 'O campo :attribute não deve ser maior que :max.',
        'string' => 'O campo :attribute não deve ter mais de :max caracteres.',
    ],
    'max_digits' => 'O campo :attribute não deve ter mais de :max dígitos.',
    'mimes' => 'O campo :attribute deve ser um ficheiro do tipo: :values.',
    'mimetypes' => 'O campo :attribute deve ser um ficheiro do tipo: :values.',
    'min' => [
        'array' => 'O campo :attribute deve ter pelo menos :min itens.',
        'file' => 'O campo :attribute deve ter pelo menos :min kilobytes.',
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'min_digits' => 'O campo :attribute deve ter pelo menos :min dígitos.',
    'multiple_of' => 'O campo :attribute deve ser um múltiplo de :value.',
    'not_in' => 'O valor selecionado para :attribute é inválido.',
    'not_regex' => 'O formato do campo :attribute é inválido.',
    'numeric' => 'O campo :attribute deve ser um número.',
    'password' => [
        'letters' => 'O campo :attribute deve conter pelo menos uma letra.',
        'mixed' => 'O campo :attribute deve conter pelo menos uma letra maiúscula e uma minúscula.',
        'numbers' => 'O campo :attribute deve conter pelo menos um número.',
        'symbols' => 'O campo :attribute deve conter pelo menos um símbolo.',
        'uncompromised' => 'O valor do campo :attribute apareceu numa fuga de dados. Por favor, escolha outro valor.',
    ],
    'present' => 'O campo :attribute deve estar presente.',
    'prohibited' => 'O campo :attribute é proibido.',
    'prohibited_if' => 'O campo :attribute é proibido quando :other é :value.',
    'prohibited_unless' => 'O campo :attribute é proibido a menos que :other esteja em :values.',
    'prohibits' => 'O campo :attribute proíbe a presença de :other.',
    'regex' => 'O formato do campo :attribute é inválido.',
    'required' => 'O campo :attribute é obrigatório.',
    'required_array_keys' => 'O campo :attribute deve conter entradas para: :values.',
    'required_if' => 'O campo :attribute é obrigatório quando :other é :value.',
    'required_if_accepted' => 'O campo :attribute é obrigatório quando :other é aceite.',
    'required_unless' => 'O campo :attribute é obrigatório a menos que :other esteja em :values.',
    'required_with' => 'O campo :attribute é obrigatório quando :values está presente.',
    'required_with_all' => 'O campo :attribute é obrigatório quando :values estão presentes.',
    'required_without' => 'O campo :attribute é obrigatório quando :values não está presente.',
    'required_without_all' => 'O campo :attribute é obrigatório quando nenhum dos :values está presente.',
    'same' => 'Os campos :attribute e :other devem corresponder.',
    'size' => [
        'array' => 'O campo :attribute deve conter :size itens.',
        'file' => 'O campo :attribute deve ter :size kilobytes.',
        'numeric' => 'O campo :attribute deve ser :size.',
        'string' => 'O campo :attribute deve ter :size caracteres.',
    ],
    'starts_with' => 'O campo :attribute deve começar com: :values.',
    'string' => 'O campo :attribute deve ser uma string.',
    'timezone' => 'O campo :attribute deve ser um fuso horário válido.',
    'unique' => 'O valor do campo :attribute já foi utilizado.',
    'uploaded' => 'O carregamento do campo :attribute falhou.',
    'url' => 'O campo :attribute deve ser um URL válido.',
    'uuid' => 'O campo :attribute deve ser um UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    // Custom messages for license document validation
    'invalid_owner_type' => 'Tipo de proprietário inválido para validação de documentos.',
    'missing_required_document' => 'Documento obrigatório em falta: :document',

    // Import validation messages
    'email_already_exists' => 'O email :email já existe',
    'field_required' => ':field é obrigatório',
    'country_not_found' => 'País \':country\' não encontrado',
    'country_id_numeric' => 'O ID do país deve ser numérico',
    'entity_not_found' => 'Entidade com numero de socio ":member_number" não encontrada',
    'invalid_date_format' => 'Formato de data inválido para :field: :value. Use DD/MM/AAAA, DD-MM-AAAA, ou AAAA-MM-DD',

    // Individual form validation
    'name_required' => 'O campo nome é obrigatório.',
    'surname_required' => 'O campo apelido é obrigatório.',
    'full_name_required' => 'O campo nome completo é obrigatório.',
    'birthdate_required' => 'O campo data de nascimento é obrigatório.',
    'country_required' => 'O campo nacionalidade é obrigatório.',
    'email_already_registered' => 'Este endereço de email já está registado.',
    'photo_required' => 'A foto de perfil é obrigatória.',
    'file_must_be_image' => 'O ficheiro deve ser uma imagem.',
    'photo_must_be_jpeg_png' => 'A foto deve ser um ficheiro JPEG ou PNG.',
    'photo_max_2mb' => 'A foto não pode ter mais de 2MB.',
    'district_required' => 'O campo distrito é obrigatório.',
    'invalid_district' => 'O distrito selecionado é inválido.',
    'sex_required' => 'O campo sexo é obrigatório.',
    'member_category_required' => 'Deve selecionar pelo menos uma categoria de membro.',
    'vat_number_required' => 'O número de identificação fiscal (NIF) é obrigatório.',
    'phone_required' => 'O campo telefone é obrigatório.',
    'address_required' => 'O campo morada é obrigatório.',
    'location_required' => 'O campo localidade é obrigatório.',
    'postal_code_required' => 'O campo código postal é obrigatório.',
    'doc_type_required' => 'O tipo de documento de identificação é obrigatório.',
    'doc_number_required' => 'O número do documento de identificação é obrigatório.',
    'doc_expiry_required' => 'A data de validade do documento é obrigatória.',
    'individual_already_exists' => 'Já existe um indivíduo com o mesmo nome, apelido, data de nascimento e país.',

    // Event attribute validation messages
    'attributes' => [
        'is_required' => ':attribute é obrigatório.',
        'validation_failed' => 'Validação falhou para :attribute: :rule',
        'must_be_equal' => ':attribute deve ser igual a :value.',
        'must_not_be_equal' => ':attribute não deve ser igual a :value.',
        'must_be_identical' => ':attribute deve ser idêntico a :value.',
        'must_not_be_identical' => ':attribute não deve ser idêntico a :value.',
        'must_be_greater_than' => ':attribute deve ser maior que :value.',
        'must_be_less_than' => ':attribute deve ser menor que :value.',
        'must_be_greater_or_equal' => ':attribute deve ser maior ou igual a :value.',
        'must_be_less_or_equal' => ':attribute deve ser menor ou igual a :value.',
        'invalid_format' => ':attribute não corresponde ao formato exigido.',
        'must_start_with' => ':attribute deve começar com :value.',
        'must_end_with' => ':attribute deve terminar com :value.',
        'must_contain' => ':attribute deve conter :value.',
        'must_not_exceed' => ':attribute não deve exceder :value.',
        'must_be_at_least' => ':attribute deve ser pelo menos :value.',
        'max_occurrences' => ':attribute pode ocorrer no máximo :value vezes.',
        'must_exist_in_array' => ':attribute deve existir nos valores permitidos.',
        'is_invalid' => ':attribute é inválido.',
        'provide_value' => 'Por favor, forneça um valor para :attribute. Esta informação é obrigatória.',
        'exceeds_maximum' => 'O valor de :attribute excede o limite máximo permitido.',
        'below_minimum' => 'O valor de :attribute está abaixo do mínimo exigido.',
        'incorrect_format' => 'O valor de :attribute não está no formato correto.',
        'already_used' => 'Este valor para :attribute já foi utilizado.',
        'not_valid' => 'O valor fornecido para :attribute não é válido. Por favor, verifique e tente novamente.',
    ],
];
