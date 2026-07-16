<?php

namespace Domain\Documents\Actions;

use App\Events\ActivateAfterPayment;
use App\Events\DocumentMarkedAsPaid;
use App\Notifications\UserAlert;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PartiallyPaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Payments\DataTransferObject\PaymentTransactionData;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterDocumentPaymentAction
{
    /**
     * Execute the action to register a payment against a document.
     *
     * @param  string  $documentId  The ID of the document.
     * @param  float  $paymentAmount  The amount being paid.
     * @param  string|null  $comment  An optional comment about the payment.
     * @param  bool  $createMoloniInvoice  Whether to create a Moloni invoice when document is fully paid.
     *
     * @throws \Exception If any errors occur during processing.
     */
    public function execute(string $documentId, float $paymentAmount, ?string $comment = null, bool $createMoloniInvoice = false): void
    {
        // Validate input data
        if ($paymentAmount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be positive.');
        }

        $document = Document::with('details', 'owner')
            ->where('id', $documentId)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $newTotalPaidAmount = $document->amount_paid + $paymentAmount;
            if ($newTotalPaidAmount > $document->total_value) {
                throw new \InvalidArgumentException("Payment amount exceeds the document's total value.");
            }

            $document->amount_paid = $newTotalPaidAmount;
            $document->status_class = $newTotalPaidAmount >= $document->total_value ? PaidDocumentState::class : PartiallyPaidDocumentState::class;
            $document->save();

            // Handle payment transaction logic - get offline payment method for manual payments
            $paymentMethod = PaymentMethod::where('handler', 'Domain\Payments\Handlers\OfflinePaymentHandler')->first();

            if (! $paymentMethod) {
                throw new \Exception('No offline payment method found. Please ensure at least one payment method with OfflinePaymentHandler exists.');
            }
            $paymentAuditDetails = [
                'registered_by' => Auth::id(), // ID of the admin registering the payment
                'registered_at' => now()->toDateTimeString(), // Current time of the manual payment registration
                'payment_method' => 'manual',
            ];
            $transactionData = new PaymentTransactionData(
                document_id: $document->id,
                amount: $paymentAmount,
                status: 'success',
                payment_data: $paymentAuditDetails,
                comment: $comment,
                payment_method_id: $paymentMethod->id,
                currency: $document->currency
            );

            /** @var PaymentTransaction $transaction */
            $transaction = PaymentTransactionData::toModel($transactionData);
            $transaction->save();

            // Commit all database changes
            DB::commit();

            // Additional logic for full payments
            if ($document->status_class === PaidDocumentState::class) {
                // Refresh models after commit
                $document->refresh();
                $transaction->refresh();

                // CRITICAL: Fire activation event FIRST before any operations that could fail
                // This ensures licenses/subscriptions are activated even if invoice generation fails
                event(new ActivateAfterPayment($document->id));

                // Dispatch event for Moloni invoice generation (if requested by admin)
                event(new DocumentMarkedAsPaid(
                    document: $document,
                    transaction: $transaction,
                    createMoloniInvoice: $createMoloniInvoice,
                    source: 'manual'
                ));

                // Non-critical operations wrapped in try-catch
                try {
                    $this->generateInvoiceNumber($document);
                } catch (\Exception $e) {
                    Log::error('Failed to generate invoice number', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't throw - invoice generation is not critical for activation
                }

                try {
                    $this->notifyDocumentOwner($document);
                } catch (\Exception $e) {
                    Log::error('Failed to notify document owner', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info('Document fully paid', ['document_id' => $document->id, 'amount' => $paymentAmount]);
            }

            // Friendly name for the status class of the document
            if ($document->status_class === PaidDocumentState::class) {
                $friendlyStatusName = 'Paid';
            } else {
                $friendlyStatusName = 'Partially Paid';
            }
            // Record activity log
            activity('DocumentPayment')
                ->performedOn($document)
                ->withProperties(['status' => $document->status_class])
                ->log("Document {$document->number_extended} has been marked as {$friendlyStatusName}.");

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            throw $exception; // Rethrow exception to handle it according to your application's error handling policies
        }
    }

    private function generateInvoiceNumber(Document $document): void
    {
        // Generate the invoice number for the document
        $generateInvoiceNumber = new GenerateDocumentInvoiceNumberAction;
        $document = $generateInvoiceNumber($document);

        logger('Invoice number generated', ['document_id' => @$document->id, 'invoice_number' => @$document->invoice_number]);

        // Verifying if the invoice number is set
        if (is_null($document->invoice_number)) {
            throw new Exception('Failed to generate an invoice number for the document.');
        }
    }

    private function notifyDocumentOwner(Document $document): void
    {
        if (empty($document->owner)) {
            Log::warning("Document with id {$document->id} has no owner.");

            return;
        }

        $users = collect();
        $url = '';

        switch (get_class($document->owner)) {
            case Individual::class:
                // Individual logic
                $users->push($document->owner->user);
                $url = route('individual.document.show', $document->id);
                break;
            case Federation::class:
                // Federation logic - handle multiple users
                $users = $document->owner->users;
                $url = route('federation.document.show', $document->id);
                break;
            case Entity::class:
                // Entity logic
                $users->push($document->owner->users()->first());
                $url = route('entity.document.show', $document->id);
                break;
        }

        // Send notification to all users
        if (! $users->isEmpty() && $url) {
            $message = __('notifications.payment_made', ['value' => money($document->total_value, $document->currency)]);
            $notification = new UserAlert($message, $url);
            $users->each(function ($user) use ($notification) {
                if ($user) {
                    $user->notify($notification);
                }
            });
        } else {
            Log::warning("Document with id {$document->id} has no associated users for notification or URL generation failed.");
        }
    }

}
