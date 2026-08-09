<?php

return [
    // Payment method names
    'method_offline' => 'Virement bancaire',
    'method_easypay' => 'Multibanco, MB WAY, ...',

    // Payment flow messages
    'offline_payment_instructions' => 'Veuillez effectuer le paiement par virement bancaire et envoyer le reçu par e-mail ou contacter les services administratifs.',
    'payment_successful' => 'Paiement effectué avec succès.',
    'payment_failed' => 'Le paiement a échoué. Veuillez réessayer.',
    'payment_pending' => 'Le paiement est en cours de traitement. Vous serez notifié une fois qu\'il sera terminé.',

    // Gateway messages
    'easypay_redirect_message' => 'Vous allez être redirigé pour finaliser votre paiement.',
    'payment_method_disabled' => 'Le mode de paiement sélectionné est actuellement désactivé.',

    // Error messages
    'invalid_payment_method' => 'Mode de paiement sélectionné invalide.',
    'payment_processing_error' => 'Une erreur s\'est produite lors du traitement de votre paiement.',
    'webhook_signature_invalid' => 'Signature de webhook invalide.',

    // Status updates
    'mark_as_paid' => 'Marquer comme payé',

    // Checkout page
    'complete_payment' => 'Finaliser le paiement',
    'document' => 'Document',
    'loading_checkout' => 'Chargement du formulaire de paiement...',
    'cancel_and_return' => 'Annuler et revenir au document',
    'powered_by_easypay' => 'Paiement sécurisé assuré par EasyPay',
    'checkout_error' => 'Échec du chargement du formulaire de paiement. Veuillez réessayer.',
    'return_to_document' => 'Revenir au document',
    'transaction_not_found' => 'Transaction introuvable ou déjà traitée.',
    'invalid_checkout_data' => 'Données de paiement invalides. Veuillez recommencer le processus de paiement.',
    'checkout_expired' => 'La session de paiement a expiré. Veuillez réessayer.',
];
