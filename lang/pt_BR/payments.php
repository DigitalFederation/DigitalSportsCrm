<?php

return [
    // Payment method names
    'method_offline' => 'Transferência Bancária',
    'method_easypay' => 'Multibanco, MB WAY, ...',

    // Payment flow messages
    'offline_payment_instructions' => 'Por favor realize o pagamento por transferência bancária e envie o comprovativo por email ou entre em contato com os serviços administrativos.',
    'payment_successful' => 'Pagamento realizado com sucesso.',
    'payment_failed' => 'Pagamento falhado. Por favor, tente novamente.',
    'payment_pending' => 'Pagamento a ser processado. Será notificado quando for concluído.',

    // Gateway messages
    'easypay_redirect_message' => 'Será redirecionado para completar o pagamento.',
    'payment_method_disabled' => 'O método de pagamento selecionado está desativado.',

    // Error messages
    'invalid_payment_method' => 'Método de pagamento inválido.',
    'payment_processing_error' => 'Ocorreu um erro ao processar o pagamento.',
    'webhook_signature_invalid' => 'Assinatura do webhook inválida.',

    // Status updates
    'mark_as_paid' => 'Marcar como Pago',

    // Checkout page
    'complete_payment' => 'Completar Pagamento',
    'document' => 'Documento',
    'loading_checkout' => 'A carregar formulário de pagamento...',
    'cancel_and_return' => 'Cancelar e voltar ao documento',
    'powered_by_easypay' => 'Pagamento seguro via EasyPay',
    'checkout_error' => 'Não foi possível carregar o formulário de pagamento. Por favor, tente novamente.',
    'return_to_document' => 'Voltar ao documento',
    'transaction_not_found' => 'Transação não encontrada ou já processada.',
    'invalid_checkout_data' => 'Dados de checkout inválidos. Por favor, reinicie o processo de pagamento.',
    'checkout_expired' => 'A sessão de checkout expirou. Por favor, tente novamente.',
];
