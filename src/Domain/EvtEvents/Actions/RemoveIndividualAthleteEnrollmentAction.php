<?php

declare(strict_types=1);

namespace Domain\EvtEvents\Actions;

use App\Models\User;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Removes an individual AthleteEnrollment record and triggers recalculation
 * of the parent Enrollment's total cost and document status.
 */
final readonly class RemoveIndividualAthleteEnrollmentAction
{
    public function __construct(
        private DatabaseManager $db,
        private FinalizeIndividualEnrollmentAction $finalizeAction // Inject Finalize action to reuse logic
    ) {}

    /**
     * @param  int|string  $athleteEnrollmentId  The ID of the AthleteEnrollment to remove.
     * @param  User  $user  The user performing the action.
     * @return array Result array indicating success or failure.
     *
     * @throws Exception If the enrollment cannot be found or removal fails.
     */
    public function execute(int|string $athleteEnrollmentId, User $user): array
    {
        $result = [];

        $this->db->connection()->transaction(function () use ($athleteEnrollmentId, $user, &$result) {

            // 1. Find the AthleteEnrollment record
            /** @var AthleteEnrollment|null $athleteEnrollment */
            $athleteEnrollment = AthleteEnrollment::with(['enrollment.event', 'individual'])->find($athleteEnrollmentId);

            if (! $athleteEnrollment) {
                throw new Exception("Athlete Enrollment record with ID {$athleteEnrollmentId} not found.");
            }

            /** @var Enrollment $parentEnrollment */
            $parentEnrollment = $athleteEnrollment->enrollment;
            /** @var Event $event */
            $event = $parentEnrollment->event;
            /** @var Individual $individual */
            $individual = $athleteEnrollment->individual;

            // Authorization Check: Ensure the user initiated the original enrollment
            if ($parentEnrollment->user_id !== $user->id) {
                throw new AuthorizationException('User is not authorized to remove this enrollment record.');
            }

            if (! $parentEnrollment || ! $event || ! $individual) {
                throw new Exception("Could not load related parent enrollment, event, or individual data for Athlete Enrollment ID {$athleteEnrollmentId}.");
            }

            $disciplineName = $athleteEnrollment->discipline?->name ?? 'N/A';
            $individualName = $individual->full_name ?? 'N/A';

            // 2. Perform the deletion (soft delete)
            $deleted = $athleteEnrollment->delete();

            if (! $deleted) {
                throw new Exception("Failed to delete Athlete Enrollment ID {$athleteEnrollmentId}.");
            }

            // 3. Log the deletion activity
            activity('enrollment_process')
                ->causedBy($user)
                ->performedOn($parentEnrollment) // Log against parent
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $individual->id,
                    'parent_enrollment_id' => $parentEnrollment->id,
                    'removed_athlete_enrollment_id' => $athleteEnrollmentId,
                    'discipline_name' => $disciplineName,
                ])
                ->log("Removed discipline enrollment ({$disciplineName}) for individual {$individualName}.");

            // 4. Trigger the Finalize action to recalculate costs and update document
            // Pass an empty collection for $newAthleteEnrollments as we are only removing
            $finalizeResult = $this->finalizeAction->execute(
                $event,
                $individual,
                $parentEnrollment,
                new Collection, // No new enrollments being added
                $user
            );

            // Check if the finalize step reported an issue (though it should handle recalculation fine)
            if (! ($finalizeResult['success'] ?? false)) {
                // Log a warning but don't necessarily fail the whole transaction if deletion succeeded
                Log::warning('Finalize action reported an issue after athlete enrollment removal.', [
                    'athlete_enrollment_id' => $athleteEnrollmentId,
                    'parent_enrollment_id' => $parentEnrollment->id,
                    'finalize_result' => $finalizeResult,
                ]);
                // Depending on requirements, you might choose to throw an exception here instead
                // throw new Exception("Recalculation failed after removing enrollment: " . ($finalizeResult['message'] ?? 'Unknown error'));
            }

            Log::info("Successfully removed Athlete Enrollment ID {$athleteEnrollmentId} and triggered recalculation.");

            // Return success, potentially including info from the finalize step if needed
            $result = [
                'success' => true,
                'message' => 'Discipline enrollment removed successfully. Parent enrollment updated.',
                'updated_enrollment_id' => $parentEnrollment->id,
                'final_cost' => $finalizeResult['total_cost'] ?? null, // Reflect the cost *after* removal
                'document_id' => $finalizeResult['document_id'] ?? null,
            ];
        }, attempts: 2); // Use transaction with retry attempts

        return $result;
    }
}
