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

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'accepted_if' => 'El campo :attribute debe ser aceptado cuando :other es :value.',
    'active_url' => 'El campo :attribute no es una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El campo :attribute solo debe contener letras.',
    'alpha_dash' => 'El campo :attribute solo debe contener letras, números, guiones y guiones bajos.',
    'alpha_num' => 'El campo :attribute solo debe contener letras y números.',
    'array' => 'El campo :attribute debe ser un array.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file' => 'El campo :attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'date_equals' => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format' => 'El campo :attribute no coincide con el formato :format.',
    'declined' => 'El campo :attribute debe ser rechazado.',
    'declined_if' => 'El campo :attribute debe ser rechazado cuando :other es :value.',
    'different' => 'Los campos :attribute y :other deben ser diferentes.',
    'digits' => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'El campo :attribute tiene dimensiones de imagen no válidas.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'doesnt_end_with' => 'El campo :attribute no debe terminar con uno de los siguientes valores: :values.',
    'doesnt_start_with' => 'El campo :attribute no debe comenzar con uno de los siguientes valores: :values.',
    'email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
    'ends_with' => 'El campo :attribute debe terminar con uno de los siguientes valores: :values.',
    'enum' => 'El :attribute seleccionado no es válido.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute debe tener un valor.',
    'gt' => [
        'array' => 'El campo :attribute debe tener más de :value elementos.',
        'file' => 'El campo :attribute debe ser mayor que :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'string' => 'El campo :attribute debe tener más de :value caracteres.',
    ],
    'gte' => [
        'array' => 'El campo :attribute debe tener :value elementos o más.',
        'file' => 'El campo :attribute debe ser mayor o igual que :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'string' => 'El campo :attribute debe tener :value caracteres o más.',
    ],
    'image' => 'El campo :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado no es válido.',
    'in_array' => 'El campo :attribute no existe en :other.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'ip' => 'El campo :attribute debe ser una dirección IP válida.',
    'ipv4' => 'El campo :attribute debe ser una dirección IPv4 válida.',
    'ipv6' => 'El campo :attribute debe ser una dirección IPv6 válida.',
    'json' => 'El campo :attribute debe ser una cadena JSON válida.',
    'lt' => [
        'array' => 'El campo :attribute debe tener menos de :value elementos.',
        'file' => 'El campo :attribute debe ser menor que :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser menor que :value.',
        'string' => 'El campo :attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'El campo :attribute no debe tener más de :value elementos.',
        'file' => 'El campo :attribute debe ser menor o igual que :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser menor o igual que :value.',
        'string' => 'El campo :attribute debe tener :value caracteres o menos.',
    ],
    'mac_address' => 'El campo :attribute debe ser una dirección MAC válida.',
    'max' => [
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
        'file' => 'El campo :attribute no debe pesar más de :max kilobytes.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'max_digits' => 'El campo :attribute no debe tener más de :max dígitos.',
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
        'file' => 'El campo :attribute debe pesar al menos :min kilobytes.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'min_digits' => 'El campo :attribute debe tener al menos :min dígitos.',
    'multiple_of' => 'El campo :attribute debe ser múltiplo de :value.',
    'not_in' => 'El :attribute seleccionado no es válido.',
    'not_regex' => 'El formato del campo :attribute no es válido.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'password' => [
        'letters' => 'El campo :attribute debe contener al menos una letra.',
        'mixed' => 'El campo :attribute debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => 'El campo :attribute debe contener al menos un número.',
        'symbols' => 'El campo :attribute debe contener al menos un símbolo.',
        'uncompromised' => 'El :attribute indicado ha aparecido en una filtración de datos. Por favor, elija un :attribute diferente.',
    ],
    'present' => 'El campo :attribute debe estar presente.',
    'prohibited' => 'El campo :attribute está prohibido.',
    'prohibited_if' => 'El campo :attribute está prohibido cuando :other es :value.',
    'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other esté en :values.',
    'prohibits' => 'El campo :attribute prohíbe que :other esté presente.',
    'regex' => 'El formato del campo :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_array_keys' => 'El campo :attribute debe contener entradas para: :values.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_if_accepted' => 'El campo :attribute es obligatorio cuando :other es aceptado.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values está presente.',
    'same' => 'Los campos :attribute y :other deben coincidir.',
    'size' => [
        'array' => 'El campo :attribute debe contener :size elementos.',
        'file' => 'El campo :attribute debe pesar :size kilobytes.',
        'numeric' => 'El campo :attribute debe ser :size.',
        'string' => 'El campo :attribute debe tener :size caracteres.',
    ],
    'starts_with' => 'El campo :attribute debe comenzar con uno de los siguientes valores: :values.',
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'timezone' => 'El campo :attribute debe ser una zona horaria válida.',
    'unique' => 'El campo :attribute ya está en uso.',
    'uploaded' => 'El campo :attribute no se pudo cargar.',
    'url' => 'El campo :attribute debe ser una URL válida.',
    'uuid' => 'El campo :attribute debe ser un UUID válido.',

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
    'invalid_owner_type' => 'Tipo de propietario no válido para la validación del documento.',
    'missing_required_document' => 'Falta el documento requerido: :document',

    // Import validation messages
    'email_already_exists' => 'El correo electrónico :email ya existe',
    'field_required' => ':field es obligatorio',
    'country_not_found' => 'País \':country\' no encontrado',
    'country_id_numeric' => 'El ID del país debe ser numérico',
    'entity_not_found' => 'Entidad con número de miembro ":member_number" no encontrada',
    'invalid_date_format' => 'Formato de fecha no válido para :field: :value. Use DD/MM/YYYY, DD-MM-YYYY o YYYY-MM-DD',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    // Individual form validation
    'name_required' => 'El campo del nombre es obligatorio.',
    'surname_required' => 'El campo de los apellidos es obligatorio.',
    'full_name_required' => 'El campo del nombre completo es obligatorio.',
    'birthdate_required' => 'El campo de la fecha de nacimiento es obligatorio.',
    'country_required' => 'El campo de la nacionalidad es obligatorio.',
    'email_already_registered' => 'Esta dirección de correo electrónico ya está registrada.',
    'photo_required' => 'La foto de perfil es obligatoria.',
    'file_must_be_image' => 'El archivo debe ser una imagen.',
    'photo_must_be_jpeg_png' => 'La foto debe ser un archivo JPEG o PNG.',
    'photo_max_2mb' => 'La foto no puede pesar más de 2 MB.',
    'district_required' => 'El campo del distrito es obligatorio.',
    'invalid_district' => 'El distrito seleccionado no es válido.',
    'sex_required' => 'El campo del sexo es obligatorio.',
    'member_category_required' => 'Debe seleccionar al menos una categoría de miembro.',
    'vat_number_required' => 'El número de identificación fiscal (NIF) es obligatorio.',
    'phone_required' => 'El campo del teléfono es obligatorio.',
    'address_required' => 'El campo de la dirección es obligatorio.',
    'location_required' => 'El campo de la localidad es obligatorio.',
    'postal_code_required' => 'El campo del código postal es obligatorio.',
    'doc_type_required' => 'El tipo de documento de identidad es obligatorio.',
    'doc_number_required' => 'El número del documento de identidad es obligatorio.',
    'doc_expiry_required' => 'La fecha de caducidad del documento es obligatoria.',
    'individual_already_exists' => 'Ya existe un individuo con el mismo nombre, apellidos, fecha de nacimiento y país.',

    'attributes' => [
        // Event attribute validation messages
        'is_required' => ':attribute es obligatorio.',
        'validation_failed' => 'La validación falló para :attribute: :rule',
        'must_be_equal' => ':attribute debe ser igual a :value.',
        'must_not_be_equal' => ':attribute no debe ser igual a :value.',
        'must_be_identical' => ':attribute debe ser idéntico a :value.',
        'must_not_be_identical' => ':attribute no debe ser idéntico a :value.',
        'must_be_greater_than' => ':attribute debe ser mayor que :value.',
        'must_be_less_than' => ':attribute debe ser menor que :value.',
        'must_be_greater_or_equal' => ':attribute debe ser mayor o igual que :value.',
        'must_be_less_or_equal' => ':attribute debe ser menor o igual que :value.',
        'invalid_format' => ':attribute no coincide con el formato requerido.',
        'must_start_with' => ':attribute debe comenzar con :value.',
        'must_end_with' => ':attribute debe terminar con :value.',
        'must_contain' => ':attribute debe contener :value.',
        'must_not_exceed' => ':attribute no debe superar :value.',
        'must_be_at_least' => ':attribute debe ser al menos :value.',
        'max_occurrences' => ':attribute puede aparecer como máximo :value veces.',
        'must_exist_in_array' => ':attribute debe existir entre los valores permitidos.',
        'is_invalid' => ':attribute no es válido.',
        'provide_value' => 'Por favor, proporcione un valor para :attribute. Esta información es obligatoria.',
        'exceeds_maximum' => 'El valor de :attribute supera el límite máximo permitido.',
        'below_minimum' => 'El valor de :attribute está por debajo del mínimo requerido.',
        'incorrect_format' => 'El valor de :attribute no tiene el formato correcto.',
        'already_used' => 'Este valor de :attribute ya ha sido utilizado.',
        'not_valid' => 'El valor proporcionado para :attribute no es válido. Por favor, compruébelo e inténtelo de nuevo.',
    ],

];
