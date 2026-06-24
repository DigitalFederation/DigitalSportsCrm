<?php

namespace App\Http\Controllers\Api;

use App\Events\DocumentMarkedAsPaid;
use App\Http\Controllers\Controller;
use Domain\Documents\Actions\MarkAsPaidAction;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Payments\Models\PaymentTransaction;
use Domain\Payments\Models\WebhookLog;
use Domain\Payments\Services\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller for handling payment gateway webhook callbacks.
 *
 * This controller receives asynchronous notifications from payment providers
 * when a payment status changes. It handles:
 *
 * - Signature validation to ensure authenticity
 * - Idempotency to prevent duplicate processing
 * - Transaction status updates
 * - Document payment marking
 * - Event dispatching for downstream integrations
 *
 * @see \Domain\Payments\Gateways\EasyPayGateway
 * @see \App\Events\DocumentMarkedAsPaid
 */
class PaymentWebhookController extends Controller
{
    public function easypay(Request $request): JsonResponse
    {
        $requestId = uniqid('webhook_', true);
        $startTime = microtime(true);
        $webhookLog = null;

        try {
            Log::info('EasyPay webhook received', [
                'request_id' => $requestId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::debug('EasyPay webhook payload', [
                'request_id' => $requestId,
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'payload' => $request->getContent(),
            ]);

            $webhookLog = WebhookLog::create([
                'gateway' => 'easypay',
                'request_id' => $requestId,
                'status' => 'processing',
                'ip_address' => $request->ip(),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'payload' => json_decode($request->getContent(), true),
            ]);

            $gatewayManager = PaymentGatewayManager::createFromConfig();
            $gateway = $gatewayManager->gateway('easypay');

            if (! $gateway->validateWebhookSignature($request->headers->all(), $request->getContent())) {
                Log::warning('EasyPay webhook signature validation failed', [
                    'request_id' => $requestId,
                    'ip' => $request->ip(),
                ]);

                $this->updateWebhookLog($webhookLog, 'invalid_signature', $startTime, ['error' => 'Invalid signature'], 401);

                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $webhookData = $request->json()->all();
            $paymentResponse = $gateway->verifyPayment($webhookData);

            if ($paymentResponse->isSuccess()) {
                return $this->handleSuccessfulPayment($paymentResponse, $webhookData, $requestId, $webhookLog, $startTime);
            }

            if ($paymentResponse->isFailed()) {
                return $this->handleFailedPayment($paymentResponse, $requestId, $webhookLog, $startTime);
            }

            Log::info('EasyPay payment status update', [
                'request_id' => $requestId,
                'status' => $paymentResponse->status,
                'transaction_id' => $paymentResponse->transactionId,
            ]);

            $this->updateWebhookLog($webhookLog, 'acknowledged', $startTime, ['status' => 'acknowledged'], 200, $paymentResponse->transactionId);

            return response()->json(['status' => 'acknowledged'], 200);

        } catch (\Exception $e) {
            Log::error('EasyPay webhook processing failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($webhookLog) {
                $this->updateWebhookLog($webhookLog, 'error', $startTime, ['error' => 'Webhook processing failed'], 500, null, $e->getMessage());
            }

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    private function handleSuccessfulPayment($paymentResponse, array $webhookData, string $requestId, WebhookLog $webhookLog, float $startTime): JsonResponse
    {
        $transaction = PaymentTransaction::find($paymentResponse->transactionId);

        if (! $transaction) {
            Log::error('Transaction not found for successful payment', [
                'request_id' => $requestId,
                'transaction_id' => $paymentResponse->transactionId,
            ]);

            $this->updateWebhookLog($webhookLog, 'failed', $startTime, ['status' => 'failed', 'reason' => 'transaction_not_found'], 200);

            return response()->json(['status' => 'failed', 'reason' => 'transaction_not_found'], 200);
        }

        // Update webhook log with transaction reference
        $webhookLog->update([
            'transaction_id' => $transaction->id,
            'document_id' => $transaction->document_id,
        ]);

        // Idempotency check: If transaction is already successful, skip processing
        if ($transaction->status === 'success') {
            Log::info('Payment already processed (idempotency check)', [
                'request_id' => $requestId,
                'transaction_id' => $transaction->id,
                'document_id' => $transaction->document_id,
            ]);

            $this->updateWebhookLog($webhookLog, 'already_processed', $startTime, ['status' => 'already_processed'], 200, $transaction->id);

            return response()->json(['status' => 'already_processed'], 200);
        }

        $document = $transaction->document;

        if (! $document) {
            Log::error('Document not found for transaction', [
                'request_id' => $requestId,
                'transaction_id' => $transaction->id,
            ]);

            $this->updateWebhookLog($webhookLog, 'failed', $startTime, ['status' => 'failed', 'reason' => 'document_not_found'], 200, $transaction->id);

            return response()->json(['status' => 'failed', 'reason' => 'document_not_found'], 200);
        }

        // Check if document is already paid (another idempotency layer)
        if ($document->status_class === PaidDocumentState::class) {
            Log::info('Document already marked as paid (idempotency check)', [
                'request_id' => $requestId,
                'document_id' => $document->id,
                'transaction_id' => $transaction->id,
            ]);

            // Update transaction status if not already success
            $transaction->update(['status' => 'success']);

            $this->updateWebhookLog($webhookLog, 'already_processed', $startTime, ['status' => 'already_processed'], 200, $transaction->id);

            return response()->json(['status' => 'already_processed'], 200);
        }

        $amount = $paymentResponse->amount;

        try {
            $paymentWasProcessed = false;

            // Use database transaction with locking to prevent race conditions
            DB::transaction(function () use ($document, $transaction, $webhookData, $requestId, $amount, &$paymentWasProcessed) {
                // Lock the transaction row to prevent concurrent processing
                $lockedTransaction = PaymentTransaction::lockForUpdate()->find($transaction->id);

                // Double-check after acquiring lock
                if ($lockedTransaction->status === 'success') {
                    Log::info('Payment processed by concurrent request', [
                        'request_id' => $requestId,
                        'transaction_id' => $transaction->id,
                    ]);

                    return;
                }

                // Update transaction status first
                $lockedTransaction->update([
                    'status' => 'success',
                    'payment_data' => json_encode($webhookData),
                ]);

                // Mark document as paid using existing action
                $markAsPaidAction = new MarkAsPaidAction;
                $markAsPaidAction->execute($document->id);

                Log::info('Document marked as paid via EasyPay webhook', [
                    'request_id' => $requestId,
                    'document_id' => $document->id,
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                ]);

                $paymentWasProcessed = true;
            });

            if (! $paymentWasProcessed) {
                $this->updateWebhookLog($webhookLog, 'already_processed', $startTime, ['status' => 'already_processed'], 200, $transaction->id);

                return response()->json(['status' => 'already_processed'], 200);
            }

            // Dispatch DocumentMarkedAsPaid event AFTER the transaction commits
            // This event is the hook point for external invoice generation (Moloni)
            $document->refresh();
            $transaction->refresh();

            event(new DocumentMarkedAsPaid(
                document: $document,
                transaction: $transaction,
                createMoloniInvoice: true,
                source: 'webhook',
                webhookData: $webhookData
            ));

            Log::info('DocumentMarkedAsPaid event dispatched', [
                'request_id' => $requestId,
                'document_id' => $document->id,
                'transaction_id' => $transaction->id,
            ]);

            $this->updateWebhookLog($webhookLog, 'success', $startTime, ['status' => 'success'], 200, $transaction->id);

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Failed to process payment', [
                'request_id' => $requestId,
                'document_id' => $document->id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            $this->updateWebhookLog($webhookLog, 'error', $startTime, ['status' => 'error'], 500, $transaction->id, $e->getMessage());

            return response()->json(['status' => 'error'], 500);
        }
    }

    private function handleFailedPayment($paymentResponse, string $requestId, WebhookLog $webhookLog, float $startTime): JsonResponse
    {
        Log::info('EasyPay payment failed via webhook', [
            'request_id' => $requestId,
            'transaction_id' => $paymentResponse->transactionId,
            'error' => $paymentResponse->errorMessage,
        ]);

        $transactionId = null;

        // Update transaction status if it exists
        if ($paymentResponse->transactionId) {
            $transaction = PaymentTransaction::find($paymentResponse->transactionId);

            if ($transaction) {
                $transactionId = $transaction->id;
                $webhookLog->update([
                    'transaction_id' => $transaction->id,
                    'document_id' => $transaction->document_id,
                ]);

                if ($transaction->status !== 'failed') {
                    $transaction->update([
                        'status' => 'failed',
                        'comment' => $paymentResponse->errorMessage ?? 'Payment failed',
                    ]);
                }
            }
        }

        $this->updateWebhookLog($webhookLog, 'failed', $startTime, ['status' => 'failed'], 200, $transactionId, $paymentResponse->errorMessage);

        return response()->json(['status' => 'failed'], 200);
    }

    private function updateWebhookLog(
        WebhookLog $webhookLog,
        string $status,
        float $startTime,
        array $response,
        int $responseCode,
        ?string $transactionId = null,
        ?string $errorMessage = null
    ): void {
        $processingTime = (int) ((microtime(true) - $startTime) * 1000);

        $updateData = [
            'status' => $status,
            'response' => $response,
            'response_code' => $responseCode,
            'processing_time_ms' => $processingTime,
        ];

        if ($transactionId) {
            $updateData['transaction_id'] = $transactionId;
        }

        if ($errorMessage) {
            $updateData['error_message'] = $errorMessage;
        }

        $webhookLog->update($updateData);
    }

    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'x-easypay-signature', 'cookie'];

        return collect($headers)
            ->map(function ($value, $key) use ($sensitiveHeaders) {
                if (in_array(strtolower($key), $sensitiveHeaders)) {
                    return '[REDACTED]';
                }

                return $value;
            })
            ->toArray();
    }
}
