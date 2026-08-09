<?php

return [
    // Create individual form
    'create_individual' => 'Einzelmitgliedskonto erstellen',
    'full_name' => 'Vollständiger Name',
    'sex' => 'Geschlecht',
    'male' => 'Männlich',
    'female' => 'Weiblich',
    'vat_number' => 'Steueridentifikationsnummer (NIF)',
    'phone' => 'Telefon',

    // User login information section
    'user_login_information' => 'Anmeldeinformationen des Benutzers',
    'user_login_description' => 'Wählen Sie die E-Mail-Adresse für die Authentifizierung dieses Benutzers. Es wird eine E-Mail gesendet, damit die Person ihre eigenen Zugangsdaten festlegen kann.',
    'login_email' => 'Anmelde-E-Mail',
    'email_credential_help' => 'E-Mail-Zugangsdaten für die Anmeldung der Person',

    // Form sections
    'personal_information' => 'Persönliche Informationen',
    'social_media_optional' => 'Optional - Fügen Sie Social-Media-Profile hinzu',
    'address_placeholder' => 'Straßenname, Hausnummer',
    'single_name_hint' => 'Geben Sie nur einen Namen ein',
    'photo_max_size_hint' => 'Fotos müssen kleiner als 2 MB sein',

    // Terms and Privacy Policy acceptance (entity creating individual)
    'terms_privacy_title' => 'Zustimmung zu Nutzungsbedingungen und Datenschutzrichtlinie',
    'terms_privacy_text' => 'Ich bestätige, dass ich die Erlaubnis des Einzelmitglieds habe, dessen persönliches Konto zu erstellen, und dass ich es über die Nutzungsbedingungen und die Datenschutzrichtlinie des Portals informiert habe.',
    'terms_privacy_checkbox' => 'Ich bestätige, dass ich die oben beschriebenen Bedingungen gelesen habe und akzeptiere.',
    'terms_privacy_required' => 'Sie müssen bestätigen, dass Sie die Erlaubnis des Einzelmitglieds haben, dessen Konto zu erstellen.',

    // Public registration form
    'registration_title' => 'Registrierung eines Einzelkontos',
    'individual_registration' => 'Einzelregistrierung',
    'photo' => 'Foto',
    'first_name' => 'Vorname',
    'address' => 'Adresse',
    'district' => 'Bezirk',
    'location' => 'Ort',
    'postal_code' => 'Postleitzahl',
    'identification_document' => 'Ausweisdokument',
    'document_type' => 'Dokumenttyp',
    'document_number' => 'Dokumentnummer',
    'expiry_date' => 'Ablaufdatum',
    'login_credentials' => 'Benutzer- und Anmeldedaten',
    'login_credentials_description' => 'Sie müssen ein Konto erstellen, um sich auf der Plattform anzumelden.',
    'password' => 'Passwort',
    'confirm_password' => 'Passwort bestätigen',
    'terms_and_conditions' => 'Allgemeine Geschäftsbedingungen',
    'terms_declaration_prefix' => 'Ich erkläre, dass ich Folgendes gelesen habe und damit einverstanden bin:',
    'terms_of_service' => 'Nutzungsbedingungen',
    'terms_declaration_middle' => 'und die',
    'privacy_policy' => 'Datenschutzrichtlinie',
    'data_sharing_declaration_prefix' => 'Ich autorisiere die Weitergabe meiner Daten an autorisierte Dritte für die Zwecke, die beschrieben sind in der',
    'data_sharing_policy' => 'Richtlinie zur Datenweitergabe',
    'submit_registration' => 'Registrierung absenden',

    // Document type options
    'doc_types' => [
        'identity_card' => 'Personalausweis',
        'citizen_card' => 'Bürgerkarte',
        'foreign_identity_card' => 'Ausländischer Personalausweis',
        'permanent_residence_card' => 'Daueraufenthaltskarte',
        'passport' => 'Reisepass',
    ],

    // Profile controller messages
    'error_saving_data' => 'Fehler beim Speichern der Daten, bitte kontaktieren Sie die Verwaltung.',
    'profile_updated_successfully' => 'Profil erfolgreich aktualisiert.',
    'invalid_file_upload' => 'Ungültiger Datei-Upload.',
    'image_upload_failed' => 'Bild-Upload fehlgeschlagen - bitte versuchen Sie ein anderes Bild oder komprimieren Sie das aktuelle.',

    // Validation messages
    'duplicate_individual_exists' => 'Eine Person mit demselben Vornamen, Nachnamen, Geburtsdatum und Land existiert bereits.',
    'invalid_district' => 'Der ausgewählte Bezirk ist ungültig.',
    'validation' => [
        'photo_required' => 'Das Foto ist erforderlich.',
        'file_must_be_image' => 'Die Datei muss ein Bild sein.',
        'photo_mimes' => 'Das Foto muss eine JPEG- oder PNG-Datei sein.',
        'photo_max_size' => 'Das Foto darf nicht größer als 2 MB sein.',
        'name_required' => 'Das Namensfeld ist erforderlich.',
        'surname_required' => 'Das Nachnamensfeld ist erforderlich.',
        'full_name_required' => 'Das Feld für den vollständigen Namen ist erforderlich.',
        'birthdate_required' => 'Das Geburtsdatumsfeld ist erforderlich.',
        'country_required' => 'Das Länderfeld ist erforderlich.',
        'district_required' => 'Das Bezirksfeld ist erforderlich.',
        'district_invalid' => 'Der ausgewählte Bezirk ist ungültig.',
        'gender_required' => 'Das Geschlechtsfeld ist erforderlich.',
        'vat_number_required' => 'Das NIF-Feld ist erforderlich.',
        'doc_type_required' => 'Das Feld für den Dokumenttyp ist erforderlich.',
        'doc_number_required' => 'Das Feld für die Dokumentnummer ist erforderlich.',
        'doc_validity_required' => 'Das Feld für das Gültigkeitsdatum des Dokuments ist erforderlich.',
        'email_already_registered' => 'Diese E-Mail-Adresse ist bereits registriert.',
        'terms_accepted' => 'Sie müssen die Nutzungsbedingungen akzeptieren.',
        'data_sharing_accepted' => 'Sie müssen die Richtlinie zur Datenweitergabe akzeptieren.',
        'entity_invalid' => 'Die ausgewählte Organisation ist ungültig.',
    ],
];
