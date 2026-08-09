<?php

$primaryName = config('branding.primary.name', 'Example Federation');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'payment_documents' => 'Zahlungsdokumente',
    'payment_documents_disclaimer' => 'Diese Dokumente dienen nur zu Informationszwecken und haben keine rechtliche Gültigkeit. Für jedes Dokument muss eine Rechnung/Quittung in einem zertifizierten Buchhaltungsprogramm ausgestellt werden.',
    'invoices' => 'Rechnungen',
    'create_manual_order' => 'Manuelle Bestellung erstellen',
    'latest_documents' => 'Neueste Dokumente',
    'filtered_results' => 'Gefilterte Ergebnisse',
    'entities' => 'Organisationen',
    'member' => 'Mitglied',

    // Table headers
    'number' => '# Nummer',
    'type' => 'Typ',
    'document_name' => 'Dokumentname',
    'status' => 'Status',
    'issue_date' => 'Ausstellungsdatum',
    'expiration_date' => 'Ablaufdatum',
    'total' => 'Gesamt',
    'id' => 'ID',
    'download' => 'Herunterladen',
    'category' => 'Kategorie',

    // Document detail page
    'document_detail' => 'Dokumentdetails',
    'payment' => 'Zahlung',
    'select_method' => 'Wählen Sie eine Methode',
    'proceed_to_payment' => 'Zur Zahlung fortfahren',

    // Document info labels
    'number_label' => 'Nummer',
    'type_label' => 'Typ',
    'date_label' => 'Datum',
    'recipient' => 'Empfänger',
    'vat_number' => 'USt-IdNr.',
    'city' => 'Stadt',
    'address' => 'Adresse',
    'postal_code' => 'Postleitzahl',
    'country' => 'Land',

    // Table columns
    'product' => 'Produkt',
    'qty' => 'Menge',
    'unit_price' => 'Stückpreis',
    'amount' => 'Betrag',
    'subtotal' => 'Zwischensumme',
    'amount_paid' => 'Gezahlter Betrag',
    'remaining_balance' => 'Restbetrag',

    // Payment status
    'document_is_paid' => 'Dieses Dokument ist bereits bezahlt',
    'find_details_below' => 'Details finden Sie unten',
    'view_moloni_invoice' => 'Rechnung/Quittung anzeigen',
    'document_type' => 'Dokumenttyp',
    'created_at' => 'Erstellt am',
    'transactions' => 'Transaktionen',
    'transaction_status' => 'Status',
    'transaction_date' => 'Datum',
    'transaction_info' => 'Info',
    'associated_documents' => 'Zugehörige Dokumente',

    // Filters
    'year' => 'Jahr',
    'document_number' => 'Dokumentnummer',
    'filter_cmas_code_help' => 'Suche nach dem internationalen Code des Inhabers',
    'filter_member_placeholder' => 'Mitgliedsname',
    'organization' => 'Organisation',
    'national_organization' => 'Nationale Organisation',
    'date_from' => 'Datum von',
    'date_to' => 'Datum bis',
    'payment_date' => 'Zahlungsdatum',

    // Index page filters
    'filters' => [
        'category' => 'Kategorie',
        'status' => 'Status',
        'type' => 'Typ',
    ],

    // Index page table
    'table' => [
        'number' => '# Nummer',
        'date' => 'Datum',
        'type' => 'Typ',
        'status' => 'Status',
        'total' => 'Gesamt',
    ],

    // Document manual create
    'attention' => 'Achtung',
    'document_no' => 'Dokument-Nr.',
    'due_date' => 'Fälligkeitsdatum',
    'federation' => 'Verband',
    'entity' => 'Organisation',
    'individual' => 'Einzelperson',
    'manual_entry' => 'Manuelle Eingabe',
    'select_federation' => 'Verband auswählen',
    'select_federation_option' => '-- Verband auswählen --',
    'select_entity' => 'Organisation auswählen',
    'select_entity_option' => '-- Organisation auswählen --',
    'search_individual' => 'Einzelperson suchen',
    'search_individual_placeholder' => 'Mitgliedsnummer, Name oder E-Mail eingeben',
    'active_member' => 'Aktives Mitglied',
    'birth_date' => 'Geburtsdatum',
    'manual_customer_entry' => 'Manuelle Kundeneingabe',
    'customer_name' => 'Kundenname',
    'document_state' => 'Dokumentstatus',
    'description' => 'Beschreibung',
    'delete' => 'Löschen',
    'add_invoice_items' => 'Rechnungspositionen hinzufügen',
    'document_line' => 'Dokumentzeile',
    'products' => 'Produkte',
    'select_product' => '-- Produkt auswählen --',
    'or' => 'ODER',
    'product_service' => 'Produkt/Dienstleistung',
    'vat_percentage' => 'MwSt. %',
    'add_item' => 'Position hinzufügen',
    'notes' => 'Anmerkungen',
    'save_document' => 'Dokument speichern',

    // Moloni invoice
    'create_moloni_invoice' => 'Moloni-Rechnung erstellen',
    'create_moloni_invoice_description' => 'Aktivieren Sie diese Option, um automatisch eine Rechnung in Moloni für diese Zahlung zu erstellen.',

    // Owner type categories (for document filters)
    'categories' => [
        'License' => 'Lizenz',
        'Membership' => 'Abonnement',
        'Document' => 'Dokument',
        'Certification' => 'Zertifizierung',
        'Registration' => 'Anmeldung',
        'Manual Order' => 'Manuelle Bestellung',
        'Insurance' => 'Versicherung',
    ],

    // Document states
    'states' => [
        'paid' => 'Bezahlt',
        'draft' => 'Entwurf',
        'pending' => 'Ausstehend',
        'canceled' => 'Storniert',
        'partially_paid' => 'Teilweise bezahlt',
        'void' => 'Ungültig',
    ],

    // Action messages
    'edit_draft_only' => 'Die Bearbeitung ist nur für Dokumente im Entwurfsstatus erlaubt.',
    'notification_sent' => 'Benachrichtigung gesendet.',
    'document_canceled_successfully' => 'Dokument erfolgreich storniert.',
    'not_cancellable_state' => 'Das Dokument befindet sich nicht in einem stornierbaren Status.',
    'has_associated_payments' => 'Das Dokument kann nicht gelöscht werden, da zugehörige Zahlungen vorhanden sind.',
    'no_invoices_found' => 'Keine Rechnungen gefunden, die den angegebenen Kriterien entsprechen.',
    'export_failed' => 'Export konnte nicht erstellt werden. Bitte versuchen Sie es erneut oder kontaktieren Sie den Support.',

    // Confirmations
    'confirm_delete_warning' => 'Sind Sie sicher, dass Sie dieses Dokument löschen möchten? Diese Aktion ist unwiderruflich und löscht alle zugehörigen Daten.',
    'confirm_cancel_warning' => 'Sind Sie sicher, dass Sie dieses Dokument stornieren möchten?',
    'document_deleted_successfully' => 'Dokument erfolgreich gelöscht.',

    // Buttons
    'resend_notification' => 'Benachrichtigung erneut senden',
    'delete_document' => 'Dokument löschen',

    // Filter labels
    'document_period' => 'Dokumentzeitraum',

    // Invoice/Order PDF labels
    'pdf' => [
        'name' => 'Name',
        'city' => 'Stadt',
        'address' => 'Adresse',
        'date' => 'Datum',
        'vat_number' => 'USt-IdNr.',
        'postal_code' => 'Postleitzahl',
        'member_number' => 'Mitglieds-Nr.',
        'country' => 'Land',
        'notes' => 'Anmerkungen',
        'description' => 'BESCHREIBUNG',
        'qty' => 'MENGE',
        'unit_price' => 'STÜCKPREIS',
        'total' => 'GESAMT',
        'subtotal' => 'Zwischensumme',
        'tax' => 'Steuer',
        'order_disclaimer' => 'Dieses Dokument stellt weder eine Rechnung noch eine Quittung dar. Das gültige Steuerdokument wird nach Zahlungsbestätigung über ein zertifiziertes Fakturierungsprogramm gemäß der geltenden Gesetzgebung ausgestellt.',
    ],

    // Invoice PDF compliance text
    'invoice_compliance_en' => "Entities and individuals hereby undertake to comply with and strictly enforce {$primaryShortName} rules, as well as to urge their members to adopt an underwater environmental friendly attitude.",
    'invoice_compliance_pt' => "As entidades e individuos comprometem-se por este documento a aplicar e fazer aplicar rigorosamente as regras de {$primaryShortName} e a incentivar os seus membros a adotar uma atitude respeitosa pelo ambiente subaquatico.",
];
