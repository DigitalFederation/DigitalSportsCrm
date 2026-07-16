<?php

namespace Domain\Documents\Actions;

use App\Events\ActivateAfterPayment;
use App\Notifications\UserAlert;
use Domain\Documents\DataTransferObject\DocumentData;
use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @mixin \Domain\Documents\Actions\MarkAsPaidAction
 */
class MarkAsPaidAction
{
    /**
     * Marks the given document as paid.
     * Only documents of type Invoice can be paid
     *
     * @throws Exception
     */
    public function execute(string $documentId, ?string $reason = null): Document
    {
        // Fetch the original Document object. Only Invoice documents can be paid.
        $document = Document::with('details')
            ->where('id', $documentId)
            ->whereHas('type', function ($query) {
                $query->where('code', 'INV')->orWhere('code', 'ORD');
            })
            ->firstOrFail();

        try {
            DB::beginTransaction();

            /* creating a new payment document with a single line item that is a debit for the
            total value of the original document. */
            $payment = $this->createPaymentDocumentWithDetail($document, $reason);

            /* changing the state of the document to "paid". */
            $this->changeDocumentToPaidState($document);

            // First commit. Since if there's an error the event is not triggered
            DB::commit();

            // CRITICAL: Event to activate the document models after payment
            // This fires FIRST before any non-critical operations
            event(new ActivateAfterPayment($document->id));

            // Non-critical operations wrapped in try-catch
            try {
                $generateInvoiceNumber = new GenerateDocumentInvoiceNumberAction;
                $document = $generateInvoiceNumber($document);
            } catch (\Exception $e) {
                Log::error('Failed to generate invoice number', [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                if ($document->owner) {
                    // Check if owner is an Entity (has users() method) or Individual (has user() method)
                    if (method_exists($document->owner, 'users')) {
                        // Entity - has multiple users
                        $user = $document->owner->users()->first();
                    } elseif ($document->owner instanceof Individual) {
                        // Individual - has single user
                        $user = $document->owner->user;
                    } else {
                        $user = null;
                    }

                    if ($user) {
                        $message = __('notifications.payment_made', ['value' => money($document->total_value, $document->currency)]);
                        $user->notify(new UserAlert($message));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify document owner', [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
            }

            activity('document')
                ->performedOn($document)
                ->event('paid')
                ->withProperties($document->toArray())
                ->log("Document {$document->number_extended} has been paid.");

            return $payment;
        } catch (Exception $e) {
            DB::rollBack();
            // log the error
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new payment document with a single line item that is a debit for the total value of
     * the original document
     *
     * @param  Document  $document  The document to be paid
     * @param  string|null  $reason  The reason for the payment.
     * @return Document A new payment document
     *
     * @throws Exception
     */
    public function createPaymentDocumentWithDetail(
        Document $document,
        ?string $reason = null
    ): Document {
        // Document type
        $docType = DocumentType::where('code', 'PAY')->first();

        if (empty($docType)) {
            throw new Exception('Document type not found', 500);
        }

        // generate the number for the payment document
        $docNumber = new GenerateDocumentNumberAction;
        $generatedNumber = $docNumber($docType);

        // Create a new payment document
        $paymentData = [
            'type_id' => $docType->id,
            'status_class' => PaidDocumentState::class,
            'owner_id' => $document->id,
            'owner_type' => Document::class,
            'customer_name' => $document->owner?->name ?? $document->customer_name,
            'net_value' => -(float) $document->net_value,
            'tax_value' => -(float) $document->tax_value,
            'tax_percentage' => $document->tax_percentage,
            'total_value' => -(float) $document->total_value,
            'method_id' => $document->method_id,
            'number' => $generatedNumber['number'],
            'number_pad' => $generatedNumber['number_pad'],
            'number_year' => $generatedNumber['number_year'],
            'number_extended' => $generatedNumber['number_extended'],
        ];
        $payment = DocumentData::toModel(DocumentData::fromArray($paymentData));
        $payment->save();

        // Create the payment document line
        $paymentDetailLine = [
            'document_id' => $payment->id,
            'unit_value' => $payment->total_value,
            'net_value' => $payment->net_value,
            'tax_value' => $payment->tax_value,
            'tax_percentage' => $payment->tax_percentage,
            'total_value' => $payment->total_value,
            'description' => 'Payment for Invoice #'.$document->number_extended.' :: '.$reason,
            'owner_id' => $document->id,
            'owner_type' => Document::class,
            'is_debit' => true,
        ];
        $detail = DocumentDetailData::toModel(DocumentDetailData::fromArray($paymentDetailLine));
        $detail->save();

        return $payment;
    }

    /**
     * Change the document's status to paid.
     *
     * @param  Document  $document  The document that we want to change the state of.
     */
    private function changeDocumentToPaidState(Document $document): void
    {
        $document->status_class = PaidDocumentState::class;
        $document->save();
    }
}
