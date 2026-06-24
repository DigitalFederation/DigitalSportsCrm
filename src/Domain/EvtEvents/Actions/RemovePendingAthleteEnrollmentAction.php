<?php

declare(strict_types=1);

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Models\User;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RemovePendingAthleteEnrollmentAction
{
    /**
     * Removes a pending athlete enrollment.
     *
     * @param  string  $expectedIndividualId  ID of the individual who should own this enrollment (Assuming UUID)
     * @param  User  $actingUser  User performing the action
     * @return array{success: bool, message: string}
     */
    public function execute(int $athleteEnrollmentId, string $expectedIndividualId, User $actingUser): array
    {
        $enrollment = AthleteEnrollment::with(['discipline:id,name', 'enrollment'])->find($athleteEnrollmentId);

        if (! $enrollment) {
            return ['success' => false, 'message' => 'Enrollment not found.'];
        }

        // Authorization & Status Check
        $statusValue = $enrollment->status_class instanceof \UnitEnum
            ? $enrollment->status_class->value
            : $enrollment->status_class;

        if ($enrollment->individual_id !== $expectedIndividualId || $statusValue !== EvtAthleteEnrollmentStatusEnum::REGISTERED->value) {
            Log::warning('Unauthorized or invalid enrollment removal attempt', [
                'athlete_enrollment_id' => $athleteEnrollmentId,
                'expected_individual_id' => $expectedIndividualId,
                'actual_individual_id' => $enrollment->individual_id,
                'status_class' => $statusValue,
                'acting_user_id' => $actingUser->id,
            ]);

            return ['success' => false, 'message' => 'Cannot remove this enrollment.'];
        }

        $parentEnrollment = $enrollment->enrollment;
        $logProperties = [
            'event_id' => $enrollment->event_id,
            'individual_id' => $enrollment->individual_id,
            'discipline_id' => $enrollment->discipline_id,
            'discipline_name' => $enrollment->discipline?->name,
            'parent_enrollment_id' => $enrollment->enrollment_id,
        ];

        // Log the removal attempt BEFORE the transaction
        activity('enrollment_process')
            ->causedBy($actingUser)
            ->performedOn($enrollment)
            ->withProperties($logProperties)
            ->log('Individual attempting remove discipline from pending list');

        try {
            DB::beginTransaction();

            // Delete attributes first (if any)
            $enrollment->attributes()->delete();

            // Then delete the athlete enrollment itself using raw query
            DB::table('evt_athletes_enrollment')->where('id', $athleteEnrollmentId)->delete();

            DB::commit();

            // Log Success AFTER Commit
            if ($parentEnrollment) {
                activity('enrollment_process')
                    ->causedBy($actingUser)
                    ->performedOn($parentEnrollment)
                    ->withProperties($logProperties)
                    ->log('Individual removed discipline enrollment');
            } else {
                Log::warning('Parent enrollment missing during successful removal logging', [
                    'athlete_enrollment_id' => $athleteEnrollmentId,
                ]);
            }

            return ['success' => true, 'message' => 'The discipline has been successfully removed.'];

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Failed to remove enrollment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'athlete_enrollment_id' => $athleteEnrollmentId,
            ]);

            return ['success' => false, 'message' => 'Failed to remove enrollment: ' . $e->getMessage()];
        }
    }
}
