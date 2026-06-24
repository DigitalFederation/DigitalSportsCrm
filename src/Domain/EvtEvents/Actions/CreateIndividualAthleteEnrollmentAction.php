<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\AthleteEnrollmentAttributes;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Class CreateIndividualAthleteEnrollmentAction
 *
 * Handles the creation of athlete enrollments for individual athletes registering themselves.
 * This differs from bulk enrollments (by federations/clubs) as individuals must select disciplines
 * first and pay afterwards.
 *
 * Flow:
 * 1. Athlete selects discipline
 * 2. Fills in required attributes
 * 3. After all disciplines are selected, a payment documet is created
 * 4 Status changes to PENDING_PAYMENT or DISCIPLINE_ASSIGNED based on cost
 */
class CreateIndividualAthleteEnrollmentAction
{
    protected $getAttributesAction;
    protected $validateAttributesAction;

    public function __construct(
        GetAttributesAndRulesFromDisciplineAction $getAttributesAction,
        ValidateAndSummarizeAthleteEnrollmentsAction $validateAttributesAction
    ) {
        $this->getAttributesAction = $getAttributesAction;
        $this->validateAttributesAction = $validateAttributesAction;
    }

    /**
     * Create an AthleteEnrollment record for an individual in an event.
     *
     * @param  Event  $event  The event being enrolled in.
     * @param  string  $individualId  The ID of the individual being enrolled.
     * @param  Enrollment  $enrollment  The parent enrollment record.
     * @param  int|null  $perPersonPricingId  ID of the applicable per-person pricing.
     * @param  int|null  $disciplinePricingId  ID of the applicable discipline pricing.
     * @param  int|null  $eventFeePricingId  ID of the applicable event fee pricing.
     * @param  int  $disciplineId  The ID of the discipline being enrolled in.
     * @param  array  $attributeValues  Key-value pairs of attribute IDs and their values.
     * @param  array  $calculatedCosts  Pre-calculated costs: ['per_person' => float, 'discipline' => float, 'event_fee' => float].
     * @param  EvtAthleteEnrollmentStatusEnum  $initialStatus  The initial status to set for the enrollment.
     * @return AthleteEnrollment The created athlete enrollment record.
     *
     * @throws \Exception
     */
    public function execute(
        Event $event,
        string $individualId,
        Enrollment $enrollment,
        ?int $perPersonPricingId,
        ?int $disciplinePricingId,
        ?int $eventFeePricingId,
        int $disciplineId,
        array $attributeValues,
        array $calculatedCosts,
        EvtAthleteEnrollmentStatusEnum $initialStatus
    ): AthleteEnrollment {
        if (! $event->allowsEnrollments()) {
            throw new \DomainException(__('Enrollments are currently closed for this event.'));
        }

        try {
            DB::beginTransaction();

            // Calculate total price from the passed costs
            $totalPrice = ($calculatedCosts['per_person'] ?? 0)
                + ($calculatedCosts['discipline'] ?? 0)
                + ($calculatedCosts['event_fee'] ?? 0);

            /** @var AthleteEnrollment $athleteEnrollment */
            $athleteEnrollment = AthleteEnrollment::create([
                'enrollment_id' => $enrollment->id,
                'event_id' => $event->id,
                'individual_id' => $individualId,
                'discipline_id' => $disciplineId,
                'federation_id' => $enrollment->enrollable->federation_id ?? null,
                'entity_id' => ($enrollment->enrollable_type === Entity::class) ? $enrollment->enrollable_id : ($enrollment->enrollable->entity_id ?? null),
                'per_person_pricing_id' => $perPersonPricingId,
                'discipline_pricing_id' => $disciplinePricingId,
                'event_fee_pricing_id' => $eventFeePricingId,
                'per_person_price' => $calculatedCosts['per_person'] ?? 0,
                'discipline_price' => $calculatedCosts['discipline'] ?? 0,
                'event_fee' => $calculatedCosts['event_fee'] ?? 0,
                'total_price' => $totalPrice,
                'status_class' => $initialStatus,
            ]);

            // Save attributes
            $this->saveAttributes($athleteEnrollment, $attributeValues);

            DB::commit();

            // Log activity (optional, could be done in finalize step)
            // Activity::log(...) // Example: Log creation with initial status

            return $athleteEnrollment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create individual athlete enrollment', [
                'event_id' => $event->id,
                'individual_id' => $individualId,
                'enrollment_id' => $enrollment->id,
                'discipline_id' => $disciplineId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw the exception to be caught by the caller (Livewire component)
        }
    }

    private function saveAttributes(AthleteEnrollment $athleteEnrollment, array $attributes): void
    {
        if (empty($attributes)) {
            return;
        }

        $attributesData = [];
        foreach ($attributes as $attributeId => $value) {
            // Ensure attributeId is valid if needed (e.g., check against Discipline attributes)
            $attributesData[] = [
                'athlete_enrollment_id' => $athleteEnrollment->id,
                'attribute_id' => $attributeId,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        AthleteEnrollmentAttributes::insert($attributesData);
    }

    /**
     * Calculates the price for a given pricing ID.
     *
     * @return float Returns 0.0 if no pricing found
     */
    private function calculatePrice(?int $pricingId): float
    {
        if (! $pricingId) {
            return 0.0;
        }
        $pricing = Pricing::find($pricingId);

        return $pricing ? $pricing->price : 0.0;
    }

    private function validateEventEnrollment(Event $event): void
    {
        if (! $event->allowsEnrollments()) {
            throw new \Exception('Enrollments are not allowed for this event at this time.');
        }
        if (! $event->canEnroll('athlete')) {
            throw new \Exception('Athlete enrollments are not allowed for this event at this time.');
        }
    }

    private function validateDisciplineAttributes(
        string $disciplineId,
        string $individualId,
        array $attributeValues
    ): void {

        $attributeData = $this->getAttributesAction->execute($disciplineId);
        $individual = Individual::with(['individualFederations', 'individualEntities'])
            ->findOrFail($individualId);

        $validationSummary = $this->validateAttributesAction->execute(
            [$individual->toArray()],
            $attributeData,
            $attributeValues
        );

        foreach ($validationSummary as $summary) {
            if (! $summary['valid']) {
                $failedRules = array_column($summary['failed_rules'], 'rule');
                throw ValidationException::withMessages([
                    'attributes' => 'Attribute validation failed: ' . implode(', ', $failedRules),
                ]);
            }
        }
    }
}
