<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$federationName = $brand['name'];
$federationShortName = $brand['short_name'];
$portalName = $brand['portal_name'];
$internationalName = $internationalBrand['name'];

return [
    // Common
    'privacy_policy' => 'Política de Privacidad',
    'privacy_policy_title' => 'POLÍTICA DE PRIVACIDAD',
    'terms_of_use' => 'Términos de Uso',
    'terms_of_use_title' => 'TÉRMINOS DE USO',
    'last_update' => 'Última actualización',
    'entity' => 'Entidad',
    'address' => 'Dirección',
    'email' => 'Correo electrónico',
    'contacts' => 'Contactos',
    'federation_full_name' => "{$federationName} ({$federationShortName})",

    // Privacy Policy
    'privacy' => [
        'responsible_entity' => 'Entidad responsable',
        'responsible_entity_text' => "{$federationName} ({$federationShortName}) es la entidad responsable del tratamiento de los datos personales recopilados a través de este Portal. Las implementaciones públicas deben adaptar este texto a la ley de protección de datos aplicable y a la jurisdicción de operación.",
        'dpo' => 'Delegado de Protección de Datos',
        'dpo_department' => 'Departamento Administrativo y Financiero',

        'legal_framework' => 'Marco legal',
        'legal_framework_intro' => "El tratamiento de datos personales por parte de {$federationShortName} se rige por la siguiente legislación:",
        'gdpr_reference' => 'Reglamento (UE) 2016/679 del Parlamento Europeo y del Consejo (Reglamento General de Protección de Datos - RGPD)',
        'law_58_2019' => 'Ley nacional de aplicación correspondiente para la protección de datos, cuando proceda',
        'law_41_2004' => 'Ley aplicable de comunicaciones electrónicas y privacidad, cuando proceda',

        'collected_data' => 'Datos personales recopilados',
        'collected_data_intro' => "En el contexto de sus actividades, {$federationShortName} recopila y trata las siguientes categorías de datos personales:",

        'identification_data' => 'Datos de identificación',
        'full_name' => 'Nombre completo',
        'birth_date' => 'Fecha de nacimiento',
        'gender' => 'Sexo',
        'nationality' => 'Nacionalidad',
        'tax_number' => 'Número de Identificación Fiscal (NIF)',
        'id_document' => 'Número y tipo de documento de identidad',
        'photo' => 'Fotografía',

        'contact_data' => 'Datos de contacto',
        'full_address' => 'Dirección completa',
        'email_address' => 'Dirección de correo electrónico',
        'phone_number' => 'Número de teléfono/móvil',

        'sports_data' => 'Datos deportivos',
        'certifications_brevets' => 'Certificaciones y brevets obtenidos',
        'federative_licenses' => 'Licencias federativas',
        'entity_affiliations' => 'Afiliaciones a entidades (clubes, escuelas, centros de buceo)',
        'event_participation' => 'Participación en eventos y competiciones',
        'sports_results' => 'Resultados deportivos',

        'health_data' => 'Datos de salud (categoría especial)',
        'health_data_text' => 'A efectos de la emisión de licencias deportivas y seguros, puede ser necesario tratar datos relacionados con la aptitud médica para la práctica deportiva. Estos datos se tratan con medidas de seguridad reforzadas y únicamente con el consentimiento explícito del interesado.',

        'processing_purposes' => 'Finalidades del tratamiento',
        'processing_purposes_intro' => 'Los datos personales se tratan para las siguientes finalidades:',
        'purpose_member_management' => 'Registro y gestión de miembros individuales y entidades afiliadas',
        'purpose_license_management' => 'Emisión, renovación y gestión de licencias federativas',
        'purpose_certification_management' => 'Emisión y gestión de certificaciones y brevets de buceo',
        'purpose_event_management' => 'Organización y gestión de eventos, competiciones y formación',
        'purpose_insurance_management' => 'Contratación y gestión de seguros deportivos',
        'purpose_institutional_communication' => 'Comunicación institucional y promoción de actividades',
        'purpose_legal_obligations' => 'Cumplimiento de obligaciones legales y reglamentarias',
        'purpose_statistics' => 'Elaboración de estadísticas anonimizadas',

        'legal_basis' => 'Base legal',
        'legal_basis_intro' => "El tratamiento de datos personales por parte de {$federationShortName} tiene las siguientes bases legales:",
        'consent' => 'Consentimiento',
        'consent_text' => 'Cuando el interesado da su consentimiento para el tratamiento con una o más finalidades específicas (art. 6(1)(a) RGPD)',
        'contract_execution' => 'Ejecución de un contrato',
        'contract_execution_text' => 'Cuando el tratamiento es necesario para la ejecución de un contrato en el que el interesado es parte, como la afiliación a la federación (art. 6(1)(b) RGPD)',
        'legal_obligation' => 'Obligación legal',
        'legal_obligation_text' => "Cuando el tratamiento es necesario para el cumplimiento de una obligación legal a la que {$federationShortName} está sujeta (art. 6(1)(c) RGPD)",
        'legitimate_interest' => 'Interés legítimo',
        'legitimate_interest_text' => "Cuando el tratamiento es necesario para los fines de los intereses legítimos perseguidos por {$federationShortName}, siempre que dichos intereses no prevalezcan sobre los intereses o los derechos y libertades fundamentales del interesado (art. 6(1)(f) RGPD)",

        'data_sharing' => 'Comunicación de datos',
        'data_sharing_intro' => 'Los datos personales pueden comunicarse a las siguientes entidades, cuando sea necesario para las finalidades indicadas:',
        'cmas' => $internationalName,
        'cmas_reason' => 'para la emisión de certificaciones internacionales',
        'public_sports_authority' => 'Autoridad pública deportiva competente',
        'public_sports_authority_reason' => 'para el cumplimiento de obligaciones legales',
        'cop' => 'Comité olímpico o deportivo nacional, cuando proceda',
        'cop_reason' => 'en el contexto de las actividades federativas',
        'insurers' => 'Compañías de seguros',
        'insurers_reason' => 'para la contratación de seguros deportivos',
        'affiliated_entities' => 'Entidades afiliadas (clubes, escuelas, centros de buceo)',
        'affiliated_entities_reason' => 'para la gestión de miembros',
        'public_authorities' => 'Autoridades públicas',
        'public_authorities_reason' => 'cuando la ley lo exija',
        'data_sharing_compliance' => "{$federationShortName} exige a todas las entidades con las que comparte datos que cumplan las obligaciones de protección de datos aplicables.",

        // Public disclosure of professional members
        'public_disclosure' => 'Divulgación pública de datos de miembros profesionales',
        'public_disclosure_intro' => "En el ámbito de sus funciones como federación deportiva y a efectos de transparencia y verificación pública de las cualificaciones profesionales, {$federationShortName} podrá publicar en las páginas públicas del Portal determinados datos personales de los miembros individuales que posean licencias o certificaciones profesionales:",
        'public_disclosure_photo' => 'Fotografía',
        'public_disclosure_name' => 'Nombre completo',
        'public_disclosure_birth_date' => 'Fecha de nacimiento',
        'public_disclosure_entity' => 'Entidad afiliada (club/escuela/centro de buceo)',
        'public_disclosure_license_status' => 'Estado de la licencia profesional',
        'public_disclosure_mandatory' => 'Esta publicación es una condición necesaria para la emisión y el mantenimiento de las licencias profesionales, con la siguiente base legal:',
        'public_disclosure_contract' => 'Ejecución del contrato de afiliación y de licencia profesional (art. 6(1)(b) RGPD)',
        'public_disclosure_legal_obligation' => 'Cumplimiento de las obligaciones legales aplicables, cuando proceda (art. 6(1)(c) RGPD)',
        'public_disclosure_legitimate_interest' => "El interés legítimo de {$federationShortName} en promover la transparencia y permitir la verificación pública de las cualificaciones profesionales (art. 6(1)(f) RGPD)",
        'public_disclosure_no_removal' => 'La publicación de estos datos es obligatoria para todos los titulares de licencias profesionales, y no es posible solicitar su eliminación mientras la licencia esté activa.',

        'international_transfers' => 'Transferencias internacionales',
        'international_transfers_text' => "Algunos datos pueden transferirse fuera del Espacio Económico Europeo, incluida la transferencia a {$internationalName} para la emisión de certificaciones internacionales. Las implementaciones públicas deben configurar las salvaguardas apropiadas para su jurisdicción de operación.",

        'retention_period' => 'Período de conservación',
        'retention_period_intro' => 'Los datos personales se conservan durante el período necesario para las finalidades para las que se recopilaron:',
        'active_member_data' => 'Datos de miembros activos',
        'active_member_data_text' => 'durante el período de afiliación y durante el período legalmente exigido tras su finalización',
        'legal_obligation_data' => 'Datos necesarios para el cumplimiento de obligaciones legales',
        'legal_obligation_data_text' => 'durante el período legalmente establecido',
        'financial_data' => 'Datos financieros y fiscales',
        'financial_data_text' => 'durante el período exigido por la ley fiscal y contable aplicable',

        'data_subject_rights' => 'Derechos del interesado',
        'data_subject_rights_intro' => 'En virtud del RGPD, los interesados tienen los siguientes derechos:',
        'right_access' => 'Derecho de acceso',
        'right_access_text' => 'Derecho a obtener confirmación de si se están tratando sus datos y, en tal caso, a acceder a ellos',
        'right_rectification' => 'Derecho de rectificación',
        'right_rectification_text' => 'Derecho a solicitar la rectificación de datos inexactos o incompletos',
        'right_erasure' => 'Derecho de supresión',
        'right_erasure_text' => 'Derecho a solicitar la supresión de los datos, cuando proceda',
        'right_portability' => 'Derecho a la portabilidad',
        'right_portability_text' => 'Derecho a recibir los datos en un formato estructurado y de uso común',
        'right_objection' => 'Derecho de oposición',
        'right_objection_text' => 'Derecho a oponerse al tratamiento de los datos en determinadas circunstancias',
        'right_restriction' => 'Derecho a la limitación',
        'right_restriction_text' => 'Derecho a solicitar la limitación del tratamiento en determinadas circunstancias',
        'right_withdraw_consent' => 'Derecho a retirar el consentimiento',
        'right_withdraw_consent_text' => 'Cuando el tratamiento se basa en el consentimiento, el interesado puede retirarlo en cualquier momento',
        'exercise_rights_text' => 'Para ejercer cualquiera de estos derechos, contacte con nosotros a través del correo electrónico de contacto configurado o por correo postal a la dirección indicada.',

        'data_security' => 'Seguridad de los datos',
        'data_security_text' => "{$federationShortName} implementa medidas técnicas y organizativas apropiadas para proteger los datos personales contra la destrucción accidental o ilícita, la pérdida, la alteración, la divulgación no autorizada o el acceso. Estas medidas pueden incluir el cifrado de datos, controles de acceso, copias de seguridad periódicas y la formación del personal.",

        'cookies' => 'Cookies',
        'cookies_text' => 'Este Portal utiliza cookies para mejorar la experiencia del usuario y garantizar el correcto funcionamiento de los servicios. Para más información sobre las cookies utilizadas, consulte nuestra Política de Cookies.',

        'complaints' => 'Reclamaciones',
        'complaints_intro' => 'Sin perjuicio de cualquier otro recurso administrativo o judicial, el interesado tiene derecho a presentar una reclamación ante la autoridad de control competente:',
        'cnpd' => 'Comisión Nacional de Protección de Datos (CNPD)',

        'policy_changes' => 'Cambios en la política',
        'policy_changes_text' => "{$federationShortName} puede modificar esta Política de Privacidad. Los cambios se publicarán en este Portal y, cuando sean significativos, se comunicarán a los interesados por correo electrónico cuando así se requiera.",

        'contacts_intro' => 'Para cualquier cuestión relacionada con la protección de datos personales, contacte con nosotros:',
    ],

    // Terms of Use
    'terms' => [
        'general_provisions' => 'Disposiciones generales',
        'general_provisions_text' => "Estos Términos de Uso rigen el acceso y el uso de {$portalName}, operado por {$federationName} ({$federationShortName}). Al acceder y utilizar este Portal, el usuario acepta estos Términos de Uso.",

        'definitions' => 'Definiciones',
        'portal' => 'Portal',
        'portal_definition' => "Plataforma digital de {$federationShortName} accesible a través de internet",
        'user' => 'Usuario',
        'user_definition' => 'Cualquier persona que accede al Portal',
        'member' => 'Miembro',
        'member_definition' => "Individuo registrado en {$federationShortName}",
        'entity_definition' => "Organización afiliada a {$federationShortName}",
        'services' => 'Servicios',
        'services_definition' => 'Conjunto de funcionalidades puestas a disposición a través del Portal',

        'acceptance' => 'Aceptación de los términos',
        'acceptance_text' => "El uso de este Portal implica la aceptación de estos Términos de Uso. Si no está de acuerdo con estos términos, debe abstenerse de usar el Portal. {$federationShortName} puede modificar estos Términos, siendo los cambios efectivos tras su publicación en el Portal.",

        'services_description' => 'Descripción de los servicios',
        'services_description_intro' => "El Portal {$portalName} ofrece los siguientes servicios:",
        'service_profile_management' => 'Registro y gestión de perfiles de miembros y entidades',
        'service_license_acquisition' => 'Adquisición y renovación de licencias federativas',
        'service_certification_management' => 'Gestión de certificaciones y brevets de buceo',
        'service_event_registration' => 'Inscripción a eventos, competiciones y formación',
        'service_document_access' => 'Acceso y descarga de documentos oficiales',
        'service_payment_processing' => 'Procesamiento de pagos',
        'service_insurance_management' => 'Gestión de seguros deportivos',
        'service_institutional_info' => 'Consulta de información institucional',

        'user_registration' => 'Registro de usuario',
        'user_registration_intro' => 'Para acceder a determinadas funcionalidades del Portal, es necesario el registro. Al registrarse, el usuario se compromete a:',
        'registration_true_info' => 'Proporcionar información veraz, exacta, actual y completa',
        'registration_keep_updated' => 'Mantener sus datos actualizados',
        'registration_credentials' => 'Mantener la confidencialidad de sus credenciales de acceso',
        'registration_notify' => "Notificar inmediatamente a {$federationShortName} en caso de uso no autorizado de su cuenta",

        // Public disclosure of professional members
        'public_disclosure' => 'Divulgación pública de datos de miembros profesionales',
        'public_disclosure_intro' => "Al adquirir una licencia o certificación profesional, el usuario reconoce que {$federationShortName} puede publicar en las páginas públicas del Portal determinados datos necesarios para la verificación pública:",
        'public_disclosure_photo' => 'Fotografía',
        'public_disclosure_name' => 'Nombre completo',
        'public_disclosure_birth_date' => 'Fecha de nacimiento',
        'public_disclosure_entity' => 'Entidad afiliada',
        'public_disclosure_license_status' => 'Estado de la licencia profesional',
        'public_disclosure_mandatory' => 'Esta publicación es una condición obligatoria para la emisión y el mantenimiento de las licencias profesionales, y no es posible solicitar su eliminación mientras la licencia esté activa.',
        'public_disclosure_purpose' => 'La publicación tiene por objeto permitir la verificación pública de las cualificaciones profesionales de los miembros, contribuyendo a la seguridad y la transparencia en el sector de las actividades subacuáticas y el deporte federado.',

        'user_obligations' => 'Obligaciones del usuario',
        'user_obligations_intro' => 'El usuario se compromete a:',
        'obligation_lawful_use' => 'Usar el Portal de conformidad con la ley y estos Términos',
        'obligation_true_info' => 'Proporcionar información veraz y actualizada',
        'obligation_respect_ip' => 'Respetar los derechos de propiedad intelectual',
        'obligation_security' => 'No comprometer la seguridad del Portal',
        'obligation_no_illegal' => 'No usar el Portal con fines ilegales o dañinos',
        'obligation_no_harmful' => 'No transmitir contenidos ilegales, difamatorios u ofensivos',

        'prohibited_conduct' => 'Conductas prohibidas',
        'prohibited_conduct_intro' => 'Queda expresamente prohibido:',
        'prohibited_unauthorized_access' => 'Acceder a áreas restringidas sin autorización',
        'prohibited_malware' => 'Introducir virus, malware o cualquier código malicioso',
        'prohibited_interference' => 'Interferir con el funcionamiento normal del Portal',
        'prohibited_bots' => 'Usar robots, rastreadores o herramientas automatizadas para extraer datos',
        'prohibited_impersonation' => 'Suplantar la identidad de otra persona o entidad',
        'prohibited_illegal_activities' => 'Usar el Portal para actividades ilegales',

        'intellectual_property' => 'Propiedad intelectual',
        'intellectual_property_text' => "Todo el contenido específico de la implementación en el Portal, incluidos los textos, gráficos, logotipos, iconos, imágenes, clips de audio y vídeo y compilaciones de datos, es propiedad de {$federationShortName} o de sus licenciantes. El propio proyecto de software está licenciado según la licencia del repositorio.",
        'intellectual_property_license' => 'Se concede al usuario una licencia limitada, no exclusiva e intransferible para acceder y utilizar el Portal con fines personales y no comerciales, siempre que se respeten estos Términos de Uso.',

        'payments' => 'Pagos',
        'payments_intro' => 'Algunos servicios puestos a disposición a través del Portal están sujetos a pago:',
        'payments_prices' => 'Los precios son los indicados en el Portal en el momento de la transacción, incluidos los impuestos aplicables cuando estén configurados',
        'payments_methods' => 'Los métodos de pago aceptados son los indicados en el Portal',
        'payments_confirmation' => 'Tras la confirmación del pago, se emitirá un recibo por correo electrónico',
        'payments_refunds' => 'La política de reembolso aplicable es la indicada para cada tipo de servicio',

        'liability_limitation' => 'Limitación de responsabilidad',
        'liability_limitation_intro' => "{$federationShortName} no será responsable de:",
        'liability_interruptions' => 'Interrupciones o fallos en el funcionamiento del Portal',
        'liability_errors' => 'Errores u omisiones en el contenido del Portal',
        'liability_third_party' => 'Daños causados por terceros o por un uso indebido',
        'liability_force_majeure' => 'Casos de fuerza mayor o caso fortuito',

        'warranty_exclusion' => 'Exclusión de garantías',
        'warranty_exclusion_text' => "El Portal se proporciona \"tal cual\" y \"según disponibilidad\". {$federationShortName} no garantiza que el Portal esté libre de errores, virus u otros componentes dañinos, ni que funcione de forma ininterrumpida. En la máxima medida permitida por la ley, {$federationShortName} excluye todas las garantías, expresas o implícitas.",

        'indemnification' => 'Indemnización',
        'indemnification_text' => "El usuario se compromete a indemnizar y eximir de responsabilidad a {$federationShortName}, sus directivos, empleados y representantes de cualquier reclamación, daño, pérdida o gasto resultante de la violación de estos Términos o del uso indebido del Portal.",

        'third_party_links' => 'Enlaces a terceros',
        'third_party_links_text' => "El Portal puede contener enlaces a sitios web de terceros. {$federationShortName} no controla estos sitios web y no es responsable de su contenido ni de sus prácticas de privacidad. La inclusión de enlaces no implica ninguna asociación, patrocinio o respaldo.",

        'suspension_termination' => 'Suspensión y terminación',
        'suspension_termination_intro' => "{$federationShortName} puede suspender o cancelar el acceso de cualquier usuario al Portal, sin previo aviso, en las siguientes situaciones:",
        'suspension_terms_violation' => 'Violación de estos Términos de Uso',
        'suspension_illegal_acts' => 'Práctica de actos ilegales',
        'suspension_harmful_conduct' => "Conducta que perjudique a {$federationShortName} o a otros usuarios",
        'suspension_user_request' => 'A petición del propio usuario',

        'terms_changes' => 'Cambios en los términos',
        'terms_changes_text' => "{$federationShortName} puede modificar estos Términos de Uso. Los cambios se publicarán en el Portal y entrarán en vigor inmediatamente tras su publicación. El uso continuado del Portal tras la publicación de los cambios constituye la aceptación de los mismos.",

        'applicable_law' => 'Ley aplicable',
        'applicable_law_text' => 'Estos Términos de Uso deben ser adaptados por cada implementación a la ley y los tribunales de su jurisdicción de operación.',

        'dispute_resolution' => 'Resolución de disputas',
        'dispute_resolution_text' => 'En caso de disputa, las partes se comprometen a buscar una solución amistosa antes de recurrir a los tribunales. El usuario puede recurrir a los mecanismos alternativos de resolución de disputas disponibles, incluida la plataforma europea de resolución de litigios en línea (https://ec.europa.eu/consumers/odr).',

        'severability' => 'Divisibilidad',
        'severability_text' => 'Si alguna disposición de estos Términos se considera inválida o inaplicable, las disposiciones restantes permanecerán en pleno vigor y efecto.',

        'contacts_intro' => 'Para cuestiones relacionadas con estos Términos de Uso, contacte con nosotros:',
        'privacy_policy_reference' => 'Para información sobre el tratamiento de sus datos personales, consulte nuestra Política de Privacidad.',
    ],
];
