<?php

return [
    // Create individual form
    'create_individual' => 'Crear cuenta de miembro individual',
    'full_name' => 'Nombre completo',
    'sex' => 'Sexo',
    'male' => 'Hombre',
    'female' => 'Mujer',
    'vat_number' => 'Número de identificación fiscal (NIF)',
    'phone' => 'Teléfono',

    // User login information section
    'user_login_information' => 'Información de acceso del usuario',
    'user_login_description' => 'Elige el correo electrónico para la autenticación de este usuario. Se enviará un correo para que la persona registre sus propias credenciales.',
    'login_email' => 'Correo de acceso',
    'email_credential_help' => 'Credencial de correo electrónico para que la persona inicie sesión',

    // Form sections
    'personal_information' => 'Información personal',
    'social_media_optional' => 'Opcional: añade perfiles de redes sociales',
    'address_placeholder' => 'Nombre de la calle, número de puerta',
    'single_name_hint' => 'Introduce solo un nombre',
    'photo_max_size_hint' => 'Las fotos deben ser inferiores a 2 MB',

    // Terms and Privacy Policy acceptance (entity creating individual)
    'terms_privacy_title' => 'Aceptación de los términos de uso y la política de privacidad',
    'terms_privacy_text' => 'Confirmo que cuento con la autorización del miembro individual para crear su cuenta personal y que le he informado de los términos de uso y la política de privacidad del portal.',
    'terms_privacy_checkbox' => 'Confirmo que he leído y acepto las condiciones descritas anteriormente.',
    'terms_privacy_required' => 'Debes confirmar que cuentas con la autorización del miembro individual para crear su cuenta.',

    // Public registration form
    'registration_title' => 'Registro de cuenta individual',
    'individual_registration' => 'Registro individual',
    'photo' => 'Foto',
    'first_name' => 'Nombre',
    'address' => 'Dirección',
    'district' => 'Distrito',
    'location' => 'Localidad',
    'postal_code' => 'Código postal',
    'identification_document' => 'Documento de identificación',
    'document_type' => 'Tipo de documento',
    'document_number' => 'Número de documento',
    'expiry_date' => 'Fecha de caducidad',
    'login_credentials' => 'Datos de usuario y acceso',
    'login_credentials_description' => 'Necesitas crear una cuenta para iniciar sesión en la plataforma.',
    'password' => 'Contraseña',
    'confirm_password' => 'Confirmar contraseña',
    'terms_and_conditions' => 'Términos y condiciones',
    'terms_declaration_prefix' => 'Declaro que he leído y acepto los',
    'terms_of_service' => 'Términos del servicio',
    'terms_declaration_middle' => 'y la',
    'privacy_policy' => 'Política de privacidad',
    'data_sharing_declaration_prefix' => 'Autorizo el intercambio de mis datos con terceros autorizados para los fines descritos en la',
    'data_sharing_policy' => 'Política de intercambio de datos',
    'submit_registration' => 'Enviar registro',

    // Document type options
    'doc_types' => [
        'identity_card' => 'Documento de identidad',
        'citizen_card' => 'Tarjeta de ciudadano',
        'foreign_identity_card' => 'Documento de identidad de extranjero',
        'permanent_residence_card' => 'Tarjeta de residencia permanente',
        'passport' => 'Pasaporte',
    ],

    // Profile controller messages
    'error_saving_data' => 'Error al guardar los datos, por favor contacta con la administración.',
    'profile_updated_successfully' => 'Perfil actualizado correctamente.',
    'invalid_file_upload' => 'Archivo subido no válido.',
    'image_upload_failed' => 'Error al subir la imagen: prueba con otra imagen o comprime la actual.',

    // Validation messages
    'duplicate_individual_exists' => 'Ya existe una persona con el mismo nombre, apellido, fecha de nacimiento y país.',
    'invalid_district' => 'El distrito seleccionado no es válido.',
    'validation' => [
        'photo_required' => 'La foto es obligatoria.',
        'file_must_be_image' => 'El archivo debe ser una imagen.',
        'photo_mimes' => 'La foto debe ser un archivo JPEG o PNG.',
        'photo_max_size' => 'La foto no puede superar los 2 MB.',
        'name_required' => 'El campo del nombre es obligatorio.',
        'surname_required' => 'El campo del apellido es obligatorio.',
        'full_name_required' => 'El campo del nombre completo es obligatorio.',
        'birthdate_required' => 'El campo de la fecha de nacimiento es obligatorio.',
        'country_required' => 'El campo del país es obligatorio.',
        'district_required' => 'El campo del distrito es obligatorio.',
        'district_invalid' => 'El distrito seleccionado no es válido.',
        'gender_required' => 'El campo del sexo es obligatorio.',
        'vat_number_required' => 'El campo del NIF es obligatorio.',
        'doc_type_required' => 'El campo del tipo de documento es obligatorio.',
        'doc_number_required' => 'El campo del número de documento es obligatorio.',
        'doc_validity_required' => 'El campo de la fecha de validez del documento es obligatorio.',
        'email_already_registered' => 'Esta dirección de correo electrónico ya está registrada.',
        'terms_accepted' => 'Debes aceptar los términos del servicio.',
        'data_sharing_accepted' => 'Debes aceptar la política de intercambio de datos.',
        'entity_invalid' => 'La entidad seleccionada no es válida.',
    ],
];
