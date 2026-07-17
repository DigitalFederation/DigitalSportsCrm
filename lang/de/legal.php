<?php

$brand = config('branding.primary');
$internationalBrand = config('branding.international');
$federationName = $brand['name'];
$federationShortName = $brand['short_name'];
$portalName = $brand['portal_name'];
$internationalName = $internationalBrand['name'];

return [
    // Common
    'privacy_policy' => 'Datenschutzerklärung',
    'privacy_policy_title' => 'DATENSCHUTZERKLÄRUNG',
    'terms_of_use' => 'Nutzungsbedingungen',
    'terms_of_use_title' => 'NUTZUNGSBEDINGUNGEN',
    'last_update' => 'Letzte Aktualisierung',
    'entity' => 'Einrichtung',
    'address' => 'Anschrift',
    'email' => 'E-Mail',
    'contacts' => 'Kontakt',
    'federation_full_name' => "{$federationName} ({$federationShortName})",

    // Privacy Policy
    'privacy' => [
        'responsible_entity' => 'Verantwortliche Stelle',
        'responsible_entity_text' => "{$federationName} ({$federationShortName}) ist die für die Verarbeitung der über dieses Portal erhobenen personenbezogenen Daten verantwortliche Stelle. Öffentliche Bereitstellungen müssen diesen Text an das geltende Datenschutzrecht und die Betriebsgerichtsbarkeit anpassen.",
        'dpo' => 'Datenschutzbeauftragter',
        'dpo_department' => 'Verwaltungs- und Finanzabteilung',

        'legal_framework' => 'Rechtsrahmen',
        'legal_framework_intro' => "Die Verarbeitung personenbezogener Daten durch {$federationShortName} unterliegt den folgenden Rechtsvorschriften:",
        'gdpr_reference' => 'Verordnung (EU) 2016/679 des Europäischen Parlaments und des Rates (Datenschutz-Grundverordnung – DSGVO)',
        'law_58_2019' => 'Geltendes nationales Datenschutz-Umsetzungsgesetz, sofern einschlägig',
        'law_41_2004' => 'Geltendes Recht über elektronische Kommunikation und Datenschutz, sofern einschlägig',

        'collected_data' => 'Erhobene personenbezogene Daten',
        'collected_data_intro' => "Im Rahmen ihrer Tätigkeiten erhebt und verarbeitet {$federationShortName} die folgenden Kategorien personenbezogener Daten:",

        'identification_data' => 'Identifikationsdaten',
        'full_name' => 'Vollständiger Name',
        'birth_date' => 'Geburtsdatum',
        'gender' => 'Geschlecht',
        'nationality' => 'Staatsangehörigkeit',
        'tax_number' => 'Steueridentifikationsnummer (NIF)',
        'id_document' => 'Nummer und Art des Ausweisdokuments',
        'photo' => 'Lichtbild',

        'contact_data' => 'Kontaktdaten',
        'full_address' => 'Vollständige Anschrift',
        'email_address' => 'E-Mail-Adresse',
        'phone_number' => 'Telefon-/Mobilnummer',

        'sports_data' => 'Sportbezogene Daten',
        'certifications_brevets' => 'Erworbene Zertifizierungen und Brevets',
        'federative_licenses' => 'Verbandslizenzen',
        'entity_affiliations' => 'Mitgliedschaften bei Einrichtungen (Vereine, Schulen, Tauchzentren)',
        'event_participation' => 'Teilnahme an Veranstaltungen und Wettkämpfen',
        'sports_results' => 'Sportergebnisse',

        'health_data' => 'Gesundheitsdaten (besondere Kategorie)',
        'health_data_text' => 'Zum Zweck der Ausstellung von Sportlizenzen und Versicherungen kann es erforderlich sein, Daten zur medizinischen Tauglichkeit für die Sportausübung zu verarbeiten. Diese Daten werden mit erhöhten Sicherheitsmaßnahmen und nur mit der ausdrücklichen Einwilligung der betroffenen Person verarbeitet.',

        'processing_purposes' => 'Verarbeitungszwecke',
        'processing_purposes_intro' => 'Personenbezogene Daten werden zu den folgenden Zwecken verarbeitet:',
        'purpose_member_management' => 'Registrierung und Verwaltung von Einzelmitgliedern und angeschlossenen Einrichtungen',
        'purpose_license_management' => 'Ausstellung, Verlängerung und Verwaltung von Verbandslizenzen',
        'purpose_certification_management' => 'Ausstellung und Verwaltung von Tauchzertifizierungen und Brevets',
        'purpose_event_management' => 'Organisation und Verwaltung von Veranstaltungen, Wettkämpfen und Schulungen',
        'purpose_insurance_management' => 'Abschluss und Verwaltung von Sportversicherungen',
        'purpose_institutional_communication' => 'Institutionelle Kommunikation und Förderung der Aktivitäten',
        'purpose_legal_obligations' => 'Erfüllung gesetzlicher und behördlicher Verpflichtungen',
        'purpose_statistics' => 'Erstellung anonymisierter Statistiken',

        'legal_basis' => 'Rechtsgrundlage',
        'legal_basis_intro' => "Die Verarbeitung personenbezogener Daten durch {$federationShortName} stützt sich auf die folgenden Rechtsgrundlagen:",
        'consent' => 'Einwilligung',
        'consent_text' => 'Wenn die betroffene Person ihre Einwilligung zur Verarbeitung für einen oder mehrere bestimmte Zwecke erteilt (Art. 6 Abs. 1 lit. a DSGVO)',
        'contract_execution' => 'Vertragserfüllung',
        'contract_execution_text' => 'Wenn die Verarbeitung für die Erfüllung eines Vertrags erforderlich ist, dessen Vertragspartei die betroffene Person ist, wie etwa die Verbandsmitgliedschaft (Art. 6 Abs. 1 lit. b DSGVO)',
        'legal_obligation' => 'Rechtliche Verpflichtung',
        'legal_obligation_text' => "Wenn die Verarbeitung zur Erfüllung einer rechtlichen Verpflichtung erforderlich ist, der {$federationShortName} unterliegt (Art. 6 Abs. 1 lit. c DSGVO)",
        'legitimate_interest' => 'Berechtigtes Interesse',
        'legitimate_interest_text' => "Wenn die Verarbeitung zur Wahrung der berechtigten Interessen erforderlich ist, die {$federationShortName} verfolgt, sofern nicht die Interessen oder Grundrechte und Grundfreiheiten der betroffenen Person überwiegen (Art. 6 Abs. 1 lit. f DSGVO)",

        'data_sharing' => 'Weitergabe von Daten',
        'data_sharing_intro' => 'Personenbezogene Daten können, sofern dies für die genannten Zwecke erforderlich ist, an die folgenden Stellen weitergegeben werden:',
        'cmas' => $internationalName,
        'cmas_reason' => 'zur Ausstellung internationaler Zertifizierungen',
        'public_sports_authority' => 'Zuständige öffentliche Sportbehörde',
        'public_sports_authority_reason' => 'zur Erfüllung gesetzlicher Verpflichtungen',
        'cop' => 'Nationales Olympisches oder Sportkomitee, sofern zutreffend',
        'cop_reason' => 'im Rahmen der Verbandstätigkeiten',
        'insurers' => 'Versicherungsgesellschaften',
        'insurers_reason' => 'zum Abschluss von Sportversicherungen',
        'affiliated_entities' => 'Angeschlossene Einrichtungen (Vereine, Schulen, Tauchzentren)',
        'affiliated_entities_reason' => 'zur Mitgliederverwaltung',
        'public_authorities' => 'Behörden',
        'public_authorities_reason' => 'sofern gesetzlich vorgeschrieben',
        'data_sharing_compliance' => "{$federationShortName} verpflichtet alle Stellen, mit denen Daten geteilt werden, zur Einhaltung der geltenden Datenschutzpflichten.",

        // Public disclosure of professional members
        'public_disclosure' => 'Öffentliche Bekanntgabe von Daten professioneller Mitglieder',
        'public_disclosure_intro' => "Im Rahmen ihrer Aufgaben als Sportverband und zum Zweck der Transparenz und öffentlichen Überprüfung beruflicher Qualifikationen kann {$federationShortName} auf öffentlichen Seiten des Portals ausgewählte personenbezogene Daten von Einzelmitgliedern veröffentlichen, die berufliche Lizenzen oder Zertifizierungen besitzen:",
        'public_disclosure_photo' => 'Lichtbild',
        'public_disclosure_name' => 'Vollständiger Name',
        'public_disclosure_birth_date' => 'Geburtsdatum',
        'public_disclosure_entity' => 'Angeschlossene Einrichtung (Verein/Schule/Tauchzentrum)',
        'public_disclosure_license_status' => 'Status der Berufslizenz',
        'public_disclosure_mandatory' => 'Diese Veröffentlichung ist eine notwendige Voraussetzung für die Ausstellung und Aufrechterhaltung von Berufslizenzen und stützt sich auf folgende Rechtsgrundlage:',
        'public_disclosure_contract' => 'Erfüllung des Mitgliedschafts- und Berufslizenzvertrags (Art. 6 Abs. 1 lit. b DSGVO)',
        'public_disclosure_legal_obligation' => 'Erfüllung geltender gesetzlicher Verpflichtungen, sofern einschlägig (Art. 6 Abs. 1 lit. c DSGVO)',
        'public_disclosure_legitimate_interest' => "Das berechtigte Interesse von {$federationShortName} an der Förderung der Transparenz und der Ermöglichung der öffentlichen Überprüfung beruflicher Qualifikationen (Art. 6 Abs. 1 lit. f DSGVO)",
        'public_disclosure_no_removal' => 'Die Veröffentlichung dieser Daten ist für alle Inhaber von Berufslizenzen verpflichtend, und ihre Entfernung kann nicht beantragt werden, solange die Lizenz aktiv ist.',

        'international_transfers' => 'Internationale Übermittlungen',
        'international_transfers_text' => "Einige Daten können außerhalb des Europäischen Wirtschaftsraums übermittelt werden, unter anderem an {$internationalName} zur Ausstellung internationaler Zertifizierungen. Öffentliche Bereitstellungen müssen geeignete Garantien für ihre Betriebsgerichtsbarkeit konfigurieren.",

        'retention_period' => 'Aufbewahrungsdauer',
        'retention_period_intro' => 'Personenbezogene Daten werden für den Zeitraum aufbewahrt, der für die Zwecke, für die sie erhoben wurden, erforderlich ist:',
        'active_member_data' => 'Daten aktiver Mitglieder',
        'active_member_data_text' => 'während der Dauer der Mitgliedschaft und für den gesetzlich vorgeschriebenen Zeitraum nach deren Beendigung',
        'legal_obligation_data' => 'Zur Erfüllung gesetzlicher Verpflichtungen erforderliche Daten',
        'legal_obligation_data_text' => 'für den gesetzlich festgelegten Zeitraum',
        'financial_data' => 'Finanz- und Steuerdaten',
        'financial_data_text' => 'für den nach dem geltenden Steuer- und Rechnungslegungsrecht erforderlichen Zeitraum',

        'data_subject_rights' => 'Rechte der betroffenen Personen',
        'data_subject_rights_intro' => 'Nach der DSGVO stehen den betroffenen Personen die folgenden Rechte zu:',
        'right_access' => 'Auskunftsrecht',
        'right_access_text' => 'Recht auf Bestätigung, ob Ihre Daten verarbeitet werden, und, falls dies der Fall ist, auf Zugang zu diesen',
        'right_rectification' => 'Recht auf Berichtigung',
        'right_rectification_text' => 'Recht auf Berichtigung unrichtiger oder unvollständiger Daten',
        'right_erasure' => 'Recht auf Löschung',
        'right_erasure_text' => 'Recht auf Löschung der Daten, sofern zutreffend',
        'right_portability' => 'Recht auf Datenübertragbarkeit',
        'right_portability_text' => 'Recht, die Daten in einem strukturierten und gängigen Format zu erhalten',
        'right_objection' => 'Widerspruchsrecht',
        'right_objection_text' => 'Recht, der Verarbeitung der Daten unter bestimmten Umständen zu widersprechen',
        'right_restriction' => 'Recht auf Einschränkung',
        'right_restriction_text' => 'Recht, unter bestimmten Umständen die Einschränkung der Verarbeitung zu verlangen',
        'right_withdraw_consent' => 'Recht auf Widerruf der Einwilligung',
        'right_withdraw_consent_text' => 'Wenn die Verarbeitung auf einer Einwilligung beruht, kann die betroffene Person diese jederzeit widerrufen',
        'exercise_rights_text' => 'Um eines dieser Rechte auszuüben, kontaktieren Sie uns über die konfigurierte Kontakt-E-Mail-Adresse oder per Post an die angegebene Anschrift.',

        'data_security' => 'Datensicherheit',
        'data_security_text' => "{$federationShortName} ergreift geeignete technische und organisatorische Maßnahmen, um personenbezogene Daten vor unbeabsichtigter oder unrechtmäßiger Zerstörung, Verlust, Veränderung, unbefugter Offenlegung oder unbefugtem Zugang zu schützen. Diese Maßnahmen können Datenverschlüsselung, Zugangskontrollen, regelmäßige Datensicherungen und Mitarbeiterschulungen umfassen.",

        'cookies' => 'Cookies',
        'cookies_text' => 'Dieses Portal verwendet Cookies, um das Nutzererlebnis zu verbessern und das ordnungsgemäße Funktionieren der Dienste sicherzustellen. Weitere Informationen zu den verwendeten Cookies finden Sie in unserer Cookie-Richtlinie.',

        'complaints' => 'Beschwerden',
        'complaints_intro' => 'Unbeschadet eines anderweitigen verwaltungsrechtlichen oder gerichtlichen Rechtsbehelfs hat die betroffene Person das Recht, bei der zuständigen Aufsichtsbehörde Beschwerde einzulegen:',
        'cnpd' => 'Nationale Datenschutzkommission (CNPD)',

        'policy_changes' => 'Änderungen der Erklärung',
        'policy_changes_text' => "{$federationShortName} kann diese Datenschutzerklärung ändern. Änderungen werden auf diesem Portal veröffentlicht und, sofern erforderlich, bei wesentlichen Änderungen den betroffenen Personen per E-Mail mitgeteilt.",

        'contacts_intro' => 'Bei Fragen zum Schutz personenbezogener Daten kontaktieren Sie uns:',
    ],

    // Terms of Use
    'terms' => [
        'general_provisions' => 'Allgemeine Bestimmungen',
        'general_provisions_text' => "Diese Nutzungsbedingungen regeln den Zugang zu und die Nutzung von {$portalName}, betrieben von {$federationName} ({$federationShortName}). Durch den Zugang zu und die Nutzung dieses Portals akzeptiert der Nutzer diese Nutzungsbedingungen.",

        'definitions' => 'Begriffsbestimmungen',
        'portal' => 'Portal',
        'portal_definition' => "Über das Internet zugängliche digitale Plattform von {$federationShortName}",
        'user' => 'Nutzer',
        'user_definition' => 'Jede Person, die auf das Portal zugreift',
        'member' => 'Mitglied',
        'member_definition' => "Bei {$federationShortName} registrierte Einzelperson",
        'entity_definition' => "Bei {$federationShortName} angeschlossene Organisation",
        'services' => 'Dienste',
        'services_definition' => 'Gesamtheit der über das Portal bereitgestellten Funktionen',

        'acceptance' => 'Annahme der Bedingungen',
        'acceptance_text' => "Die Nutzung dieses Portals setzt die Annahme dieser Nutzungsbedingungen voraus. Wenn Sie mit diesen Bedingungen nicht einverstanden sind, sollten Sie von der Nutzung des Portals absehen. {$federationShortName} kann diese Bedingungen ändern, wobei Änderungen nach ihrer Veröffentlichung auf dem Portal wirksam werden.",

        'services_description' => 'Beschreibung der Dienste',
        'services_description_intro' => "Das Portal {$portalName} stellt die folgenden Dienste bereit:",
        'service_profile_management' => 'Registrierung und Verwaltung von Mitglieds- und Einrichtungsprofilen',
        'service_license_acquisition' => 'Erwerb und Verlängerung von Verbandslizenzen',
        'service_certification_management' => 'Verwaltung von Tauchzertifizierungen und Brevets',
        'service_event_registration' => 'Anmeldung zu Veranstaltungen, Wettkämpfen und Schulungen',
        'service_document_access' => 'Zugang zu und Herunterladen von offiziellen Dokumenten',
        'service_payment_processing' => 'Zahlungsabwicklung',
        'service_insurance_management' => 'Verwaltung von Sportversicherungen',
        'service_institutional_info' => 'Einsicht in institutionelle Informationen',

        'user_registration' => 'Registrierung des Nutzers',
        'user_registration_intro' => 'Um auf bestimmte Funktionen des Portals zuzugreifen, ist eine Registrierung erforderlich. Mit der Registrierung verpflichtet sich der Nutzer:',
        'registration_true_info' => 'wahre, genaue, aktuelle und vollständige Informationen bereitzustellen',
        'registration_keep_updated' => 'seine Daten aktuell zu halten',
        'registration_credentials' => 'die Vertraulichkeit seiner Zugangsdaten zu wahren',
        'registration_notify' => "{$federationShortName} unverzüglich zu benachrichtigen, falls sein Konto unbefugt genutzt wird",

        // Public disclosure of professional members
        'public_disclosure' => 'Öffentliche Bekanntgabe von Daten professioneller Mitglieder',
        'public_disclosure_intro' => "Mit dem Erwerb einer Berufslizenz oder Zertifizierung erkennt der Nutzer an, dass {$federationShortName} auf öffentlichen Seiten des Portals ausgewählte, für die öffentliche Überprüfung erforderliche Daten veröffentlichen kann:",
        'public_disclosure_photo' => 'Lichtbild',
        'public_disclosure_name' => 'Vollständiger Name',
        'public_disclosure_birth_date' => 'Geburtsdatum',
        'public_disclosure_entity' => 'Angeschlossene Einrichtung',
        'public_disclosure_license_status' => 'Status der Berufslizenz',
        'public_disclosure_mandatory' => 'Diese Veröffentlichung ist eine verpflichtende Voraussetzung für die Ausstellung und Aufrechterhaltung von Berufslizenzen, und ihre Entfernung kann nicht beantragt werden, solange die Lizenz aktiv ist.',
        'public_disclosure_purpose' => 'Die Veröffentlichung soll die öffentliche Überprüfung der beruflichen Qualifikationen der Mitglieder ermöglichen und damit zur Sicherheit und Transparenz im Bereich der Unterwasseraktivitäten und des Verbandssports beitragen.',

        'user_obligations' => 'Pflichten des Nutzers',
        'user_obligations_intro' => 'Der Nutzer verpflichtet sich:',
        'obligation_lawful_use' => 'das Portal in Übereinstimmung mit dem Gesetz und diesen Bedingungen zu nutzen',
        'obligation_true_info' => 'wahre und aktuelle Informationen bereitzustellen',
        'obligation_respect_ip' => 'geistige Eigentumsrechte zu respektieren',
        'obligation_security' => 'die Sicherheit des Portals nicht zu gefährden',
        'obligation_no_illegal' => 'das Portal nicht für rechtswidrige oder schädliche Zwecke zu nutzen',
        'obligation_no_harmful' => 'keine rechtswidrigen, verleumderischen oder anstößigen Inhalte zu übermitteln',

        'prohibited_conduct' => 'Verbotenes Verhalten',
        'prohibited_conduct_intro' => 'Ausdrücklich untersagt ist es:',
        'prohibited_unauthorized_access' => 'ohne Genehmigung auf gesperrte Bereiche zuzugreifen',
        'prohibited_malware' => 'Viren, Schadsoftware oder jeglichen schädlichen Code einzuschleusen',
        'prohibited_interference' => 'das normale Funktionieren des Portals zu beeinträchtigen',
        'prohibited_bots' => 'Roboter, Crawler oder automatisierte Werkzeuge zur Datenextraktion zu verwenden',
        'prohibited_impersonation' => 'sich als eine andere Person oder Einrichtung auszugeben',
        'prohibited_illegal_activities' => 'das Portal für rechtswidrige Aktivitäten zu nutzen',

        'intellectual_property' => 'Geistiges Eigentum',
        'intellectual_property_text' => "Alle bereitstellungsspezifischen Inhalte des Portals, einschließlich Texte, Grafiken, Logos, Symbole, Bilder, Audio- und Videoclips sowie Datenzusammenstellungen, sind Eigentum von {$federationShortName} oder seinen Lizenzgebern. Das Softwareprojekt selbst ist gemäß der Repository-Lizenz lizenziert.",
        'intellectual_property_license' => 'Dem Nutzer wird eine beschränkte, nicht ausschließliche und nicht übertragbare Lizenz zum Zugang zu und zur Nutzung des Portals für persönliche und nicht kommerzielle Zwecke gewährt, sofern diese Nutzungsbedingungen eingehalten werden.',

        'payments' => 'Zahlungen',
        'payments_intro' => 'Einige über das Portal bereitgestellte Dienste sind zahlungspflichtig:',
        'payments_prices' => 'Es gelten die zum Zeitpunkt der Transaktion auf dem Portal angegebenen Preise, einschließlich der geltenden Steuern, sofern konfiguriert',
        'payments_methods' => 'Es werden die auf dem Portal angegebenen Zahlungsmethoden akzeptiert',
        'payments_confirmation' => 'Nach Bestätigung der Zahlung wird ein Beleg per E-Mail ausgestellt',
        'payments_refunds' => 'Es gilt die für die jeweilige Art des Dienstes angegebene Rückerstattungsrichtlinie',

        'liability_limitation' => 'Haftungsbeschränkung',
        'liability_limitation_intro' => "{$federationShortName} haftet nicht für:",
        'liability_interruptions' => 'Unterbrechungen oder Störungen im Betrieb des Portals',
        'liability_errors' => 'Fehler oder Auslassungen in den Inhalten des Portals',
        'liability_third_party' => 'Schäden, die durch Dritte oder unsachgemäße Nutzung verursacht werden',
        'liability_force_majeure' => 'Ereignisse höherer Gewalt oder Zufall',

        'warranty_exclusion' => 'Gewährleistungsausschluss',
        'warranty_exclusion_text' => "Das Portal wird \"wie besehen\" und \"wie verfügbar\" bereitgestellt. {$federationShortName} garantiert nicht, dass das Portal frei von Fehlern, Viren oder anderen schädlichen Komponenten ist, noch dass es unterbrechungsfrei funktioniert. Im gesetzlich zulässigen Höchstmaß schließt {$federationShortName} alle ausdrücklichen oder stillschweigenden Gewährleistungen aus.",

        'indemnification' => 'Schadloshaltung',
        'indemnification_text' => "Der Nutzer verpflichtet sich, {$federationShortName}, seine Amtsträger, Mitarbeiter und Vertreter von jeglichen Ansprüchen, Schäden, Verlusten oder Kosten freizustellen und schadlos zu halten, die aus der Verletzung dieser Bedingungen oder der unsachgemäßen Nutzung des Portals entstehen.",

        'third_party_links' => 'Links zu Drittseiten',
        'third_party_links_text' => "Das Portal kann Links zu Websites Dritter enthalten. {$federationShortName} kontrolliert diese Websites nicht und ist nicht für deren Inhalte oder Datenschutzpraktiken verantwortlich. Die Aufnahme von Links bedeutet keine Verbindung, Förderung oder Billigung.",

        'suspension_termination' => 'Sperrung und Beendigung',
        'suspension_termination_intro' => "{$federationShortName} kann den Zugang eines Nutzers zum Portal ohne vorherige Ankündigung in den folgenden Fällen sperren oder beenden:",
        'suspension_terms_violation' => 'Verletzung dieser Nutzungsbedingungen',
        'suspension_illegal_acts' => 'Begehung rechtswidriger Handlungen',
        'suspension_harmful_conduct' => "Verhalten, das {$federationShortName} oder anderen Nutzern schadet",
        'suspension_user_request' => 'auf Wunsch des Nutzers selbst',

        'terms_changes' => 'Änderungen der Bedingungen',
        'terms_changes_text' => "{$federationShortName} kann diese Nutzungsbedingungen ändern. Änderungen werden auf dem Portal veröffentlicht und treten unmittelbar nach der Veröffentlichung in Kraft. Die fortgesetzte Nutzung des Portals nach Veröffentlichung von Änderungen stellt deren Annahme dar.",

        'applicable_law' => 'Anwendbares Recht',
        'applicable_law_text' => 'Diese Nutzungsbedingungen sind von jeder Bereitstellung an das Recht und die Gerichte ihrer Betriebsgerichtsbarkeit anzupassen.',

        'dispute_resolution' => 'Streitbeilegung',
        'dispute_resolution_text' => 'Im Streitfall verpflichten sich die Parteien, vor der Anrufung der Gerichte eine gütliche Lösung anzustreben. Der Nutzer kann verfügbare alternative Streitbeilegungsmechanismen in Anspruch nehmen, einschließlich der europäischen Plattform zur Online-Streitbeilegung (https://ec.europa.eu/consumers/odr).',

        'severability' => 'Salvatorische Klausel',
        'severability_text' => 'Sollte eine Bestimmung dieser Bedingungen für ungültig oder nicht durchsetzbar befunden werden, bleiben die übrigen Bestimmungen in vollem Umfang wirksam und in Kraft.',

        'contacts_intro' => 'Bei Fragen zu diesen Nutzungsbedingungen kontaktieren Sie uns:',
        'privacy_policy_reference' => 'Informationen zur Verarbeitung Ihrer personenbezogenen Daten finden Sie in unserer Datenschutzerklärung.',
    ],
];
