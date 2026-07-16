<?php

return [
    'currency_no_decimals' => 'Le champ :attribute doit être un nombre entier — :currency n\'a pas de décimales.',

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

    'accepted' => 'Le champ :attribute doit être accepté.',
    'accepted_if' => 'Le champ :attribute doit être accepté lorsque :other vaut :value.',
    'active_url' => 'Le champ :attribute n\'est pas une URL valide.',
    'after' => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha' => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne doit contenir que des lettres, des chiffres, des tirets et des traits de soulignement.',
    'alpha_num' => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'before' => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date antérieure ou égale au :date.',
    'between' => [
        'array' => 'Le champ :attribute doit contenir entre :min et :max éléments.',
        'file' => 'Le champ :attribute doit être compris entre :min et :max kilo-octets.',
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => 'Le champ :attribute n\'est pas une date valide.',
    'date_equals' => 'Le champ :attribute doit être une date égale au :date.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'declined' => 'Le champ :attribute doit être refusé.',
    'declined_if' => 'Le champ :attribute doit être refusé lorsque :other vaut :value.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions' => 'Le champ :attribute a des dimensions d\'image invalides.',
    'distinct' => 'Le champ :attribute a une valeur en double.',
    'doesnt_end_with' => 'Le champ :attribute ne doit pas se terminer par l\'un des éléments suivants : :values.',
    'doesnt_start_with' => 'Le champ :attribute ne doit pas commencer par l\'un des éléments suivants : :values.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'ends_with' => 'Le champ :attribute doit se terminer par l\'un des éléments suivants : :values.',
    'enum' => 'La valeur sélectionnée pour :attribute est invalide.',
    'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
    'file' => 'Le champ :attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',
    'gt' => [
        'array' => 'Le champ :attribute doit contenir plus de :value éléments.',
        'file' => 'Le champ :attribute doit être supérieur à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'string' => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],
    'gte' => [
        'array' => 'Le champ :attribute doit contenir au moins :value éléments.',
        'file' => 'Le champ :attribute doit être supérieur ou égal à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au moins :value caractères.',
    ],
    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'in_array' => 'Le champ :attribute n\'existe pas dans :other.',
    'integer' => 'Le champ :attribute doit être un entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4' => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6' => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json' => 'Le champ :attribute doit être une chaîne JSON valide.',
    'lt' => [
        'array' => 'Le champ :attribute doit contenir moins de :value éléments.',
        'file' => 'Le champ :attribute doit être inférieur à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'string' => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],
    'lte' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :value éléments.',
        'file' => 'Le champ :attribute doit être inférieur ou égal à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au maximum :value caractères.',
    ],
    'mac_address' => 'Le champ :attribute doit être une adresse MAC valide.',
    'max' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file' => 'Le champ :attribute ne doit pas dépasser :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne doit pas être supérieur à :max.',
        'string' => 'Le champ :attribute ne doit pas contenir plus de :max caractères.',
    ],
    'max_digits' => 'Le champ :attribute ne doit pas contenir plus de :max chiffres.',
    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le champ :attribute doit faire au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'min_digits' => 'Le champ :attribute doit contenir au moins :min chiffres.',
    'multiple_of' => 'Le champ :attribute doit être un multiple de :value.',
    'not_in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'not_regex' => 'Le format du champ :attribute est invalide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'password' => [
        'letters' => 'Le champ :attribute doit contenir au moins une lettre.',
        'mixed' => 'Le champ :attribute doit contenir au moins une lettre majuscule et une lettre minuscule.',
        'numbers' => 'Le champ :attribute doit contenir au moins un chiffre.',
        'symbols' => 'Le champ :attribute doit contenir au moins un symbole.',
        'uncompromised' => 'Le champ :attribute fourni est apparu dans une fuite de données. Veuillez choisir un autre :attribute.',
    ],
    'present' => 'Le champ :attribute doit être présent.',
    'prohibited' => 'Le champ :attribute est interdit.',
    'prohibited_if' => 'Le champ :attribute est interdit lorsque :other vaut :value.',
    'prohibited_unless' => 'Le champ :attribute est interdit sauf si :other figure dans :values.',
    'prohibits' => 'Le champ :attribute empêche :other d\'être présent.',
    'regex' => 'Le format du champ :attribute est invalide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_array_keys' => 'Le champ :attribute doit contenir des entrées pour : :values.',
    'required_if' => 'Le champ :attribute est obligatoire lorsque :other vaut :value.',
    'required_if_accepted' => 'Le champ :attribute est obligatoire lorsque :other est accepté.',
    'required_unless' => 'Le champ :attribute est obligatoire sauf si :other figure dans :values.',
    'required_with' => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_with_all' => 'Le champ :attribute est obligatoire lorsque :values sont présents.',
    'required_without' => 'Le champ :attribute est obligatoire lorsque :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est obligatoire lorsque aucun des :values n\'est présent.',
    'same' => 'Les champs :attribute et :other doivent correspondre.',
    'size' => [
        'array' => 'Le champ :attribute doit contenir :size éléments.',
        'file' => 'Le champ :attribute doit faire :size kilo-octets.',
        'numeric' => 'Le champ :attribute doit être égal à :size.',
        'string' => 'Le champ :attribute doit contenir :size caractères.',
    ],
    'starts_with' => 'Le champ :attribute doit commencer par l\'un des éléments suivants : :values.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone' => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique' => 'La valeur du champ :attribute est déjà utilisée.',
    'uploaded' => 'Le téléversement du champ :attribute a échoué.',
    'url' => 'Le champ :attribute doit être une URL valide.',
    'uuid' => 'Le champ :attribute doit être un UUID valide.',

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
    'invalid_owner_type' => 'Type de propriétaire invalide pour la validation du document.',
    'missing_required_document' => 'Document requis manquant : :document',

    // Import validation messages
    'email_already_exists' => 'L\'e-mail :email existe déjà',
    'field_required' => ':field est obligatoire',
    'country_not_found' => 'Pays « :country » introuvable',
    'country_id_numeric' => 'L\'identifiant du pays doit être numérique',
    'entity_not_found' => 'Entité portant le numéro de membre « :member_number » introuvable',
    'invalid_date_format' => 'Format de date invalide pour :field : :value. Utilisez JJ/MM/AAAA, JJ-MM-AAAA ou AAAA-MM-JJ',

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
    'name_required' => 'Le champ nom est obligatoire.',
    'surname_required' => 'Le champ nom de famille est obligatoire.',
    'full_name_required' => 'Le champ nom complet est obligatoire.',
    'birthdate_required' => 'Le champ date de naissance est obligatoire.',
    'country_required' => 'Le champ nationalité est obligatoire.',
    'email_already_registered' => 'Cette adresse e-mail est déjà enregistrée.',
    'photo_required' => 'La photo de profil est obligatoire.',
    'file_must_be_image' => 'Le fichier doit être une image.',
    'photo_must_be_jpeg_png' => 'La photo doit être un fichier JPEG ou PNG.',
    'photo_max_2mb' => 'La photo ne doit pas dépasser 2 Mo.',
    'district_required' => 'Le champ district est obligatoire.',
    'invalid_district' => 'Le district sélectionné est invalide.',
    'sex_required' => 'Le champ sexe est obligatoire.',
    'member_category_required' => 'Vous devez sélectionner au moins une catégorie de membre.',
    'vat_number_required' => 'Le numéro d\'identification fiscale (NIF) est obligatoire.',
    'phone_required' => 'Le champ téléphone est obligatoire.',
    'address_required' => 'Le champ adresse est obligatoire.',
    'location_required' => 'Le champ localité est obligatoire.',
    'postal_code_required' => 'Le champ code postal est obligatoire.',
    'doc_type_required' => 'Le type de pièce d\'identité est obligatoire.',
    'doc_number_required' => 'Le numéro de la pièce d\'identité est obligatoire.',
    'doc_expiry_required' => 'La date d\'expiration du document est obligatoire.',
    'individual_already_exists' => 'Une personne portant les mêmes nom, prénom, date de naissance et pays existe déjà.',

    'attributes' => [
        // Event attribute validation messages
        'is_required' => ':attribute est obligatoire.',
        'validation_failed' => 'La validation a échoué pour :attribute : :rule',
        'must_be_equal' => ':attribute doit être égal à :value.',
        'must_not_be_equal' => ':attribute ne doit pas être égal à :value.',
        'must_be_identical' => ':attribute doit être identique à :value.',
        'must_not_be_identical' => ':attribute ne doit pas être identique à :value.',
        'must_be_greater_than' => ':attribute doit être supérieur à :value.',
        'must_be_less_than' => ':attribute doit être inférieur à :value.',
        'must_be_greater_or_equal' => ':attribute doit être supérieur ou égal à :value.',
        'must_be_less_or_equal' => ':attribute doit être inférieur ou égal à :value.',
        'invalid_format' => ':attribute ne correspond pas au format requis.',
        'must_start_with' => ':attribute doit commencer par :value.',
        'must_end_with' => ':attribute doit se terminer par :value.',
        'must_contain' => ':attribute doit contenir :value.',
        'must_not_exceed' => ':attribute ne doit pas dépasser :value.',
        'must_be_at_least' => ':attribute doit être au moins :value.',
        'max_occurrences' => ':attribute peut apparaître au maximum :value fois.',
        'must_exist_in_array' => ':attribute doit figurer parmi les valeurs autorisées.',
        'is_invalid' => ':attribute est invalide.',
        'provide_value' => 'Veuillez fournir une valeur pour :attribute. Cette information est obligatoire.',
        'exceeds_maximum' => 'La valeur de :attribute dépasse la limite maximale autorisée.',
        'below_minimum' => 'La valeur de :attribute est inférieure au minimum requis.',
        'incorrect_format' => 'La valeur de :attribute n\'est pas au bon format.',
        'already_used' => 'Cette valeur de :attribute a déjà été utilisée.',
        'not_valid' => 'La valeur fournie pour :attribute n\'est pas valide. Veuillez vérifier et réessayer.',
    ],

];
