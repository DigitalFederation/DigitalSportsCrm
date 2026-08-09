<?php

return [
    // Payment method names
    'method_offline' => 'Banküberweisung',
    'method_easypay' => 'Multibanco, MB WAY, ...',

    // Payment flow messages
    'offline_payment_instructions' => 'Bitte leisten Sie die Zahlung per Banküberweisung und senden Sie den Beleg per E-Mail oder kontaktieren Sie die Verwaltung.',
    'payment_successful' => 'Zahlung erfolgreich abgeschlossen.',
    'payment_failed' => 'Zahlung fehlgeschlagen. Bitte versuchen Sie es erneut.',
    'payment_pending' => 'Die Zahlung wird verarbeitet. Sie werden benachrichtigt, sobald sie abgeschlossen ist.',

    // Gateway messages
    'easypay_redirect_message' => 'Sie werden weitergeleitet, um Ihre Zahlung abzuschließen.',
    'payment_method_disabled' => 'Die ausgewählte Zahlungsmethode ist derzeit deaktiviert.',

    // Error messages
    'invalid_payment_method' => 'Ungültige Zahlungsmethode ausgewählt.',
    'payment_processing_error' => 'Bei der Verarbeitung Ihrer Zahlung ist ein Fehler aufgetreten.',
    'webhook_signature_invalid' => 'Ungültige Webhook-Signatur.',

    // Status updates
    'mark_as_paid' => 'Als bezahlt markieren',

    // Checkout page
    'complete_payment' => 'Zahlung abschließen',
    'document' => 'Dokument',
    'loading_checkout' => 'Zahlungsformular wird geladen...',
    'cancel_and_return' => 'Abbrechen und zum Dokument zurückkehren',
    'powered_by_easypay' => 'Sichere Zahlung mit EasyPay',
    'checkout_error' => 'Zahlungsformular konnte nicht geladen werden. Bitte versuchen Sie es erneut.',
    'return_to_document' => 'Zum Dokument zurückkehren',
    'transaction_not_found' => 'Transaktion nicht gefunden oder bereits verarbeitet.',
    'invalid_checkout_data' => 'Ungültige Checkout-Daten. Bitte starten Sie den Zahlungsvorgang erneut.',
    'checkout_expired' => 'Die Checkout-Sitzung ist abgelaufen. Bitte versuchen Sie es erneut.',
];
