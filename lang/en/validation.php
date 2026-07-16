<?php

return [
    'currency_no_decimals' => 'The :attribute must be a whole number — :currency has no decimal places.',

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

    'accepted' => 'The :attribute must be accepted.',
    'accepted_if' => 'The :attribute must be accepted when :other is :value.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute must have between :min and :max items.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute must be between :min and :max.',
        'string' => 'The :attribute must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'declined' => 'The :attribute must be declined.',
    'declined_if' => 'The :attribute must be declined when :other is :value.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute may not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute may not start with one of the following: :values.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute must have more than :value items.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'numeric' => 'The :attribute must be greater than :value.',
        'string' => 'The :attribute must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute must have :value items or more.',
        'file' => 'The :attribute must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'array' => 'The :attribute must have less than :value items.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'numeric' => 'The :attribute must be less than :value.',
        'string' => 'The :attribute must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute must not have more than :value items.',
        'file' => 'The :attribute must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'string' => 'The :attribute must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute must not have more than :max items.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute must not be greater than :max.',
        'string' => 'The :attribute must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute must not have more than :max digits.',
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute must have at least :min items.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute must have at least :min digits.',
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => [
        'letters' => 'The :attribute must contain at least one letter.',
        'mixed' => 'The :attribute must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute must contain at least one number.',
        'symbols' => 'The :attribute must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'array' => 'The :attribute must contain :size items.',
        'file' => 'The :attribute must be :size kilobytes.',
        'numeric' => 'The :attribute must be :size.',
        'string' => 'The :attribute must be :size characters.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',

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
    'invalid_owner_type' => 'Invalid owner type for document validation.',
    'missing_required_document' => 'Missing required document: :document',

    // Import validation messages
    'email_already_exists' => 'Email :email already exists',
    'field_required' => ':field is required',
    'country_not_found' => 'Country \':country\' not found',
    'country_id_numeric' => 'Country ID must be numeric',
    'entity_not_found' => 'Entity with member number ":member_number" not found',
    'invalid_date_format' => 'Invalid date format for :field: :value. Use DD/MM/YYYY, DD-MM-YYYY, or YYYY-MM-DD',

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
    'name_required' => 'The name field is required.',
    'surname_required' => 'The surname field is required.',
    'full_name_required' => 'The full name field is required.',
    'birthdate_required' => 'The birthdate field is required.',
    'country_required' => 'The nationality field is required.',
    'email_already_registered' => 'This email address is already registered.',
    'photo_required' => 'The profile photo is required.',
    'file_must_be_image' => 'The file must be an image.',
    'photo_must_be_jpeg_png' => 'The photo must be a JPEG or PNG file.',
    'photo_max_2mb' => 'The photo may not be greater than 2MB.',
    'district_required' => 'The district field is required.',
    'invalid_district' => 'The selected district is invalid.',
    'sex_required' => 'The sex field is required.',
    'member_category_required' => 'You must select at least one member category.',
    'vat_number_required' => 'The tax identification number (NIF) is required.',
    'phone_required' => 'The phone field is required.',
    'address_required' => 'The address field is required.',
    'location_required' => 'The location field is required.',
    'postal_code_required' => 'The postal code field is required.',
    'doc_type_required' => 'The identification document type is required.',
    'doc_number_required' => 'The identification document number is required.',
    'doc_expiry_required' => 'The document expiry date is required.',
    'individual_already_exists' => 'An individual with the same name, surname, birthdate and country already exists.',

    'attributes' => [
        // Event attribute validation messages
        'is_required' => ':attribute is required.',
        'validation_failed' => 'Validation failed for :attribute: :rule',
        'must_be_equal' => ':attribute must be equal to :value.',
        'must_not_be_equal' => ':attribute must not be equal to :value.',
        'must_be_identical' => ':attribute must be identical to :value.',
        'must_not_be_identical' => ':attribute must not be identical to :value.',
        'must_be_greater_than' => ':attribute must be greater than :value.',
        'must_be_less_than' => ':attribute must be less than :value.',
        'must_be_greater_or_equal' => ':attribute must be greater than or equal to :value.',
        'must_be_less_or_equal' => ':attribute must be less than or equal to :value.',
        'invalid_format' => ':attribute does not match the required format.',
        'must_start_with' => ':attribute must start with :value.',
        'must_end_with' => ':attribute must end with :value.',
        'must_contain' => ':attribute must contain :value.',
        'must_not_exceed' => ':attribute must not exceed :value.',
        'must_be_at_least' => ':attribute must be at least :value.',
        'max_occurrences' => ':attribute can occur at most :value times.',
        'must_exist_in_array' => ':attribute must exist in the allowed values.',
        'is_invalid' => ':attribute is invalid.',
        'provide_value' => 'Please provide a value for :attribute. This information is required.',
        'exceeds_maximum' => 'The value for :attribute exceeds the maximum allowed limit.',
        'below_minimum' => 'The value for :attribute is below the minimum required.',
        'incorrect_format' => 'The value for :attribute is not in the correct format.',
        'already_used' => 'This value for :attribute has already been used.',
        'not_valid' => 'The value provided for :attribute is not valid. Please check and try again.',
    ],

];
