<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$federationName = $brand['name'];
$federationShortName = $brand['short_name'];
$portalName = $brand['portal_name'];
$internationalName = $internationalBrand['name'];

return [
    // Common
    'privacy_policy' => 'Privacy Policy',
    'privacy_policy_title' => 'PRIVACY POLICY',
    'terms_of_use' => 'Terms of Use',
    'terms_of_use_title' => 'TERMS OF USE',
    'last_update' => 'Last update',
    'entity' => 'Entity',
    'address' => 'Address',
    'email' => 'Email',
    'contacts' => 'Contacts',
    'federation_full_name' => "{$federationName} ({$federationShortName})",

    // Privacy Policy
    'privacy' => [
        'responsible_entity' => 'Responsible Entity',
        'responsible_entity_text' => "{$federationName} ({$federationShortName}) is the entity responsible for processing personal data collected through this Portal. Public deployments must adapt this text to the applicable data protection law and operating jurisdiction.",
        'dpo' => 'Data Protection Officer',
        'dpo_department' => 'Administrative and Financial Department',

        'legal_framework' => 'Legal Framework',
        'legal_framework_intro' => "The processing of personal data by {$federationShortName} is governed by the following legislation:",
        'gdpr_reference' => 'Regulation (EU) 2016/679 of the European Parliament and of the Council (General Data Protection Regulation - GDPR)',
        'law_58_2019' => 'Applicable national implementation law for data protection, where relevant',
        'law_41_2004' => 'Applicable electronic communications and privacy law, where relevant',

        'collected_data' => 'Personal Data Collected',
        'collected_data_intro' => "In the context of its activities, {$federationShortName} collects and processes the following categories of personal data:",

        'identification_data' => 'Identification Data',
        'full_name' => 'Full name',
        'birth_date' => 'Date of birth',
        'gender' => 'Gender',
        'nationality' => 'Nationality',
        'tax_number' => 'Tax Identification Number (NIF)',
        'id_document' => 'ID document number and type',
        'photo' => 'Photograph',

        'contact_data' => 'Contact Data',
        'full_address' => 'Full address',
        'email_address' => 'Email address',
        'phone_number' => 'Phone/mobile number',

        'sports_data' => 'Sports Data',
        'certifications_brevets' => 'Certifications and brevets obtained',
        'federative_licenses' => 'Federative licenses',
        'entity_affiliations' => 'Entity affiliations (clubs, schools, diving centers)',
        'event_participation' => 'Participation in events and competitions',
        'sports_results' => 'Sports results',

        'health_data' => 'Health Data (Special Category)',
        'health_data_text' => 'For the purpose of issuing sports licenses and insurance, it may be necessary to process data related to medical fitness for sports practice. This data is processed with enhanced security measures and only with the explicit consent of the data subject.',

        'processing_purposes' => 'Processing Purposes',
        'processing_purposes_intro' => 'Personal data is processed for the following purposes:',
        'purpose_member_management' => 'Registration and management of individual members and affiliated entities',
        'purpose_license_management' => 'Issuance, renewal, and management of federative licenses',
        'purpose_certification_management' => 'Issuance and management of diving certifications and brevets',
        'purpose_event_management' => 'Organization and management of events, competitions, and training',
        'purpose_insurance_management' => 'Contracting and management of sports insurance',
        'purpose_institutional_communication' => 'Institutional communication and activity promotion',
        'purpose_legal_obligations' => 'Compliance with legal and regulatory obligations',
        'purpose_statistics' => 'Preparation of anonymized statistics',

        'legal_basis' => 'Legal Basis',
        'legal_basis_intro' => "The processing of personal data by {$federationShortName} has the following legal bases:",
        'consent' => 'Consent',
        'consent_text' => 'When the data subject gives consent for processing for one or more specific purposes (Art. 6(1)(a) GDPR)',
        'contract_execution' => 'Contract Execution',
        'contract_execution_text' => 'When processing is necessary for the performance of a contract to which the data subject is a party, such as federation membership (Art. 6(1)(b) GDPR)',
        'legal_obligation' => 'Legal Obligation',
        'legal_obligation_text' => "When processing is necessary for compliance with a legal obligation to which {$federationShortName} is subject (Art. 6(1)(c) GDPR)",
        'legitimate_interest' => 'Legitimate Interest',
        'legitimate_interest_text' => "When processing is necessary for the purposes of the legitimate interests pursued by {$federationShortName}, provided that such interests are not overridden by the interests or fundamental rights and freedoms of the data subject (Art. 6(1)(f) GDPR)",

        'data_sharing' => 'Data Sharing',
        'data_sharing_intro' => 'Personal data may be shared with the following entities, when necessary for the stated purposes:',
        'cmas' => $internationalName,
        'cmas_reason' => 'for issuance of international certifications',
        'public_sports_authority' => 'Competent public sports authority',
        'public_sports_authority_reason' => 'for compliance with legal obligations',
        'cop' => 'National Olympic or sports committee, where applicable',
        'cop_reason' => 'in the context of federative activities',
        'insurers' => 'Insurance companies',
        'insurers_reason' => 'for contracting sports insurance',
        'affiliated_entities' => 'Affiliated entities (clubs, schools, diving centers)',
        'affiliated_entities_reason' => 'for member management',
        'public_authorities' => 'Public authorities',
        'public_authorities_reason' => 'when legally required',
        'data_sharing_compliance' => "{$federationShortName} requires all entities with whom it shares data to comply with applicable data protection obligations.",

        // Public disclosure of professional members
        'public_disclosure' => 'Public Disclosure of Professional Members Data',
        'public_disclosure_intro' => "Within the scope of its duties as a sports federation and for purposes of transparency and public verification of professional qualifications, {$federationShortName} may publish on public pages of the Portal selected personal data of individual members who hold professional licenses or certifications:",
        'public_disclosure_photo' => 'Photograph',
        'public_disclosure_name' => 'Full name',
        'public_disclosure_birth_date' => 'Date of birth',
        'public_disclosure_entity' => 'Affiliated entity (club/school/diving center)',
        'public_disclosure_license_status' => 'Professional license status',
        'public_disclosure_mandatory' => 'This publication is a necessary condition for the issuance and maintenance of professional licenses, with the following legal basis:',
        'public_disclosure_contract' => 'Performance of the affiliation and professional licensing contract (Art. 6(1)(b) GDPR)',
        'public_disclosure_legal_obligation' => 'Compliance with applicable legal obligations, where relevant (Art. 6(1)(c) GDPR)',
        'public_disclosure_legitimate_interest' => "The legitimate interest of {$federationShortName} in promoting transparency and enabling public verification of professional qualifications (Art. 6(1)(f) GDPR)",
        'public_disclosure_no_removal' => 'The publication of this data is mandatory for all holders of professional licenses, and it is not possible to request its removal while the license is active.',

        'international_transfers' => 'International Transfers',
        'international_transfers_text' => "Some data may be transferred outside the European Economic Area, including to {$internationalName} for issuance of international certifications. Public deployments must configure appropriate safeguards for their operating jurisdiction.",

        'retention_period' => 'Retention Period',
        'retention_period_intro' => 'Personal data is retained for the period necessary for the purposes for which it was collected:',
        'active_member_data' => 'Active member data',
        'active_member_data_text' => 'during the membership period and for the legally required period after termination',
        'legal_obligation_data' => 'Data necessary for compliance with legal obligations',
        'legal_obligation_data_text' => 'for the legally established period',
        'financial_data' => 'Financial and tax data',
        'financial_data_text' => 'for the period required by the applicable tax and accounting law',

        'data_subject_rights' => 'Data Subject Rights',
        'data_subject_rights_intro' => 'Under the GDPR, data subjects have the following rights:',
        'right_access' => 'Right of Access',
        'right_access_text' => 'Right to obtain confirmation as to whether your data is being processed and, if so, to access it',
        'right_rectification' => 'Right to Rectification',
        'right_rectification_text' => 'Right to request the rectification of inaccurate or incomplete data',
        'right_erasure' => 'Right to Erasure',
        'right_erasure_text' => 'Right to request the erasure of data, when applicable',
        'right_portability' => 'Right to Portability',
        'right_portability_text' => 'Right to receive data in a structured and commonly used format',
        'right_objection' => 'Right to Object',
        'right_objection_text' => 'Right to object to the processing of data in certain circumstances',
        'right_restriction' => 'Right to Restriction',
        'right_restriction_text' => 'Right to request the restriction of processing in certain circumstances',
        'right_withdraw_consent' => 'Right to Withdraw Consent',
        'right_withdraw_consent_text' => 'When processing is based on consent, the data subject may withdraw it at any time',
        'exercise_rights_text' => 'To exercise any of these rights, contact us via the configured contact email or by mail to the address indicated.',

        'data_security' => 'Data Security',
        'data_security_text' => "{$federationShortName} implements appropriate technical and organizational measures to protect personal data against accidental or unlawful destruction, loss, alteration, unauthorized disclosure, or access. These measures may include data encryption, access controls, regular backups, and staff training.",

        'cookies' => 'Cookies',
        'cookies_text' => 'This Portal uses cookies to improve user experience and ensure proper functioning of services. For more information about the cookies used, please consult our Cookie Policy.',

        'complaints' => 'Complaints',
        'complaints_intro' => 'Without prejudice to any other administrative or judicial remedy, the data subject has the right to lodge a complaint with the competent supervisory authority:',
        'cnpd' => 'National Data Protection Commission (CNPD)',

        'policy_changes' => 'Policy Changes',
        'policy_changes_text' => "{$federationShortName} may modify this Privacy Policy. Changes will be published on this Portal and, when significant, communicated to data subjects by email where required.",

        'contacts_intro' => 'For any questions related to the protection of personal data, contact us:',
    ],

    // Terms of Use
    'terms' => [
        'general_provisions' => 'General Provisions',
        'general_provisions_text' => "These Terms of Use govern access to and use of {$portalName}, operated by {$federationName} ({$federationShortName}). By accessing and using this Portal, the user accepts these Terms of Use.",

        'definitions' => 'Definitions',
        'portal' => 'Portal',
        'portal_definition' => "{$federationShortName} digital platform accessible via the internet",
        'user' => 'User',
        'user_definition' => 'Any person who accesses the Portal',
        'member' => 'Member',
        'member_definition' => "Individual registered with {$federationShortName}",
        'entity_definition' => "Organization affiliated with {$federationShortName}",
        'services' => 'Services',
        'services_definition' => 'Set of functionalities made available through the Portal',

        'acceptance' => 'Acceptance of Terms',
        'acceptance_text' => "The use of this Portal implies acceptance of these Terms of Use. If you do not agree with these terms, you should refrain from using the Portal. {$federationShortName} may modify these Terms, with changes being effective after their publication on the Portal.",

        'services_description' => 'Description of Services',
        'services_description_intro' => "The {$portalName} Portal provides the following services:",
        'service_profile_management' => 'Registration and management of member and entity profiles',
        'service_license_acquisition' => 'Acquisition and renewal of federative licenses',
        'service_certification_management' => 'Management of diving certifications and brevets',
        'service_event_registration' => 'Registration for events, competitions, and training',
        'service_document_access' => 'Access and download of official documents',
        'service_payment_processing' => 'Payment processing',
        'service_insurance_management' => 'Sports insurance management',
        'service_institutional_info' => 'Consultation of institutional information',

        'user_registration' => 'User Registration',
        'user_registration_intro' => 'To access certain functionalities of the Portal, registration is required. By registering, the user commits to:',
        'registration_true_info' => 'Provide true, accurate, current, and complete information',
        'registration_keep_updated' => 'Keep their data updated',
        'registration_credentials' => 'Maintain the confidentiality of their access credentials',
        'registration_notify' => "Immediately notify {$federationShortName} in case of unauthorized use of their account",

        // Public disclosure of professional members
        'public_disclosure' => 'Public Disclosure of Professional Members Data',
        'public_disclosure_intro' => "By acquiring a professional license or certification, the user acknowledges that {$federationShortName} may publish on public pages of the Portal selected data required for public verification:",
        'public_disclosure_photo' => 'Photograph',
        'public_disclosure_name' => 'Full name',
        'public_disclosure_birth_date' => 'Date of birth',
        'public_disclosure_entity' => 'Affiliated entity',
        'public_disclosure_license_status' => 'Professional license status',
        'public_disclosure_mandatory' => 'This publication is a mandatory condition for the issuance and maintenance of professional licenses, and it is not possible to request its removal while the license is active.',
        'public_disclosure_purpose' => 'The publication is intended to enable public verification of members\' professional qualifications, contributing to safety and transparency in the underwater activities sector and federated sports.',

        'user_obligations' => 'User Obligations',
        'user_obligations_intro' => 'The user commits to:',
        'obligation_lawful_use' => 'Use the Portal in accordance with the law and these Terms',
        'obligation_true_info' => 'Provide true and updated information',
        'obligation_respect_ip' => 'Respect intellectual property rights',
        'obligation_security' => 'Not compromise the security of the Portal',
        'obligation_no_illegal' => 'Not use the Portal for illegal or harmful purposes',
        'obligation_no_harmful' => 'Not transmit illegal, defamatory, or offensive content',

        'prohibited_conduct' => 'Prohibited Conduct',
        'prohibited_conduct_intro' => 'It is expressly prohibited to:',
        'prohibited_unauthorized_access' => 'Access restricted areas without authorization',
        'prohibited_malware' => 'Introduce viruses, malware, or any malicious code',
        'prohibited_interference' => 'Interfere with the normal functioning of the Portal',
        'prohibited_bots' => 'Use robots, crawlers, or automated tools to extract data',
        'prohibited_impersonation' => 'Impersonate another person or entity',
        'prohibited_illegal_activities' => 'Use the Portal for illegal activities',

        'intellectual_property' => 'Intellectual Property',
        'intellectual_property_text' => "All deployment-specific content on the Portal, including text, graphics, logos, icons, images, audio and video clips, and data compilations, is the property of {$federationShortName} or its licensors. The software project itself is licensed according to the repository license.",
        'intellectual_property_license' => 'The user is granted a limited, non-exclusive, and non-transferable license to access and use the Portal for personal and non-commercial purposes, provided these Terms of Use are respected.',

        'payments' => 'Payments',
        'payments_intro' => 'Some services made available through the Portal are subject to payment:',
        'payments_prices' => 'Prices are those indicated on the Portal at the time of the transaction, including applicable taxes when configured',
        'payments_methods' => 'Accepted payment methods are those indicated on the Portal',
        'payments_confirmation' => 'After payment confirmation, a receipt will be issued by email',
        'payments_refunds' => 'The applicable refund policy is that indicated for each type of service',

        'liability_limitation' => 'Limitation of Liability',
        'liability_limitation_intro' => "{$federationShortName} shall not be liable for:",
        'liability_interruptions' => 'Interruptions or failures in the operation of the Portal',
        'liability_errors' => 'Errors or omissions in the content of the Portal',
        'liability_third_party' => 'Damages caused by third parties or improper use',
        'liability_force_majeure' => 'Force majeure events or acts of God',

        'warranty_exclusion' => 'Warranty Exclusion',
        'warranty_exclusion_text' => "The Portal is provided \"as is\" and \"as available\". {$federationShortName} does not guarantee that the Portal is free from errors, viruses, or other harmful components, nor that it will function uninterruptedly. To the maximum extent permitted by law, {$federationShortName} excludes all warranties, express or implied.",

        'indemnification' => 'Indemnification',
        'indemnification_text' => "The user commits to indemnify and hold harmless {$federationShortName}, its officers, employees, and representatives from any claims, damages, losses, or expenses resulting from the violation of these Terms or improper use of the Portal.",

        'third_party_links' => 'Third-Party Links',
        'third_party_links_text' => "The Portal may contain links to third-party websites. {$federationShortName} does not control these websites and is not responsible for their content or privacy practices. The inclusion of links does not imply any association, sponsorship, or endorsement.",

        'suspension_termination' => 'Suspension and Termination',
        'suspension_termination_intro' => "{$federationShortName} may suspend or terminate access of any user to the Portal, without prior notice, in the following situations:",
        'suspension_terms_violation' => 'Violation of these Terms of Use',
        'suspension_illegal_acts' => 'Practice of illegal acts',
        'suspension_harmful_conduct' => "Conduct that harms {$federationShortName} or other users",
        'suspension_user_request' => 'At the request of the user themselves',

        'terms_changes' => 'Changes to Terms',
        'terms_changes_text' => "{$federationShortName} may modify these Terms of Use. Changes will be published on the Portal and take effect immediately after publication. Continued use of the Portal after publication of changes constitutes acceptance thereof.",

        'applicable_law' => 'Applicable Law',
        'applicable_law_text' => 'These Terms of Use must be adapted by each deployment to the law and courts of its operating jurisdiction.',

        'dispute_resolution' => 'Dispute Resolution',
        'dispute_resolution_text' => 'In case of dispute, the parties commit to seeking an amicable solution before resorting to the courts. The user may resort to available alternative dispute resolution mechanisms, including the European online dispute resolution platform (https://ec.europa.eu/consumers/odr).',

        'severability' => 'Severability',
        'severability_text' => 'If any provision of these Terms is deemed invalid or unenforceable, the remaining provisions shall remain in full force and effect.',

        'contacts_intro' => 'For questions related to these Terms of Use, contact us:',
        'privacy_policy_reference' => 'For information about the processing of your personal data, please consult our Privacy Policy.',
    ],
];
