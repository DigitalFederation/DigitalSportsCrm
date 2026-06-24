<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\EvtEvents\States\PendingCoachEnrollmentState;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Domain\EvtEvents\States\PendingTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ManualGenerateAthleteEnrollmentPaymentAction
{
    public function __construct(
        private EnrollmentsCostCalculationService $costCalculationService,
        private CreateIndividualEnrollmentOrderAction $createIndividualEnrollmentOrderAction,
        private CreateEnrollmentPaymentDocumentAction $createEnrollmentPaymentDocumentAction
    ) {}

    /**
     * Invoke the action to generate a payment document for athlete enrollment.
     *
     * @param  Enrollment  $enrollment  The enrollment to generate the payment document for.
     * @return Document|null The generated payment document, or null if the cost is invalid.
     */
    public function __invoke(Enrollment $enrollment): ?Document
    {
        $excludedAthleteStatuses = [
            EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
            EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
            EvtAthleteEnrollmentStatusEnum::CANCELED->value,
        ];

        $this->loadFilteredRelationships($enrollment, $excludedAthleteStatuses);
        $event = $enrollment->event;

        // Recalculate and update enrollment costs
        $this->costCalculationService->recalculateAndUpdateEnrollmentCosts($enrollment);

        // Re-apply filters after refresh() inside recalculate wipes them
        $this->loadFilteredRelationships($enrollment, $excludedAthleteStatuses);

        // Get updated selected individuals
        $selectedIndividuals = $this->prepareSelectedIndividuals($enrollment);

        // Calculate total cost
        $totalCost = $this->costCalculationService->calculateTotalCost($event, new EloquentCollection([$enrollment]));

        if ($this->isCostInvalid($totalCost)) {
            return null;
        }

        if ($this->isIndividualEnrollment($enrollment)) {
            return $this->processIndividualEnrollment($event, $enrollment, $selectedIndividuals);
        } elseif ($this->isFederationEnrollment($enrollment)) {
            return $this->processFederationEnrollment($event, $enrollment, $selectedIndividuals, $totalCost);
        } else {
            return $this->processEntityEnrollment($event, $enrollment, $selectedIndividuals, $totalCost);
        }
    }

    /**
     * Prepare the selected individuals data from the enrollment.
     *
     * @param  Enrollment  $enrollment  The enrollment to prepare data from.
     * @return Collection A collection of selected individuals data.
     */
    private function prepareSelectedIndividuals(Enrollment $enrollment): Collection
    {
        return $enrollment->athleteEnrollments
            ->filter(fn ($athleteEnrollment) => $athleteEnrollment->individual !== null)
            ->map(fn ($athleteEnrollment) => [
                'id' => $athleteEnrollment->id,
                'individual_id' => $athleteEnrollment->individual_id,
                'name' => $athleteEnrollment->individual->name,
                'surname' => $athleteEnrollment->individual->surname,
                'role' => 'ATHLETE',
                'discipline_id' => $athleteEnrollment->discipline_id,
                'pricing_id' => $athleteEnrollment->per_person_pricing_id,
                'price' => $athleteEnrollment->per_person_price,
                'discipline_price' => $athleteEnrollment->discipline_price,
            ]);
    }

    /**
     * Check if the calculated total cost is invalid (zero or negative).
     *
     * @param  float  $totalCost  The total cost to check.
     * @return bool True if the cost is invalid, false otherwise.
     */
    private function isCostInvalid(float $totalCost): bool
    {
        return $totalCost <= 0;
    }

    /**
     * Determine if the enrollment is an federation enrollment.
     *
     * @param  Enrollment  $enrollment  The enrollment to check.
     * @return bool True if it's an individual enrollment, false if it's a federation enrollment.
     */
    private function isFederationEnrollment(Enrollment $enrollment): bool
    {
        return $enrollment->enrollable_type === Federation::class;
    }

    /**
     * Determine if the enrollment is an individual enrollment.
     *
     * @param  Enrollment  $enrollment  The enrollment to check.
     * @return bool True if it's an individual enrollment, false if it's a federation enrollment.
     */
    private function isIndividualEnrollment(Enrollment $enrollment): bool
    {
        return $enrollment->enrollable_type === Individual::class;
    }

    /**
     * Process an individual enrollment to create a payment document.
     *
     * @param  mixed  $event  The event associated with the enrollment.
     * @param  Enrollment  $enrollment  The enrollment to process.
     * @param  array|Collection  $selectedIndividuals  The selected individuals data.
     * @return Document The generated payment document.
     */
    private function processIndividualEnrollment(
        $event,
        Enrollment $enrollment,
        array|Collection $selectedIndividuals
    ): Document {

        return $this->createIndividualEnrollmentOrderAction->execute(
            $event,
            $enrollment,
            $enrollment->enrollable_id,
            Individual::class,
            new EloquentCollection($selectedIndividuals),
            [$enrollment->id => $enrollment->pricing_id]
        );
    }

    /**
     * Process a federation enrollment to create a payment document.
     *
     * @param  mixed  $event  The event associated with the enrollment.
     * @param  Enrollment  $enrollment  The enrollment to process.
     * @param  array|Collection  $selectedIndividuals  The selected individuals data.
     * @param  float  $totalCost  The total cost of the enrollment.
     * @return Document The generated payment document.
     */
    private function processFederationEnrollment(
        $event,
        Enrollment $enrollment,
        array|Collection $selectedIndividuals,
        float $totalCost
    ): Document {
        return $this->createEnrollmentPaymentDocumentAction->execute(
            $event,
            $enrollment,
            $enrollment->enrollable_id,
            Federation::class,
            $selectedIndividuals->toArray(),
            $totalCost,
            null
        );
    }

    /**
     * Process a entity enrollment to create a payment document.
     *
     * @param  mixed  $event  The event associated with the enrollment.
     * @param  Enrollment  $enrollment  The enrollment to process.
     * @param  array|Collection  $selectedIndividuals  The selected individuals data.
     * @param  float  $totalCost  The total cost of the enrollment.
     * @return Document The generated payment document.
     */
    private function processEntityEnrollment(
        $event,
        Enrollment $enrollment,
        array|Collection $selectedIndividuals,
        float $totalCost
    ): Document {
        return $this->createEnrollmentPaymentDocumentAction->execute(
            $event,
            $enrollment,
            $enrollment->enrollable_id,
            Entity::class,
            $selectedIndividuals->toArray(),
            $totalCost,
            null
        );
    }

    /**
     * Load enrollment relationships with status filters applied.
     *
     * @param  array<string>  $excludedAthleteStatuses
     */
    private function loadFilteredRelationships(Enrollment $enrollment, array $excludedAthleteStatuses): void
    {
        $enrollment->load([
            'event',
            'athleteEnrollments' => fn ($query) => $query->whereNotIn('status_class', $excludedAthleteStatuses),
            'athleteEnrollments.individual',
            'athleteEnrollments.event',
            'coachEnrollments' => fn ($query) => $query->where('status_class', PendingCoachEnrollmentState::class),
            'teamOfficialEnrollments' => fn ($query) => $query->where('status_class', PendingTeamOfficialEnrollmentState::class),
            'refereeEnrollments' => fn ($query) => $query->where('status_class', PendingRefereeEnrollmentState::class),
        ]);
    }
}
