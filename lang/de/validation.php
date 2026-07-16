<?php

return [
    'currency_no_decimals' => 'Das Feld :attribute muss eine ganze Zahl sein — :currency hat keine Nachkommastellen.',

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

    'accepted' => ':attribute muss akzeptiert werden.',
    'accepted_if' => ':attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url' => ':attribute ist keine gültige URL.',
    'after' => ':attribute muss ein Datum nach dem :date sein.',
    'after_or_equal' => ':attribute muss ein Datum am oder nach dem :date sein.',
    'alpha' => ':attribute darf nur Buchstaben enthalten.',
    'alpha_dash' => ':attribute darf nur Buchstaben, Zahlen, Binde- und Unterstriche enthalten.',
    'alpha_num' => ':attribute darf nur Buchstaben und Zahlen enthalten.',
    'array' => ':attribute muss ein Array sein.',
    'before' => ':attribute muss ein Datum vor dem :date sein.',
    'before_or_equal' => ':attribute muss ein Datum am oder vor dem :date sein.',
    'between' => [
        'array' => ':attribute muss zwischen :min und :max Elemente haben.',
        'file' => ':attribute muss zwischen :min und :max Kilobyte groß sein.',
        'numeric' => ':attribute muss zwischen :min und :max liegen.',
        'string' => ':attribute muss zwischen :min und :max Zeichen lang sein.',
    ],
    'boolean' => 'Das Feld :attribute muss wahr oder falsch sein.',
    'confirmed' => 'Die Bestätigung von :attribute stimmt nicht überein.',
    'current_password' => 'Das Passwort ist falsch.',
    'date' => ':attribute ist kein gültiges Datum.',
    'date_equals' => ':attribute muss ein Datum gleich dem :date sein.',
    'date_format' => ':attribute entspricht nicht dem Format :format.',
    'declined' => ':attribute muss abgelehnt werden.',
    'declined_if' => ':attribute muss abgelehnt werden, wenn :other :value ist.',
    'different' => ':attribute und :other müssen unterschiedlich sein.',
    'digits' => ':attribute muss :digits Ziffern lang sein.',
    'digits_between' => ':attribute muss zwischen :min und :max Ziffern lang sein.',
    'dimensions' => ':attribute hat ungültige Bildabmessungen.',
    'distinct' => 'Das Feld :attribute hat einen doppelten Wert.',
    'doesnt_end_with' => ':attribute darf nicht mit einem der folgenden Werte enden: :values.',
    'doesnt_start_with' => ':attribute darf nicht mit einem der folgenden Werte beginnen: :values.',
    'email' => ':attribute muss eine gültige E-Mail-Adresse sein.',
    'ends_with' => ':attribute muss mit einem der folgenden Werte enden: :values.',
    'enum' => 'Das ausgewählte :attribute ist ungültig.',
    'exists' => 'Das ausgewählte :attribute ist ungültig.',
    'file' => ':attribute muss eine Datei sein.',
    'filled' => 'Das Feld :attribute muss einen Wert enthalten.',
    'gt' => [
        'array' => ':attribute muss mehr als :value Elemente haben.',
        'file' => ':attribute muss größer als :value Kilobyte sein.',
        'numeric' => ':attribute muss größer als :value sein.',
        'string' => ':attribute muss länger als :value Zeichen sein.',
    ],
    'gte' => [
        'array' => ':attribute muss :value oder mehr Elemente haben.',
        'file' => ':attribute muss größer oder gleich :value Kilobyte sein.',
        'numeric' => ':attribute muss größer oder gleich :value sein.',
        'string' => ':attribute muss mindestens :value Zeichen lang sein.',
    ],
    'image' => ':attribute muss ein Bild sein.',
    'in' => 'Das ausgewählte :attribute ist ungültig.',
    'in_array' => 'Das Feld :attribute existiert nicht in :other.',
    'integer' => ':attribute muss eine ganze Zahl sein.',
    'ip' => ':attribute muss eine gültige IP-Adresse sein.',
    'ipv4' => ':attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6' => ':attribute muss eine gültige IPv6-Adresse sein.',
    'json' => ':attribute muss eine gültige JSON-Zeichenkette sein.',
    'lt' => [
        'array' => ':attribute muss weniger als :value Elemente haben.',
        'file' => ':attribute muss kleiner als :value Kilobyte sein.',
        'numeric' => ':attribute muss kleiner als :value sein.',
        'string' => ':attribute muss kürzer als :value Zeichen sein.',
    ],
    'lte' => [
        'array' => ':attribute darf nicht mehr als :value Elemente haben.',
        'file' => ':attribute muss kleiner oder gleich :value Kilobyte sein.',
        'numeric' => ':attribute muss kleiner oder gleich :value sein.',
        'string' => ':attribute darf höchstens :value Zeichen lang sein.',
    ],
    'mac_address' => ':attribute muss eine gültige MAC-Adresse sein.',
    'max' => [
        'array' => ':attribute darf nicht mehr als :max Elemente haben.',
        'file' => ':attribute darf nicht größer als :max Kilobyte sein.',
        'numeric' => ':attribute darf nicht größer als :max sein.',
        'string' => ':attribute darf nicht länger als :max Zeichen sein.',
    ],
    'max_digits' => ':attribute darf nicht mehr als :max Ziffern haben.',
    'mimes' => ':attribute muss eine Datei des Typs :values sein.',
    'mimetypes' => ':attribute muss eine Datei des Typs :values sein.',
    'min' => [
        'array' => ':attribute muss mindestens :min Elemente haben.',
        'file' => ':attribute muss mindestens :min Kilobyte groß sein.',
        'numeric' => ':attribute muss mindestens :min sein.',
        'string' => ':attribute muss mindestens :min Zeichen lang sein.',
    ],
    'min_digits' => ':attribute muss mindestens :min Ziffern haben.',
    'multiple_of' => ':attribute muss ein Vielfaches von :value sein.',
    'not_in' => 'Das ausgewählte :attribute ist ungültig.',
    'not_regex' => 'Das Format von :attribute ist ungültig.',
    'numeric' => ':attribute muss eine Zahl sein.',
    'password' => [
        'letters' => ':attribute muss mindestens einen Buchstaben enthalten.',
        'mixed' => ':attribute muss mindestens einen Groß- und einen Kleinbuchstaben enthalten.',
        'numbers' => ':attribute muss mindestens eine Zahl enthalten.',
        'symbols' => ':attribute muss mindestens ein Symbol enthalten.',
        'uncompromised' => 'Das angegebene :attribute ist in einem Datenleck aufgetaucht. Bitte wählen Sie ein anderes :attribute.',
    ],
    'present' => 'Das Feld :attribute muss vorhanden sein.',
    'prohibited' => 'Das Feld :attribute ist unzulässig.',
    'prohibited_if' => 'Das Feld :attribute ist unzulässig, wenn :other :value ist.',
    'prohibited_unless' => 'Das Feld :attribute ist unzulässig, es sei denn, :other ist in :values enthalten.',
    'prohibits' => 'Das Feld :attribute verhindert, dass :other vorhanden ist.',
    'regex' => 'Das Format von :attribute ist ungültig.',
    'required' => 'Das Feld :attribute ist erforderlich.',
    'required_array_keys' => 'Das Feld :attribute muss Einträge für :values enthalten.',
    'required_if' => 'Das Feld :attribute ist erforderlich, wenn :other :value ist.',
    'required_if_accepted' => 'Das Feld :attribute ist erforderlich, wenn :other akzeptiert wird.',
    'required_unless' => 'Das Feld :attribute ist erforderlich, es sei denn, :other ist in :values enthalten.',
    'required_with' => 'Das Feld :attribute ist erforderlich, wenn :values vorhanden ist.',
    'required_with_all' => 'Das Feld :attribute ist erforderlich, wenn :values vorhanden sind.',
    'required_without' => 'Das Feld :attribute ist erforderlich, wenn :values nicht vorhanden ist.',
    'required_without_all' => 'Das Feld :attribute ist erforderlich, wenn keiner der Werte :values vorhanden ist.',
    'same' => ':attribute und :other müssen übereinstimmen.',
    'size' => [
        'array' => ':attribute muss :size Elemente enthalten.',
        'file' => ':attribute muss :size Kilobyte groß sein.',
        'numeric' => ':attribute muss :size sein.',
        'string' => ':attribute muss :size Zeichen lang sein.',
    ],
    'starts_with' => ':attribute muss mit einem der folgenden Werte beginnen: :values.',
    'string' => ':attribute muss eine Zeichenkette sein.',
    'timezone' => ':attribute muss eine gültige Zeitzone sein.',
    'unique' => ':attribute ist bereits vergeben.',
    'uploaded' => ':attribute konnte nicht hochgeladen werden.',
    'url' => ':attribute muss eine gültige URL sein.',
    'uuid' => ':attribute muss eine gültige UUID sein.',

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
    'invalid_owner_type' => 'Ungültiger Inhabertyp für die Dokumentenprüfung.',
    'missing_required_document' => 'Fehlendes erforderliches Dokument: :document',

    // Import validation messages
    'email_already_exists' => 'Die E-Mail-Adresse :email ist bereits vorhanden',
    'field_required' => ':field ist erforderlich',
    'country_not_found' => 'Land \':country\' nicht gefunden',
    'country_id_numeric' => 'Die Länder-ID muss numerisch sein',
    'entity_not_found' => 'Einrichtung mit der Mitgliedsnummer ":member_number" nicht gefunden',
    'invalid_date_format' => 'Ungültiges Datumsformat für :field: :value. Verwenden Sie TT/MM/JJJJ, TT-MM-JJJJ oder JJJJ-MM-TT',

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
    'name_required' => 'Das Feld Vorname ist erforderlich.',
    'surname_required' => 'Das Feld Nachname ist erforderlich.',
    'full_name_required' => 'Das Feld Vollständiger Name ist erforderlich.',
    'birthdate_required' => 'Das Feld Geburtsdatum ist erforderlich.',
    'country_required' => 'Das Feld Staatsangehörigkeit ist erforderlich.',
    'email_already_registered' => 'Diese E-Mail-Adresse ist bereits registriert.',
    'photo_required' => 'Das Profilbild ist erforderlich.',
    'file_must_be_image' => 'Die Datei muss ein Bild sein.',
    'photo_must_be_jpeg_png' => 'Das Foto muss eine JPEG- oder PNG-Datei sein.',
    'photo_max_2mb' => 'Das Foto darf nicht größer als 2 MB sein.',
    'district_required' => 'Das Feld Bezirk ist erforderlich.',
    'invalid_district' => 'Der ausgewählte Bezirk ist ungültig.',
    'sex_required' => 'Das Feld Geschlecht ist erforderlich.',
    'member_category_required' => 'Sie müssen mindestens eine Mitgliederkategorie auswählen.',
    'vat_number_required' => 'Die Steueridentifikationsnummer (NIF) ist erforderlich.',
    'phone_required' => 'Das Feld Telefon ist erforderlich.',
    'address_required' => 'Das Feld Anschrift ist erforderlich.',
    'location_required' => 'Das Feld Ort ist erforderlich.',
    'postal_code_required' => 'Das Feld Postleitzahl ist erforderlich.',
    'doc_type_required' => 'Die Art des Ausweisdokuments ist erforderlich.',
    'doc_number_required' => 'Die Nummer des Ausweisdokuments ist erforderlich.',
    'doc_expiry_required' => 'Das Ablaufdatum des Dokuments ist erforderlich.',
    'individual_already_exists' => 'Eine Einzelperson mit demselben Vornamen, Nachnamen, Geburtsdatum und Land existiert bereits.',

    'attributes' => [
        // Event attribute validation messages
        'is_required' => ':attribute ist erforderlich.',
        'validation_failed' => 'Validierung für :attribute fehlgeschlagen: :rule',
        'must_be_equal' => ':attribute muss gleich :value sein.',
        'must_not_be_equal' => ':attribute darf nicht gleich :value sein.',
        'must_be_identical' => ':attribute muss identisch mit :value sein.',
        'must_not_be_identical' => ':attribute darf nicht identisch mit :value sein.',
        'must_be_greater_than' => ':attribute muss größer als :value sein.',
        'must_be_less_than' => ':attribute muss kleiner als :value sein.',
        'must_be_greater_or_equal' => ':attribute muss größer oder gleich :value sein.',
        'must_be_less_or_equal' => ':attribute muss kleiner oder gleich :value sein.',
        'invalid_format' => ':attribute entspricht nicht dem erforderlichen Format.',
        'must_start_with' => ':attribute muss mit :value beginnen.',
        'must_end_with' => ':attribute muss mit :value enden.',
        'must_contain' => ':attribute muss :value enthalten.',
        'must_not_exceed' => ':attribute darf :value nicht überschreiten.',
        'must_be_at_least' => ':attribute muss mindestens :value sein.',
        'max_occurrences' => ':attribute darf höchstens :value Mal vorkommen.',
        'must_exist_in_array' => ':attribute muss in den zulässigen Werten enthalten sein.',
        'is_invalid' => ':attribute ist ungültig.',
        'provide_value' => 'Bitte geben Sie einen Wert für :attribute an. Diese Information ist erforderlich.',
        'exceeds_maximum' => 'Der Wert für :attribute überschreitet das zulässige Höchstlimit.',
        'below_minimum' => 'Der Wert für :attribute liegt unter dem erforderlichen Mindestwert.',
        'incorrect_format' => 'Der Wert für :attribute liegt nicht im korrekten Format vor.',
        'already_used' => 'Dieser Wert für :attribute wurde bereits verwendet.',
        'not_valid' => 'Der für :attribute angegebene Wert ist ungültig. Bitte überprüfen Sie ihn und versuchen Sie es erneut.',
    ],

];
