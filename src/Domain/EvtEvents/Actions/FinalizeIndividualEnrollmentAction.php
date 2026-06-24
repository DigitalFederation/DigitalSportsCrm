<?php

declare(strict_types=1);

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Models\User;
use Domain\Documents\Actions\CreateDocumentWithDetailsAction;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FinalizeIndividualEnrollmentAction
{
    /**
     * Finalizes the individual enrollment process.
     * Recalculates total cost based on *all* athlete enrollments, handles PER_PERSON/EVENT_FEE logic,
     * updates the parent Enrollment status, total cost, and creates/updates a payment document if necessary.
     *
     * @param  Event  $event  The event being enrolled in.
     * @param  Individual  $individual  The individual being enrolled.
     * @param  Enrollment  $enrollment  The parent Enrollment record (retrieved or created).
     * @param  Collection  $newAthleteEnrollments  Collection of newly created AthleteEnrollment models for this batch.
     * @param  User  $user  The user performing the action.
     * @return array Result array ['success' => bool, 'message' => string, 'document_id' => ?int, 'total_cost' => float]
     */
    public function execute(
        Event $event,
        Individual $individual,
        Enrollment $enrollment,
        Collection $newAthleteEnrollments,
        User $user
    ): array {
        // Get the real instance of CreateEnrollmentPaymentDocumentAction
        $createEnrollmentPaymentDocumentAction = app(CreateEnrollmentPaymentDocumentAction::class);

        try {
            DB::beginTransaction();

            // 1. Load ALL current athlete enrollments
            $allAthleteEnrollments = $enrollment->athleteEnrollments()->get();

            // 2. Recalculate the total cost
            $calculatedTotalCost = 0.0;
            $perPersonFeeApplied = false;
            $eventFeeApplied = false;
            foreach ($allAthleteEnrollments as $ae) {
                $calculatedTotalCost += $ae->discipline_price ?? 0;
                if (! $perPersonFeeApplied && ($ae->per_person_price ?? 0) > 0) {
                    $calculatedTotalCost += $ae->per_person_price;
                    $perPersonFeeApplied = true;
                }
                if (! $eventFeeApplied && ($ae->event_fee ?? 0) > 0) {
                    $calculatedTotalCost += $ae->event_fee;
                    $eventFeeApplied = true;
                }
            }

            // 3. Handle Parent Status & Document based on Cost and Existing Document State
            $documentId = $enrollment->document_id;
            /** @var Document|null $existingDocument */
            $existingDocument = $documentId ? Document::find($documentId) : null;

            $finalParentPaymentStatus = EvtEventPaymentStatusEnum::PENDING; // Default assumption
            $newDocumentCreated = false;
            $updatedExistingDocument = false;

            if ($calculatedTotalCost > 0) {
                if ($existingDocument && $existingDocument->status_class === PaidDocumentState::class) {
                    // Cost > 0, but base enrollment was already PAID. Keep PAID status.
                    $finalParentPaymentStatus = EvtEventPaymentStatusEnum::PAID;
                    $documentId = $existingDocument->id; // Keep link to PAID document
                    Log::info("Enrollment {$enrollment->id} remains PAID despite new cost ({$calculatedTotalCost}) due to existing PAID document {$documentId}.");
                } elseif ($existingDocument && $existingDocument->status_class === PendingDocumentState::class) {
                    // Cost > 0, update existing PENDING document
                    $finalParentPaymentStatus = EvtEventPaymentStatusEnum::PENDING;
                    $existingDocument->total_value = $calculatedTotalCost;
                    $existingDocument->save();
                    $documentId = $existingDocument->id;
                    $updatedExistingDocument = true;
                    Log::info("Updated existing PENDING payment document {$documentId} value to {$calculatedTotalCost} for enrollment {$enrollment->id}");
                } else {
                    // Cost > 0, No document or Non-Pending/Non-PAID document exists. Create NEW.
                    $finalParentPaymentStatus = EvtEventPaymentStatusEnum::PENDING;
                    // Format athlete data based on *all* enrollments for the new document
                    $athleteDataForDocument = $allAthleteEnrollments->map(fn ($ae) => [
                        'id' => $ae->individual_id,
                        'individual_id' => $ae->individual_id,
                        'role' => 'ATHLETE',
                        'pricing_id' => $ae->per_person_pricing_id ?? $ae->discipline_pricing_id ?? $ae->event_fee_pricing_id,
                        'discipline_id' => $ae->discipline_id,
                        'discipline_price' => $ae->discipline_price,
                        'per_person_price' => $ae->per_person_price,
                        'event_fee_price' => $ae->event_fee,
                    ])->toArray();

                    // Call the *real* payment document action
                    // The underlying CreateDocumentWithDetailsAction will be mocked in the test
                    $newDocument = $createEnrollmentPaymentDocumentAction->execute(
                        $event,
                        $enrollment,        // Parent enrollment
                        $individual->id,    // Payer ID
                        Individual::class, // Payer Type
                        $athleteDataForDocument, // Data based on *all* items
                        $calculatedTotalCost,  // Use the *recalculated* total cost
                        null // $documentSeriesId (pass if applicable)
                    );
                    // Add detailed log right after the call
                    Log::info('Returned from createEnrollmentPaymentDocumentAction', [
                        'doc_received' => ! is_null($newDocument),
                        'doc_class' => is_object($newDocument) ? get_class($newDocument) : gettype($newDocument),
                        'doc_id' => $newDocument?->id,
                    ]);

                    if ($newDocument === null) {
                        // No document created - total value is zero, set to PAID
                        $finalParentPaymentStatus = EvtEventPaymentStatusEnum::PAID;
                        $documentId = null;
                        Log::info("No payment document created for enrollment {$enrollment->id} - total value is zero");
                    } else {
                        $documentId = $newDocument->id;
                        $newDocumentCreated = true;
                        Log::info("Created NEW payment document {$documentId} for enrollment {$enrollment->id} with cost {$calculatedTotalCost}");
                    }
                }
            } else { // calculatedTotalCost == 0
                if ($existingDocument && $existingDocument->status_class === PaidDocumentState::class) {
                    // Cost is 0, but was previously PAID. Keep PAID status and link.
                    $finalParentPaymentStatus = EvtEventPaymentStatusEnum::PAID;
                    $documentId = $existingDocument->id; // Keep link to PAID document
                    Log::info("Enrollment {$enrollment->id} cost is zero after removal, keeping PAID status due to existing PAID document {$documentId}.");
                } elseif ($existingDocument && $existingDocument->status_class === PendingDocumentState::class) {
                    // Cost is 0, and a PENDING document exists.
                    // This document is now irrelevant as no payment is needed.
                    // Set parent enrollment to PAID and unlink the document.
                    $finalParentPaymentStatus = EvtEventPaymentStatusEnum::PAID;
                    Log::info("Cost is zero. Unlinking now irrelevant PENDING document {$existingDocument->id} for enrollment {$enrollment->id}. Setting enrollment status to PAID.");
                    // Optionally: Add logic here to cancel/delete $existingDocument if required by business rules.
                    // e.g., $existingDocument->cancel(); or $existingDocument->delete();
                    $documentId = null; // Unlink the document
                    $updatedExistingDocument = false; // Document wasn't updated, it's being unlinked.
                } else {
                    // Cost is 0, no relevant document exists (or existing one wasn't Pending/Paid).
                    // Set status to PAID and unlink any irrelevant doc.
                    $finalParentPaymentStatus = EvtEventPaymentStatusEnum::PAID;
                    if ($enrollment->document_id !== null) {
                        Log::info("Cost is zero. Unlinking irrelevant document {$enrollment->document_id} for enrollment {$enrollment->id}");
                    }
                    $documentId = null;
                }
            }

            // 4. Update the parent Enrollment record
            $enrollment->total_price = $calculatedTotalCost;
            $enrollment->payment_status = $finalParentPaymentStatus->value;
            $enrollment->document_id = $documentId; // Update link based on logic above
            $enrollment->activated_at = $enrollment->activated_at ?? now();
            $enrollment->save();

            // 5. Update status of all associated Athlete Enrollments
            $finalAthleteStatus = ($finalParentPaymentStatus === EvtEventPaymentStatusEnum::PAID)
                ? EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED
                : EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT;
            $updatedCount = $enrollment->athleteEnrollments()->update(['status_class' => $finalAthleteStatus]);

            // Log this update step
            Log::info("Updated status of {$updatedCount} athlete enrollments for enrollment {$enrollment->id} to {$finalAthleteStatus->value}");
            activity('enrollment_process')
                ->causedBy($user)
                ->performedOn($enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $individual->id,
                    'enrollment_id' => $enrollment->id,
                    'final_athlete_status' => $finalAthleteStatus->value,
                    'updated_count' => $updatedCount,
                ])
                ->log('Synchronized status for all associated athlete enrollments.');

            DB::commit();

            // 6. Prepare success message
            $message = ($finalParentPaymentStatus === EvtEventPaymentStatusEnum::PENDING && $calculatedTotalCost > 0)
                ? 'Registration updated. Please proceed with the payment.'
                : 'Registration updated successfully.';
            if ($finalParentPaymentStatus === EvtEventPaymentStatusEnum::PAID && $calculatedTotalCost == 0) {
                // If cost is zero AND status is PAID (either initially or after removal from PAID)
                $message = 'Registration updated. No payment required.';
            }

            // 7. Log overall activity
            activity('enrollment_process')
                ->causedBy($user)
                ->performedOn($enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $individual->id,
                    'enrollment_id' => $enrollment->id,
                    'calculated_total_cost' => $calculatedTotalCost,
                    'payment_status' => $finalParentPaymentStatus->value,
                    'document_id' => $enrollment->document_id,
                    'newly_added_count' => $newAthleteEnrollments->count(),
                    'total_disciplines' => $allAthleteEnrollments->count(),
                ])
                ->log('Individual enrollment finalization complete.');

            return [
                'success' => true,
                'message' => $message,
                'document_id' => $enrollment->document_id,
                'total_cost' => $calculatedTotalCost,
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Failed to finalize individual enrollment action', [
                'event_id' => $event->id,
                'individual_id' => $individual->id,
                'enrollment_id' => $enrollment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Log activity for failure
            activity('enrollment_error')
                ->causedBy($user)
                ->performedOn($enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $individual->id,
                    'enrollment_id' => $enrollment->id ?? null,
                    'error_type' => get_class($e),
                    'error_message' => $e->getMessage(),
                ])
                ->log('Error during FinalizeIndividualEnrollmentAction execution.');

            return [
                'success' => false,
                'message' => 'An unexpected error occurred during finalization: ' . $e->getMessage(),
                'document_id' => null,
                'total_cost' => $calculatedTotalCost ?? 0,
            ];
        }
    }
}
