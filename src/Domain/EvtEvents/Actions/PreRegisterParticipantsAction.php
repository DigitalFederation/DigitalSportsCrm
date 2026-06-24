<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Exceptions\EnrollmentValidationException;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PreRegisterParticipantsAction handles bulk pre-registration of participants for events.
 *
 * This action manages the registration process for multiple participant types (athletes,
 * coaches, referees, officials) in a single transaction, including validation,
 * enrollment creation, and consolidated payment document generation.
 */
class PreRegisterParticipantsAction
{
    /**
     * Execute the pre-registration process for multiple participant types.
     *
     * @param  Event  $event  The event for participant registration
     * @param  Model  $enrollable  The entity doing the enrollment (Federation/Entity)
     * @param  array<string, array>  $participants  Participant data grouped by role type
     * @param  array  $creditsUsed  Credits used in this registration, grouped by role type
     *
     * @throws EnrollmentValidationException When validation fails
     * @throws Exception For unexpected errors during processing
     */
    public function execute(Event $event, Model $enrollable, array $participants, array $creditsUsed = []): Enrollment
    {
        try {
            $this->validateInput($event, $enrollable, $participants);

            return DB::transaction(function () use ($event, $enrollable, $participants, $creditsUsed) {
                $enrollment = $this->createEnrollment($event, $enrollable);

                foreach ($participants as $roleType => $roleParticipants) {
                    $this->createRoleEnrollments($enrollment, $roleType, $roleParticipants, $enrollable);
                }

                $this->createConsolidatedPaymentDocument($event, $enrollment, $enrollable, $participants, $creditsUsed);

                return $enrollment;
            });
        } catch (Exception $e) {

            Log::error('Pre-registration failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            throw new EnrollmentValidationException($e->getMessage());
        }
    }

    /**
     * Create enrollments for participants of a specific role type.
     *
     * @param  Enrollment  $enrollment  Base enrollment record
     * @param  string  $roleType  Type of participant role
     * @param  array  $participants  Array of participant data
     * @param  Model  $enrollable  Enrolling entity
     *
     * @throws Exception
     */
    private function createRoleEnrollments(
        Enrollment $enrollment,
        string $roleType,
        array $participants,
        Model $enrollable
    ): array {

        // 1) Get the single Pricing row for this role.
        //    If no Pricing found, default to 0.
        $pricing = $this->getPricingForRole($enrollment->event, $roleType);
        $price = $pricing->price ?? 0.0;

        $roleEnrolls = [];

        // 2) Loop over each participant
        foreach ($participants as $participant) {
            // Create a sub-enrollment for the participant (athlete, coach, etc.)
            $createdEnrollment = match ($roleType) {
                'athlete' => (new CreateAthleteEnrollmentAction)->execute(
                    $enrollment->event,
                    $enrollable instanceof Federation ? $enrollable : null,
                    $participant['id'],
                    $enrollment,
                    $pricing?->id,
                    $participant['discipline_pricing_id'] ?? null,
                    null,
                    $participant['discipline_id'] ?? null,
                    [],
                    $enrollable instanceof Entity ? $enrollable : null
                ),
                'coach' => (new CreateCoachEnrollmentAction)->execute(
                    $enrollment->event,
                    $enrollable,
                    $participant['id'],
                    $enrollment,
                    $pricing?->id
                ),
                'referee' => (new CreateRefereeEnrollmentAction)->execute(
                    $enrollment->event,
                    $enrollable,
                    $participant['id'],
                    $enrollment,
                    $pricing?->id
                ),
                'official' => (new CreateTeamOfficialEnrollmentAction)->execute(
                    $enrollment->event,
                    $enrollable,
                    $participant['id'],
                    $enrollment,
                    $pricing?->id
                ),
                default => null,
            };

            // 3) If price = 0 => Mark sub-enrollment with proper active state
            if ($price === 0.0 && $createdEnrollment) {
                $statusClass = match ($roleType) {
                    'athlete' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                    'coach' => RegisteredCoachEnrollmentState::class,
                    'referee' => ActiveRefereeEnrollmentState::class,
                    'official' => RegisteredTeamOfficialEnrollmentState::class,
                    default => null,
                };

                if ($statusClass) {
                    $createdEnrollment->update([
                        'status_class' => $statusClass,
                    ]);
                }
            }

            if ($createdEnrollment) {
                $roleEnrolls[] = $createdEnrollment;
            }
        }

        return $roleEnrolls;
    }

    /**
     * Validate enrollable entity and participant data.
     *
     * @param  Event  $event  Target event
     * @param  Model  $enrollable  Enrolling entity
     * @param  array  $participants  Participant data by role
     *
     * @throws EnrollmentValidationException
     */
    private function validateInput(
        Event $event,
        Model $enrollable,
        array $participants
    ): void {
        if (! ($enrollable instanceof Federation || $enrollable instanceof Entity || $enrollable instanceof Individual)) {
            throw new EnrollmentValidationException('Invalid enrollable type. Must be Federation, Entity or Entity.');
        }

        if (empty($participants)) {
            throw new EnrollmentValidationException('No athletes provided for enrollment.');
        }

        if (! $event->allowsEnrollments()) {
            throw new EnrollmentValidationException('Event is not open for enrollment.');
        }

        // Validate role-specific constraints
        foreach ($participants as $roleType => $roleParticipants) {
            if (! empty($roleParticipants)) {
                $this->validateRoleEnrollment($event, $roleType);
            }
        }
    }

    /**
     * Validate role-specific enrollment requirements.
     *
     * @param  Event  $event  Target event
     * @param  string  $roleType  Role being validated
     *
     * @throws EnrollmentValidationException
     */
    private function validateRoleEnrollment(
        Event $event,
        string $roleType
    ): void {
        match ($roleType) {
            'coach' => $this->validateCoachEnrollment($event),
            'referee' => $this->validateRefereeEnrollment($event),
            default => null
        };
    }

    private function validateCoachEnrollment(Event $event): void
    {
        if (! $event->allow_coach_enrollment) {
            throw new EnrollmentValidationException('Coach enrollment is not allowed for this event.');
        }
    }

    private function validateRefereeEnrollment(Event $event): void
    {
        if (! $event->allow_referee_enrollment) {
            throw new EnrollmentValidationException('Referee enrollment is not allowed for this event.');
        }
    }

    /**
     * Create base enrollment record.
     *
     * @param  Event  $event  Target event
     * @param  Model  $enrollable  Enrolling entity
     * @return Enrollment Created enrollment record
     */
    private function createEnrollment(
        Event $event,
        Model $enrollable
    ): Enrollment {
        return Enrollment::create([
            'payment_status' => EvtEventPaymentStatusEnum::PENDING,
            'event_id' => $event->getKey(),
            'enrollable_id' => $enrollable->getKey(),
            'enrollable_type' => get_class($enrollable),
            'user_id' => Auth::id(),
            'total_price' => 0,
        ]);
    }

    /**
     * Create consolidated payment document for all participant types.
     *
     * @param  Event  $event  Target event
     * @param  Enrollment  $enrollment  Base enrollment record
     * @param  Model  $enrollable  Enrolling entity
     * @param  array<string, array>  $participants  Participant data by role
     * @param  array  $creditsUsed  Credits used in this registration
     */
    public function createConsolidatedPaymentDocument(
        Event $event,
        Enrollment $enrollment,
        Model $enrollable,
        array $participants,
        array $creditsUsed = []
    ): void {
        $selectedIndividuals = [];
        $totalCost = 0;
        $creditApplied = 0;

        // Process each role type and their participants
        foreach ($participants as $roleType => $roleParticipants) {
            $pricing = $this->getPricingForRole($event, $roleType);
            if (! $pricing) {
                continue;
            }

            // Get total participants count for this role
            $participantCount = count($roleParticipants);

            // Calculate base cost for this role
            $roleCost = $pricing->price * $participantCount;

            // Apply credit if available for this role
            $roleCredit = $creditsUsed[$roleType]['monetary_value'] ?? 0;
            $roleCreditSlots = $creditsUsed[$roleType]['slots_used'] ?? 0;

            // Adjust role cost for credits used
            if ($roleCredit > 0) {
                $roleCost = max(0, $roleCost - $roleCredit);
                $creditApplied += $roleCredit;
            }

            // Add to total cost
            $totalCost += $roleCost;

            // Format participants for document creation
            foreach ($roleParticipants as $participant) {
                $participantData = [
                    'individual_id' => $participant['id'],
                    'role' => strtoupper($roleType),
                    'pricing_id' => $pricing->id,
                ];

                // Add discipline-specific data for athletes
                if ($roleType === 'athlete' && isset($participant['discipline_id'])) {
                    $participantData['discipline_id'] = $participant['discipline_id'];
                    $participantData['discipline_price'] = $participant['discipline_price'] ?? 0;
                }

                $selectedIndividuals[] = $participantData;
            }
        }

        // Update enrollment with final total price
        $enrollment->update([
            'total_price' => $totalCost,
            'pricing_id' => null, // Since we're handling multiple pricing types
            'credits_applied' => ! empty($creditsUsed) ? json_encode($creditsUsed) : null,
            'payment_status' => $totalCost === 0 ? EvtEventPaymentStatusEnum::PAID : EvtEventPaymentStatusEnum::PENDING,
        ]);

        if ($totalCost > 0) {
            $createEnrollmentPaymentDocumentAction = new CreateEnrollmentPaymentDocumentAction;
            $document = $createEnrollmentPaymentDocumentAction->execute(
                $event,
                $enrollment,
                (string) $enrollable->getKey(),
                get_class($enrollable),
                $selectedIndividuals,
                $totalCost,
                null // No single pricing ID as we handle multiple roles
            );

            $enrollment->update([
                'document_id' => $document->id,
            ]);

            // Update all enrollments to PENDING_PAYMENT
            foreach ($enrollment->athleteEnrollments as $athleteEnrollment) {
                $athleteEnrollment->update([
                    'status_class' => EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                ]);
            }
        } else {
            // If total cost is zero (fully covered by credits), mark all enrollments as PAID
            foreach ($enrollment->athleteEnrollments as $athleteEnrollment) {
                $athleteEnrollment->update([
                    'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
                ]);
            }

            // Also mark coach, referee and official enrollments as active if applicable
            if (method_exists($enrollment, 'coachEnrollments')) {
                foreach ($enrollment->coachEnrollments as $coachEnrollment) {
                    if (method_exists($coachEnrollment, 'update')) {
                        $coachEnrollment->update([
                            'status_class' => RegisteredCoachEnrollmentState::class,
                        ]);
                    }
                }
            }

            if (method_exists($enrollment, 'refereeEnrollments')) {
                foreach ($enrollment->refereeEnrollments as $refereeEnrollment) {
                    if (method_exists($refereeEnrollment, 'update')) {
                        $refereeEnrollment->update([
                            'status_class' => ActiveRefereeEnrollmentState::class,
                        ]);
                    }
                }
            }

            if (method_exists($enrollment, 'officialsEnrollments')) {
                foreach ($enrollment->officialsEnrollments as $officialEnrollment) {
                    if (method_exists($officialEnrollment, 'update')) {
                        $officialEnrollment->update([
                            'status_class' => RegisteredTeamOfficialEnrollmentState::class,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Get applicable pricing for a specific role type.
     *
     * @param  Event  $event  Target event
     * @param  string  $roleType  Role type for pricing lookup
     * @return Pricing|null Pricing configuration if found
     */
    private function getPricingForRole(
        Event $event,
        string $roleType
    ): ?Pricing {
        $enrollmentRole = match ($roleType) {
            'athlete' => EvtEventEnrollmentRoleEnum::ATHLETE,
            'coach' => EvtEventEnrollmentRoleEnum::COACH,
            'referee' => EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL,
            'official' => EvtEventEnrollmentRoleEnum::OFFICIAL,
            default => null
        };

        if (! $enrollmentRole) {
            return null;
        }

        return Pricing::query()
            ->where('event_id', $event->id)
            ->where('enrollment_role', $enrollmentRole)
            ->where('is_active', true)
            ->first();
    }
}
