<?php

return [
    // Subscription Creation
    'subscription_created_successfully' => 'Abonnement erfolgreich erstellt. Bitte fahren Sie mit der Zahlung fort.',
    'subscription_created_pending_payment' => 'Abonnement erfolgreich erstellt. Bitte fahren Sie mit der Zahlung fort.',
    'insurance_subscription_created_pending_payment' => 'Versicherungsabonnement erfolgreich erstellt! Bitte schließen Sie die Zahlung ab, um Ihren Versicherungsschutz zu aktivieren.',
    'subscription_created_free' => 'Abonnement erfolgreich erstellt.',
    'subscription_creation_error' => 'Bei der Verarbeitung Ihres Abonnements ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
    'subscription_already_pending' => 'Sie haben bereits ein ausstehendes Abonnement für dieses Paket.',
    'subscription_already_pending_payment' => 'Sie haben bereits ein ausstehendes Abonnement für dieses Paket. Bitte schließen Sie die Zahlung ab, um es zu aktivieren.',

    // Document Generation
    'affiliation_description' => 'Mitgliedschaft: :name - :federation',
    'insurance_description' => 'Versicherung: :name',
    'subscription_document_notes' => 'Abonnement für Paket: :package',
    'bulk_subscription_document_note' => 'Sammelabonnement für :count Mitglieder - Paket: :package',

    // Document Observer
    'activating_subscription_after_payment' => 'Mitgliederabonnement wird nach der Zahlung aktiviert',
    'subscription_activated' => 'Mitgliederabonnement aktiviert',

    // Payment Flow
    'payment_required' => 'Zahlung erforderlich, um das Abonnement abzuschließen',
    'proceed_to_payment' => 'Bitte fahren Sie mit der Zahlung fort, um Ihr Abonnement zu aktivieren',

    // Validation Messages
    'package_selection_required' => 'Die Auswahl eines Mitgliedschaftspakets ist erforderlich.',
    'package_selection_invalid' => 'Das ausgewählte Mitgliedschaftspaket ist ungültig.',
    'invalid_member_type' => 'Ungültiger Mitgliedertyp für das Abonnement.',
    'no_validation_affiliation_for_insurance' => 'Für den Abschluss reiner Versicherungspakete ist eine aktive Validierungsmitgliedschaft erforderlich.',
    'no_active_affiliation_found' => 'Keine aktive Mitgliedschaft gefunden. Eine Validierungsmitgliedschaft ist erforderlich.',
    'duplicate_affiliation_plans' => 'Sie haben bereits ein aktives Abonnement für die folgenden Mitgliedschaftspläne: :plans',
    'all_affiliation_plans_already_active' => 'Sie haben bereits ein aktives Abonnement für alle Mitgliedschaftspläne in diesem Paket: :plans',
    'duplicate_insurance_plans' => 'Sie haben bereits ein aktives oder ausstehendes Abonnement für die folgenden Versicherungspläne: :plans',
    'insufficient_privileges_for_request_type' => 'Unzureichende Berechtigungen für diese Art von Anfrage.',
    'validation_plan_required_for_non_validation_packages' => 'Die Einzelperson muss über einen aktiven Validierungsplan verfügen, um dieses Mitgliedschaftspaket zu abonnieren.',

    // Renewal
    'subscription_renewed_successfully' => 'Mitgliedschaftsabonnement erfolgreich verlängert.',

    // Individual Profile Messages
    'complete_profile_before_managing_subscriptions' => 'Bitte vervollständigen Sie Ihr persönliches Profil, bevor Sie Abonnements verwalten.',

    // Affiliation Plan Business Scenarios
    'business_scenarios' => [
        'direct_individual' => [
            'label' => 'Direktes Einzelabonnement',
            'description' => 'Einzelpersonen abonnieren diesen Plan selbst direkt',
            'example' => 'Beispiel: Persönliche Jahresmitgliedschaft, Studententarife',
        ],
        'entity_for_individuals' => [
            'label' => 'Einrichtung abonniert für Einzelpersonen',
            'description' => 'Einrichtungen (Vereine, Schulen) abonnieren diesen Plan FÜR ihre Einzelmitglieder',
            'example' => 'Beispiel: Verein zahlt für Sportlermitgliedschaften, Tauchzentrum zahlt für Schülerzertifizierungen',
        ],
        'direct_entity' => [
            'label' => 'Direktes Einrichtungsabonnement',
            'description' => 'Einrichtungen abonnieren diesen Plan für sich selbst (institutionelle Mitgliedschaft)',
            'example' => 'Beispiel: Institutionelle Vereinsmitgliedschaft, Tauchzentrumszertifizierung',
        ],
        'flexible' => [
            'label' => 'Flexibler Plan',
            'description' => 'Kann sowohl von Einzelpersonen als auch von Einrichtungen mit unterschiedlicher Preisgestaltung genutzt werden',
            'example' => 'Beispiel: Premium-Plan mit individuellen und institutionellen Tarifen',
        ],
    ],

    // Form Labels
    'choose_business_scenario' => 'Geschäftsszenario auswählen',
    'business_scenario_help' => 'Wählen Sie aus, welche Art von Abonnementplan Sie erstellen möchten. Dies bestimmt, wer abonnieren kann und wie die Preisgestaltung funktioniert.',
    'plan_name' => 'Planname',
    'plan_name_help' => 'Wählen Sie einen klaren, aussagekräftigen Namen',
    'select_federation' => 'Verband auswählen...',
    'pricing' => 'Preisgestaltung',
    'fee_individual_member' => 'Pro Einzelmitglied berechnete Gebühr',
    'fee_individual_subscription' => 'Gebühr bei Abonnement durch Einzelpersonen',
    'fee_entity_institution' => 'Der Einrichtung (Institution) berechnete Gebühr',
    'fee_entity_subscription' => 'Gebühr bei Abonnement durch Einrichtungen',
    'free_plan_option' => 'Dies ist ein kostenloser Plan (Gebühren auf 0 € setzen)',
    'immediate_availability' => 'Für sofortige Verfügbarkeit leer lassen',
    'no_expiration' => 'Für keinen Ablauf leer lassen',
    'description_help' => 'Geben Sie ausführliche Informationen darüber an, was dieser Plan umfasst, Voraussetzungen, Vorteile usw.',
    'pdf_documents' => 'PDF-Dokumente',
    'upload_documents_help' => 'Laden Sie Bedingungen, Konditionen oder andere relevante Dokumente hoch. Jeweils max. 10 MB.',
    'current_attachments' => 'Aktuelle Anhänge',
    'uncheck_remove_files' => 'Deaktivieren, um Dateien zu entfernen',
    'plan_summary' => 'Planübersicht',
    'usage' => 'Verwendung',
    'create_plan_help' => 'Erstellen Sie einen neuen Mitgliedschaftsplan, indem Sie das Geschäftsszenario auswählen, das am besten beschreibt, wie dieser Plan funktionieren soll. Das Formular führt Sie durch die entsprechenden Einstellungen.',
    'edit_plan_help' => 'Bearbeiten Sie die Details dieses Mitgliedschaftsplans. Das Geschäftsszenario bestimmt die Planstruktur und die Preisoptionen.',
    'complete_profile_before_selecting_subscription' => 'Bitte vervollständigen Sie Ihr persönliches Profil, bevor Sie ein Abonnement auswählen.',
    'complete_profile_before_purchasing_subscription' => 'Bitte vervollständigen Sie Ihr persönliches Profil, bevor Sie ein Abonnement erwerben.',
    'complete_profile_before_viewing_history' => 'Bitte vervollständigen Sie Ihr persönliches Profil, bevor Sie den Abonnementverlauf einsehen.',
    'please_login_to_continue' => 'Bitte melden Sie sich an, um fortzufahren.',
    'profile_issue_contact_support' => 'Es gab ein Problem mit Ihrem Profil. Bitte wenden Sie sich an den Support.',
    'subscription_not_eligible_for_renewal' => 'Dieses Abonnement kommt nicht für eine Verlängerung in Frage.',
    'renewal_error_try_again' => 'Bei der Verlängerung Ihres Abonnements ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
    'duplicate_affiliation_plans_error' => 'Sie haben bereits ein aktives Abonnement für einen oder mehrere Mitgliedschaftspläne in diesem Paket.',

    // Official Document Requirements
    'missing_official_documents' => 'Sie können dieses Paket nicht abonnieren, da es offizielle Dokumente erfordert, die Sie nicht hochgeladen haben oder die nicht aktiv sind.',
    'insurance_requires_document' => 'Erforderlich: :document für :insurance.',

    // Validation Plan
    'validation_plan' => 'Validierungsplan',
    'validation_plan_help' => 'Erweiterte Berechtigungen für Abonnenten dieses Plans aktivieren',
    'validation_plan_enables' => 'Validierungspläne ermöglichen',
    'insurance_requests' => 'Versicherungspolicen anfordern',
    'license_requests' => 'Lizenzen und Zertifizierungen anfordern',
    'entity_member_licenses' => 'Für Einrichtungen: Lizenzen für ihre Mitglieder anfordern',

    // Validation Plan Error Messages
    'insurance_subscription_not_authorized' => 'Versicherungsabonnement nicht autorisiert: :reason',
    'license_request_not_authorized' => 'Lizenzanfrage nicht autorisiert: :reason',
    'entity_member_insurance_not_authorized' => 'Zuweisung der Versicherung für Einrichtungsmitglied nicht autorisiert: :reason',
    'entity_member_license_not_authorized' => 'Lizenzanfrage für Einrichtungsmitglied nicht autorisiert: :reason',

    // Validation Plan Privilege Messages
    'validation_plan_no_insurance_privileges' => 'Ihr aktueller Mitgliedschaftsplan umfasst keine Berechtigungen zur Anforderung von Versicherungen',
    'validation_plan_no_license_privileges' => 'Ihr aktueller Mitgliedschaftsplan umfasst keine Berechtigungen zur Anforderung von Lizenzen',
    'validation_plan_no_entity_member_licenses' => 'Ihr aktueller Mitgliedschaftsplan erlaubt es nicht, Lizenzen für Einrichtungsmitglieder anzufordern',
    'validation_plan_no_entity_member_subscriptions' => 'Ihr aktueller Mitgliedschaftsplan erlaubt es nicht, Mitglieder für Pakete zu abonnieren',

    // Validation Plan UI Messages
    'validation_plan_required' => 'Validierungsplan erforderlich',
    'access_restricted' => 'Zugang eingeschränkt',
    'contact_federation_validation_plan' => 'Bitte wenden Sie sich an Ihren Verband, um Ihren Validierungsplan zu erweitern und die Funktionen für Mitgliederabonnements freizuschalten.',
    'validation_plan_required_message' => 'Ein Validierungsplan ist erforderlich, um Mitglieder für Pakete zu abonnieren.',
    'no_active_affiliation_found' => 'Keine aktive Mitgliedschaft gefunden',
    'entity_member_subscriptions_not_authorized' => 'Sie können keine Mitglieder für Pakete abonnieren. :reason',
    'invalid_member_type' => 'Ungültiger Mitgliedertyp',
    'insufficient_privileges_for_request_type' => 'Unzureichende Berechtigungen für diese Art von Anfrage',

    // Subscription page
    'affiliations' => 'Mitgliedschaften',
    'active_affiliations' => 'Aktive Mitgliedschaften',
    'included_plans' => 'Enthaltene Pläne',
    'affiliation_plans' => 'Mitgliedschaftspläne',

    // Member subscriptions
    'member_subscriptions' => [
        'created_successfully' => 'Mitgliederabonnement erfolgreich erstellt.',
        'renewed_successfully' => 'Mitgliederabonnement erfolgreich verlängert.',
        'delete' => 'Löschen',
        'deleted_successfully' => 'Mitgliederabonnement erfolgreich gelöscht.',
        'delete_failed' => 'Löschen des Mitgliederabonnements fehlgeschlagen. Bitte versuchen Sie es erneut.',
        'confirm_delete_title' => 'Mitgliederabonnement löschen',
        'confirm_delete_warning' => 'Diese Aktion löscht das Mitgliederabonnement sowie alle zugehörigen Mitgliedschaften und Versicherungen dauerhaft. Diese Aktion kann nicht rückgängig gemacht werden.',
        'will_delete_related' => 'Dadurch werden :affiliations Mitgliedschaft(en) und :insurances Versicherung(en) gelöscht',
        'delete_confirm' => 'Abonnement löschen',
        'change_status' => 'Status ändern',
        'change_status_title' => 'Abonnementstatus ändern',
        'change_status_warning' => 'Dadurch wird nur der Abonnementstatus geändert. Zahlungsdokumente, Mitgliedschaften und Versicherungen sind NICHT betroffen.',
        'new_status' => 'Neuer Status',
        'update_status' => 'Status aktualisieren',
        'status_updated_successfully' => 'Status des Mitgliederabonnements erfolgreich aktualisiert.',
        'status_update_failed' => 'Aktualisierung des Status des Mitgliederabonnements fehlgeschlagen.',
        'pending_payment' => 'Zahlung ausstehend',
    ],

    // Notifications
    'subscription_activated_notification' => 'Ihr Abonnement für :package wurde aktiviert und ist gültig bis :date.',

    // Membership states
    'states' => [
        'active' => 'Aktiv',
        'pending' => 'Ausstehend',
        'expired' => 'Abgelaufen',
        'canceled' => 'Storniert',
    ],

    // Member subscription states
    'subscription_states' => [
        'active' => 'Aktiv',
        'pending' => 'Ausstehend',
        'pending_payment' => 'Zahlung ausstehend',
        'expired' => 'Abgelaufen',
    ],

    // Table headers
    'title' => 'Mitgliedschaften',
    'name' => 'Name',
    'plans' => 'Pläne',
    'status' => 'Status',
    'expiration_date' => 'Ablaufdatum',
    'organizations_membership_association' => 'Zuordnung der Organisationsmitgliedschaft',
];
