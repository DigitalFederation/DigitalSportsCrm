<?php

return [
    // Payment method names
    'method_offline' => 'Transferencia bancaria',
    'method_easypay' => 'Multibanco, MB WAY, ...',

    // Payment flow messages
    'offline_payment_instructions' => 'Realiza el pago mediante transferencia bancaria y envía el comprobante por correo electrónico o ponte en contacto con los servicios administrativos.',
    'payment_successful' => 'Pago completado correctamente.',
    'payment_failed' => 'El pago falló. Inténtalo de nuevo.',
    'payment_pending' => 'El pago se está procesando. Se te notificará cuando se complete.',

    // Gateway messages
    'easypay_redirect_message' => 'Serás redirigido para completar tu pago.',
    'payment_method_disabled' => 'El método de pago seleccionado está deshabilitado actualmente.',

    // Error messages
    'invalid_payment_method' => 'El método de pago seleccionado no es válido.',
    'payment_processing_error' => 'Se produjo un error al procesar tu pago.',
    'webhook_signature_invalid' => 'Firma de webhook no válida.',

    // Status updates
    'mark_as_paid' => 'Marcar como pagado',

    // Checkout page
    'complete_payment' => 'Completar pago',
    'document' => 'Documento',
    'loading_checkout' => 'Cargando el formulario de pago...',
    'cancel_and_return' => 'Cancelar y volver al documento',
    'powered_by_easypay' => 'Pago seguro con tecnología de EasyPay',
    'checkout_error' => 'No se pudo cargar el formulario de pago. Inténtalo de nuevo.',
    'return_to_document' => 'Volver al documento',
    'transaction_not_found' => 'Transacción no encontrada o ya procesada.',
    'invalid_checkout_data' => 'Datos de pago no válidos. Inicia de nuevo el proceso de pago.',
    'checkout_expired' => 'La sesión de pago ha caducado. Inténtalo de nuevo.',
];
