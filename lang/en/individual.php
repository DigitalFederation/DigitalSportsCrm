<?php

return [
    // Create individual form
    'create_individual' => 'Create Individual Member Account',
    'full_name' => 'Full Name',
    'sex' => 'Sex',
    'male' => 'Male',
    'female' => 'Female',
    'vat_number' => 'Tax Identification Number (NIF)',
    'phone' => 'Phone',

    // User login information section
    'user_login_information' => 'User login information',
    'user_login_description' => 'Choose the email for the authentication of this user. An email will be sent for the person to register their own credentials.',
    'login_email' => 'Login email',
    'email_credential_help' => 'Email credential for the Individual to login',

    // Form sections
    'personal_information' => 'Personal Information',
    'social_media_optional' => 'Optional - Add social media profiles',
    'address_placeholder' => 'Street name, door number',
    'single_name_hint' => 'Enter only one name',
    'photo_max_size_hint' => 'Photos must be smaller than 2MB',

    // Terms and Privacy Policy acceptance (entity creating individual)
    'terms_privacy_title' => 'Terms of Use and Privacy Policy Acceptance',
    'terms_privacy_text' => 'I confirm that I have authorization from the individual member to create their personal account, and that I have informed them of the terms of use and privacy policy of the portal.',
    'terms_privacy_checkbox' => 'I confirm that I have read and accept the conditions described above.',
    'terms_privacy_required' => 'You must confirm that you have authorization from the individual member to create their account.',

    // Public registration form
    'registration_title' => 'Individual Account Registration',
    'individual_registration' => 'Individual Registration',
    'photo' => 'Photo',
    'first_name' => 'First Name',
    'address' => 'Address',
    'district' => 'District',
    'location' => 'Location',
    'postal_code' => 'Postal Code',
    'identification_document' => 'Identification Document',
    'document_type' => 'Document Type',
    'document_number' => 'Document Number',
    'expiry_date' => 'Expiry Date',
    'login_credentials' => 'User and Login Details',
    'login_credentials_description' => 'You need to create an account to sign in to the platform.',
    'password' => 'Password',
    'confirm_password' => 'Confirm Password',
    'terms_and_conditions' => 'Terms and Conditions',
    'terms_declaration_prefix' => 'I declare that I have read and agree to the',
    'terms_of_service' => 'Terms of Service',
    'terms_declaration_middle' => 'and the',
    'privacy_policy' => 'Privacy Policy',
    'data_sharing_declaration_prefix' => 'I authorize the sharing of my data with authorized third parties for the purposes described in the',
    'data_sharing_policy' => 'Data Sharing Policy',
    'submit_registration' => 'Submit Registration',

    // Document type options
    'doc_types' => [
        'identity_card' => 'Identity Card',
        'citizen_card' => 'Citizen Card',
        'foreign_identity_card' => 'Foreign Identity Card',
        'permanent_residence_card' => 'Permanent Residence Card',
        'passport' => 'Passport',
    ],

    // Profile controller messages
    'error_saving_data' => 'Error saving data, please contact the administration.',
    'profile_updated_successfully' => 'Profile updated successfully.',
    'invalid_file_upload' => 'Invalid file upload.',
    'image_upload_failed' => 'Image upload failed - please try a different image or compress the current one.',

    // Validation messages
    'duplicate_individual_exists' => 'An individual with the same name, surname, birthdate and country already exists.',
    'invalid_district' => 'The selected district is invalid.',
    'validation' => [
        'photo_required' => 'The photo is required.',
        'file_must_be_image' => 'The file must be an image.',
        'photo_mimes' => 'The photo must be a JPEG or PNG file.',
        'photo_max_size' => 'The photo may not be greater than 2MB.',
        'name_required' => 'The name field is required.',
        'surname_required' => 'The surname field is required.',
        'full_name_required' => 'The full name field is required.',
        'birthdate_required' => 'The birthdate field is required.',
        'country_required' => 'The country field is required.',
        'district_required' => 'The district field is required.',
        'district_invalid' => 'The selected district is invalid.',
        'gender_required' => 'The gender field is required.',
        'vat_number_required' => 'The NIF field is required.',
        'doc_type_required' => 'The document type field is required.',
        'doc_number_required' => 'The document number field is required.',
        'doc_validity_required' => 'The document validity date field is required.',
        'email_already_registered' => 'This email address is already registered.',
        'terms_accepted' => 'You must accept the terms of service.',
        'data_sharing_accepted' => 'You must accept the data sharing policy.',
        'entity_invalid' => 'The selected entity is invalid.',
    ],
];
