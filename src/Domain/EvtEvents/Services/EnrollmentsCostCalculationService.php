<?php

namespace Domain\EvtEvents\Services;

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\Pricing;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentsCostCalculationService
{
    private array $pricingCache = [];

    /**
     * Calculate the total cost for all pending enrollments.
     *
     * @param  Event  $event  The event for which to calculate costs.
     * @param  Collection<int, Enrollment>  $pendingEnrollments  Pending enrollments.
     * @param  bool  $isIndividualEnrollment  Flag to indicate if the enrollment is individual.
     * @return float The total cost of all pending enrollments.
     */
    public function calculateTotalCost(Event $event, Collection $pendingEnrollments, bool $isIndividualEnrollment = false): float
    {

        $breakdown = $this->getCostBreakdown($event, $pendingEnrollments, $isIndividualEnrollment);

        return array_sum(array_column($breakdown, 'cost'));
    }

    /**
     * Recalculate costs for a single athlete enrollment.
     *
     * This method fetches the latest pricing data and recalculates the costs
     * for the given athlete enrollment, including per-person fee, discipline fee,
     * and event fee. It ensures that the most up-to-date pricing is used,
     * regardless of when the enrollment was initially created.
     *
     * @param  AthleteEnrollment  $athleteEnrollment  The athlete enrollment to recalculate costs for.
     * @return array An array of cost items, each containing 'type', 'cost', 'pricingId', and optionally 'disciplineId'.
     */
    public function recalculateAthleteEnrollmentCosts(AthleteEnrollment $athleteEnrollment): array
    {
        // Ensure the event is loaded
        if (! $athleteEnrollment->relationLoaded('event')) {
            $athleteEnrollment->load('event');
        }

        $event = $athleteEnrollment->event;
        $costs = [];

        // Get all applicable pricing for this event and athlete role
        $pricings = Pricing::active()
            ->where('event_id', $event->id)
            ->where(function ($query) {
                $query->where('enrollment_role', EvtEventEnrollmentRoleEnum::ATHLETE->value)
                    ->orWhereNull('enrollment_role');
            })
            ->get();

        foreach ($pricings as $pricing) {
            switch ($pricing->price_type) {
                case EvtEventFeeTypeEnum::PER_PERSON->value:
                    $costs[] = [
                        'type' => 'Per Person Fee',
                        'cost' => $pricing->price,
                        'pricing_id' => $pricing->id,
                    ];
                    break;
                case EvtEventFeeTypeEnum::PER_DISCIPLINE->value:
                    if ($pricing->discipline_id === $athleteEnrollment->discipline_id) {
                        $costs[] = [
                            'type' => 'Discipline Fee',
                            'cost' => $pricing->price,
                            'pricing_id' => $pricing->id,
                            'discipline_id' => $pricing->discipline_id,
                        ];
                    }
                    break;
                case EvtEventFeeTypeEnum::EVENT_FEE->value:
                    $costs[] = [
                        'type' => 'Event Fee',
                        'cost' => $pricing->price,
                        'pricing_id' => $pricing->id,
                    ];
                    break;
            }
        }

        return $costs;
    }

    /**
     * Get a detailed cost breakdown for all pending enrollments.
     *
     * @param  Event  $event  The event for which to calculate costs.
     * @param  Collection<int, Enrollment>  $pendingEnrollments  Pending enrollments.
     * @param  bool  $isIndividualEnrollment  Flag to indicate if the enrollment is individual.
     * @return array An array containing the cost breakdown.
     */
    public function getCostBreakdown(
        Event $event,
        Collection $pendingEnrollments,
        bool $isIndividualEnrollment = false
    ): array {
        $breakdown = [];
        $eventFeeAdded = false;
        $countedIndividuals = []; // New array to track counted individuals

        // Process individual enrollments differently
        if ($isIndividualEnrollment) {

            foreach ($pendingEnrollments as $enrollment) {
                $enrollment->loadMissing('individualEnrollments', 'athleteEnrollments');

                foreach ($enrollment->individualEnrollments as $individualEnrollment) {
                    $cost = $this->getIndividualEnrollmentCost($event, $individualEnrollment);

                    if ($cost <= 0) {
                        continue;
                    }

                    $breakdown[] = [
                        'type' => 'Individual Fee',
                        'individual_id' => $individualEnrollment->individual_id,
                        'cost' => $cost,
                        'pricing_id' => $individualEnrollment->pricing_id,
                    ];
                }

                $this->processAthleteEnrollments(
                    $event,
                    $enrollment,
                    $breakdown,
                    $eventFeeAdded,
                    true,
                    $countedIndividuals
                );
            }

            return $this->groupCostBreakdown($breakdown);
        }

        foreach ($pendingEnrollments as $enrollment) {
            $isIndividual = $enrollment->enrollable_type === Individual::class;

            $this->processEnrollment(
                $event,
                $enrollment,
                $breakdown,
                $eventFeeAdded,
                $isIndividual,
                $countedIndividuals
            );
        }

        return $this->groupCostBreakdown($breakdown);
    }

    private function getIndividualEnrollmentCost(Event $event, IndividualEnrollment $individualEnrollment): float
    {
        if ($individualEnrollment->price !== null && (float) $individualEnrollment->price > 0) {
            return (float) $individualEnrollment->price;
        }

        if ($individualEnrollment->pricing_id) {
            $pricing = Pricing::active()->find($individualEnrollment->pricing_id);

            if ($pricing && (float) $pricing->price > 0) {
                return (float) $pricing->price;
            }
        }

        return (float) $event->calculateUnitCost();
    }

    /**
     * ClassGroup the cost breakdown by type and sum the costs.
     *
     * @param  array  $breakdown  The original breakdown array.
     * @return array The grouped breakdown array.
     */
    private function groupCostBreakdown(array $breakdown): array
    {
        $groupedBreakdown = [];

        foreach ($breakdown as $item) {
            if (! isset($groupedBreakdown[$item['type']])) {
                $groupedBreakdown[$item['type']] = [
                    'type' => $item['type'],
                    'cost' => 0,
                ];
            }
            $groupedBreakdown[$item['type']]['cost'] += $item['cost'];
        }

        return array_values($groupedBreakdown);
    }

    /**
     * Process a single enrollment and add its costs to the breakdown.
     *
     * @param  Event  $event  The event being processed.
     * @param  Enrollment  $enrollment  The enrollment being processed.
     * @param  array  $breakdown  Reference to the cost breakdown array.
     * @param  bool  $eventFeeAdded  Reference to flag indicating if event fee has been added.
     * @param  bool  $isIndividualEnrollment  Flag to indicate if the enrollment is individual.
     */
    private function processEnrollment(Event $event, Enrollment $enrollment, array &$breakdown, bool &$eventFeeAdded, bool $isIndividualEnrollment, array &$countedIndividuals): void
    {
        $this->processAthleteEnrollments($event, $enrollment, $breakdown, $eventFeeAdded, $isIndividualEnrollment, $countedIndividuals);
        $this->processOtherEnrollments($event, $enrollment, $breakdown, EvtEventEnrollmentRoleEnum::INDIVIDUAL);
        $this->processOtherEnrollments($event, $enrollment, $breakdown, EvtEventEnrollmentRoleEnum::COACH);
        $this->processOtherEnrollments($event, $enrollment, $breakdown, EvtEventEnrollmentRoleEnum::OFFICIAL);
    }

    /**
     * Process athlete enrollments for a given enrollment.
     *
     * @param  Event  $event  The event being processed.
     * @param  Enrollment  $enrollment  The enrollment being processed.
     * @param  array  $breakdown  Reference to the cost breakdown array.
     * @param  bool  $eventFeeAdded  Reference to flag indicating if event fee has been added.
     * @param  bool  $isIndividualEnrollment  Flag to indicate if the enrollment is individual.
     */
    private function processAthleteEnrollments(
        Event $event,
        Enrollment $enrollment,
        array &$breakdown,
        bool &$eventFeeAdded,
        bool $isIndividualEnrollment,
        array &$countedIndividuals
    ): void {

        foreach ($enrollment->athleteEnrollments as $athleteEnrollment) {
            $this->addAthleteEnrollmentCosts(
                $event,
                $athleteEnrollment,
                $breakdown,
                $eventFeeAdded,
                $isIndividualEnrollment,
                $countedIndividuals
            );
        }
    }

    /**
     * Add costs for an athlete enrollment to the breakdown.
     *
     * @param  Event  $event  The event being processed.
     * @param  AthleteEnrollment  $athleteEnrollment  The athlete enrollment being processed.
     * @param  array  $breakdown  Reference to the cost breakdown array.
     * @param  bool  $eventFeeAdded  Reference to flag indicating if event fee has been added.
     * @param  bool  $isIndividualEnrollment  Flag to indicate if the enrollment is individual.
     */
    private function addAthleteEnrollmentCosts(
        Event $event,
        AthleteEnrollment $athleteEnrollment,
        array &$breakdown,
        bool &$eventFeeAdded,
        bool $isIndividualEnrollment,
        array &$countedIndividuals
    ): void {
        $individualId = $athleteEnrollment->individual_id;

        // Add per-person price only once for individual enrollment
        if (! in_array($individualId, $countedIndividuals) && $athleteEnrollment->per_person_price > 0) {

            $breakdown[] = [
                'type' => 'Per Person Fee',
                'cost' => $athleteEnrollment->per_person_price,
                'pricing_id' => $athleteEnrollment->per_person_pricing_id,
            ];
            $countedIndividuals[] = $individualId;

            Log::info('Added per-person fee', [
                'individual_id' => $individualId,
                'fee' => $athleteEnrollment->per_person_price,
                'pricing_id' => $athleteEnrollment->per_person_pricing_id,
            ]);
        }

        // Add discipline price
        if ($athleteEnrollment->discipline_price > 0) {
            $breakdown[] = [
                'type' => 'Discipline Fee',
                'cost' => $athleteEnrollment->discipline_price,
                'pricing_id' => $athleteEnrollment->discipline_pricing_id,
                'discipline_id' => $athleteEnrollment->discipline_id,
            ];
        }

        // Add event fee (only once per enrollment)
        if (! $eventFeeAdded && $athleteEnrollment->event_fee > 0) {
            $breakdown[] = [
                'type' => 'Event Fee',
                'cost' => $athleteEnrollment->event_fee,
                'pricing_id' => $athleteEnrollment->event_fee_pricing_id,
            ];
            $eventFeeAdded = true;
        }
    }

    /**
     * Process other types of enrollments (e.g., coaches, officials).
     *
     * @param  Event  $event  The event being processed.
     * @param  Enrollment  $enrollment  The enrollment being processed.
     * @param  array  $breakdown  Reference to the cost breakdown array.
     * @param  EvtEventEnrollmentRoleEnum  $role  The role of the enrollment.
     */
    private function processOtherEnrollments(
        Event $event,
        Enrollment $enrollment,
        array &$breakdown,
        EvtEventEnrollmentRoleEnum $role
    ): void {
        $enrollmentField = $this->getEnrollmentField($role);
        foreach ($enrollment->$enrollmentField as $otherEnrollment) {

            // For organization events, we should use a default pricing if none exists
            if ($event->isOrganizationEvent()) {
                $pricing = $this->getPricing($event->id, null, $role, $otherEnrollment->pricing_id) ??
                    $this->createDefaultPricing($event, $role);
            } else {
                $pricing = $this->getPricing($event->id, null, $role, $otherEnrollment->pricing_id);
            }

            if ($pricing) {
                $breakdown[] = [
                    'type' => ucfirst(strtolower($role->value)) . ' Fee',
                    'cost' => $pricing->price,
                    'pricing_id' => $pricing->id,
                ];
            }
        }
    }

    /**
     * Create default pricing for usage in Organization events
     */
    private function createDefaultPricing(
        Event $event,
        EvtEventEnrollmentRoleEnum $role
    ): Pricing {
        return new Pricing([
            'event_id' => $event->id,
            'price' => 0,
            'enrollment_role' => $role->value,
            'is_active' => true,
            'price_type' => 'flat_fee',
        ]);
    }

    /**
     * Get the enrollment field name for a specific role.
     *
     * @param  EvtEventEnrollmentRoleEnum  $role  The role to get the field name for.
     * @return string The name of the enrollment field.
     *
     * @throws Exception If an unknown enrollment role is provided.
     */
    private function getEnrollmentField(EvtEventEnrollmentRoleEnum $role): string
    {
        return match ($role) {
            EvtEventEnrollmentRoleEnum::INDIVIDUAL => 'individualEnrollments',
            EvtEventEnrollmentRoleEnum::COACH => 'coachEnrollments',
            EvtEventEnrollmentRoleEnum::OFFICIAL => 'teamOfficialEnrollments',
            default => throw new Exception('Unknown enrollment role'),
        };
    }

    /**
     * Get pricing for a specific event, discipline, role, and pricing ID.
     *
     * @param  int  $eventId  The ID of the event.
     * @param  int|null  $disciplineId  The ID of the discipline (if applicable).
     * @param  EvtEventEnrollmentRoleEnum  $role  The role for the pricing.
     * @param  int|null  $pricingId  The ID of the specific pricing (if known).
     * @return Pricing|null The pricing model if found, null otherwise.
     */
    public function getPricing(
        int $eventId,
        ?int $disciplineId,
        EvtEventEnrollmentRoleEnum $role,
        ?int $pricingId
    ): ?Pricing {
        if ($pricingId) {
            return Pricing::active()->find($pricingId);
        }

        $pricing = $pricingId
            ? Pricing::active()->find($pricingId)
            : Pricing::active()
                ->where('event_id', $eventId)
                ->where('discipline_id', $disciplineId)
                ->where('enrollment_role', $role->value)
                ->first();

        return $pricing;
    }

    // Ensure price_type is determined correctly
    public function getPriceType(?int $pricingId): ?string
    {

        $pricing = Pricing::find($pricingId);

        return $pricing ? $pricing->price_type : null;
    }

    /**
     * Recalcilates and Updates the record
     **/
    public function recalculateAndUpdateEnrollmentCosts(Enrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment) {
            $this->recalculateAthleteEnrollments($enrollment);
            $this->recalculateOtherEnrollments($enrollment);
            $enrollment->refresh();
        });
    }

    private function recalculateAthleteEnrollments(Enrollment $enrollment): void
    {
        foreach ($enrollment->athleteEnrollments as $athleteEnrollment) {
            $costs = $this->recalculateAthleteEnrollmentCosts($athleteEnrollment);

            $updateData = [
                'per_person_price' => 0,
                'per_person_pricing_id' => null,
                'discipline_price' => 0,
                'discipline_pricing_id' => null,
                'event_fee' => 0,
                'event_fee_pricing_id' => null,
            ];

            foreach ($costs as $cost) {
                switch ($cost['type']) {
                    case 'Per Person Fee':
                        $updateData['per_person_price'] = $cost['cost'];
                        $updateData['per_person_pricing_id'] = $cost['pricing_id'];
                        break;
                    case 'Discipline Fee':
                        $updateData['discipline_price'] = $cost['cost'];
                        $updateData['discipline_pricing_id'] = $cost['pricing_id'];
                        break;
                    case 'Event Fee':
                        $updateData['event_fee'] = $cost['cost'];
                        $updateData['event_fee_pricing_id'] = $cost['pricing_id'];
                        break;
                }
            }

            $athleteEnrollment->update($updateData);
            $athleteEnrollment->calculateTotalPrice();
        }
    }

    private function recalculateOtherEnrollments(Enrollment $enrollment): void
    {
        $otherEnrollments = [
            'coachEnrollments',
            'refereeEnrollments',
            'teamOfficialEnrollments',
        ];

        foreach ($otherEnrollments as $enrollmentType) {
            foreach ($enrollment->$enrollmentType as $otherEnrollment) {
                $pricing = $this->getPricing(
                    $enrollment->event_id,
                    null,
                    $otherEnrollment->getEnrollmentRole(),
                    null
                );

                if ($pricing) {
                    $otherEnrollment->update([
                        'pricing_id' => $pricing->id,
                        'price' => $pricing->price,
                    ]);
                }
            }
        }
    }
}
