<?php

namespace Domain\Documents\Actions;

use App\Events\ActivateAfterPayment;
use App\Events\DocumentMarkedAsPaid;
use App\Notifications\UserAlert;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Payments\DataTransferObject\PaymentTransactionData;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @mixin \Domain\Documents\Actions\ManuallyMarkDocumentAsPaidAction
 */
class ManuallyMarkDocumentAsPaidAction
{
    public function execute(string $documentId, ?string $comment = null, bool $createMoloniInvoice = false): void
    {
        $document = Document::with('details', 'owner')
            ->where('id', $documentId)
            ->whereHas('type', function ($query) {
                $query->where('code', 'ORD');
            })
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Update the document status to 'Paid'
            $document->status_class = PaidDocumentState::class;
            $document->save();

            $paymentMethod = PaymentMethod::where('handler', 'Domain\Payments\Handlers\OfflinePaymentHandler')->firstOrFail();

            // Create a new PaymentTransaction
            $transactionData = PaymentTransactionData::fromArray([
                'document_id' => $document->id,
                'amount' => $document->total_value,
                'status' => 'success',
                'payment_data' => [],
                'comment' => $comment,
                'payment_method_id' => $paymentMethod->id,
            ]);

            Log::info('Transaction data', $transactionData->toArray());

            // Validate required fields
            if (is_null($transactionData->document_id) || is_null($transactionData->amount) || is_null($transactionData->status)) {
                throw ValidationException::withMessages([
                    'document_id' => 'The document_id field must not be null.',
                    'amount' => 'The amount field must not be null.',
                    'status' => 'The status field must not be null.',
                ]);
            }

            /** @var PaymentTransaction $transaction */
            $transaction = PaymentTransactionData::toModel($transactionData);
            $transaction->save();

            DB::commit();

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
                $generateInvoiceNumber = new GenerateDocumentInvoiceNumberAction;
                $document = $generateInvoiceNumber($document);

                if (is_null($document->invoice_number)) {
                    Log::warning('Failed to generate invoice number', ['document_id' => $document->id]);
                }
            } catch (\Exception $e) {
                Log::error('Invoice generation failed', [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $this->notifyDocumentOwner($document);
            } catch (\Exception $e) {
                Log::error('Failed to notify document owner', [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
            }

            activity('DocumentPaid')
                ->performedOn($document)
                ->event('paid')
                ->withProperties($document->toArray())
                ->log("Document {$document->number_extended} has been paid.");

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
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
