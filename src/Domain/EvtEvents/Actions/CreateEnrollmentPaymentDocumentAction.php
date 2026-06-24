<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\Documents\Actions\CreateDocumentWithDetailsAction;
use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Documents\Models\Document;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Illuminate\Support\Facades\Log;

class CreateEnrollmentPaymentDocumentAction
{
    public function execute(
        Event $event,
        Enrollment $enrollment,
        string $enrollable_id,
        string $enrollable_type,
        array $selectedIndividuals,
        float $totalCost,
        ?int $pricingId
    ): ?Document {

        Log::info('Starting document creation for Event', [
            'event_id' => $event->id,
            'enrollment_id' => $enrollment->id,
            'enrollable_id' => $enrollable_id,
            'enrollable_type' => $enrollable_type,
            'total_cost' => $totalCost,
        ]);

        $documentDetailsData = $this->generateDocumentDetailsData(
            $selectedIndividuals,
            $event,
            $enrollment,
            $totalCost,
            $pricingId
        );

        $document = (new CreateDocumentWithDetailsAction)($documentDetailsData, 'ORD', $enrollable_id, $enrollable_type);

        if ($document === null) {
            Log::info('No document created - total value is zero', [
                'event_id' => $event->id,
                'enrollment_id' => $enrollment->id,
                'enrollable_id' => $enrollable_id,
                'enrollable_type' => $enrollable_type,
            ]);

            return null;
        }

        Log::info('Finished document creation', [
            'document_id' => $document->id,
            'total_cost' => $document->total_value,
        ]);

        return $document;
    }

    private function generateDocumentDetailsData(
        array $selectedIndividuals,
        Event $event,
        Enrollment $enrollment,
        float $totalCost,
        ?int $pricingId
    ): array {
        $documentDetailsData = [];
        $groupedEnrollments = $this->groupEnrollmentsByPricing($selectedIndividuals, $event);
        $reference = $event->competition?->moloni_reference ?? $event->moloni_reference;

        $this->addEventFeeIfExists($event, $enrollment, $documentDetailsData, $reference);

        $this->addPerPersonFees($groupedEnrollments, $enrollment, $documentDetailsData, $reference);

        $this->addOtherFees($groupedEnrollments, $event, $enrollment, $documentDetailsData, $reference);
        $this->addDisciplineCosts($selectedIndividuals, $event, $enrollment, $documentDetailsData, $reference);

        return $documentDetailsData;
    }

    private function addEventFeeIfExists(Event $event, Enrollment $enrollment, array &$documentDetailsData, ?string $reference = null): void
    {
        $eventFee = Pricing::active()
            ->where('event_id', $event->id)
            ->where('price_type', EvtEventFeeTypeEnum::EVENT_FEE->value)
            ->first();

        if ($eventFee) {
            $documentDetailsData[] = $this->createDocumentDetailData(
                $enrollment,
                $eventFee->description . ' - ' . $event->name . ' (Event Fee)',
                $eventFee->price,
                1,
                $reference
            );
        }
    }

    private function addPerPersonFees(array $groupedEnrollments, Enrollment $enrollment, array &$documentDetailsData, ?string $reference = null): void
    {
        if (isset($groupedEnrollments['per_person'])) {
            $perPersonPricing = $groupedEnrollments['per_person']['pricing'];
            $uniqueIndividuals = collect($groupedEnrollments['per_person']['individuals'])
                ->unique('individual_id')
                ->count();
            Log::debug('Processing per-person fees', [
                'unique_individuals' => $uniqueIndividuals,
                'per_person_price' => $perPersonPricing->price ?? 0,
            ]);
            $description = ($perPersonPricing->description ?? 'Per Person Fee') . ' - ' . $enrollment->event->name;
            if ($uniqueIndividuals > 1) {
                $description .= " (x{$uniqueIndividuals} enrollments)";
            }

            $documentDetailsData[] = $this->createDocumentDetailData(
                $enrollment,
                $description,
                $perPersonPricing->price ?? 0,
                $uniqueIndividuals,
                $reference
            );

            Log::info('Added per-person fees to document', [
                'unique_individuals' => $uniqueIndividuals,
                'total_fee' => ($perPersonPricing->price ?? 0) * $uniqueIndividuals,
            ]);
        }
    }

    private function addOtherFees(array $groupedEnrollments, Event $event, Enrollment $enrollment, array &$documentDetailsData, ?string $reference = null): void
    {
        foreach ($groupedEnrollments as $pricingId => $data) {
            if ($pricingId === 'per_person') {
                continue;
            }

            $pricing = $data['pricing'];
            $quantity = count($data['individuals']);

            // Build the description conditionally
            $description = $pricing->description ? $pricing->description . ' - ' . $event->name : $event->name;

            if ($pricing->price_type === EvtEventFeeTypeEnum::FLAT_FEE->value) {
                $description .= ' (x' . $quantity . ' enrollments)';
                $quantity = 1;
            } elseif ($quantity > 1) {
                $description .= " (x{$quantity} enrollments)";
            }

            $documentDetailsData[] = $this->createDocumentDetailData(
                $enrollment,
                $description,
                $pricing->price,
                $quantity,
                $reference
            );
        }
    }

    private function addDisciplineCosts(array $selectedIndividuals, Event $event, Enrollment $enrollment, array &$documentDetailsData, ?string $reference = null): void
    {
        $disciplineCosts = $this->calculateDisciplineCosts($selectedIndividuals, $event);

        foreach ($disciplineCosts as $cost) {
            $documentDetailsData[] = $this->createDocumentDetailData(
                $enrollment,
                $cost['description'],
                $cost['unit_value'],
                $cost['quantity'],
                $reference
            );
        }
    }

    private function calculateDisciplineCosts(array $selectedIndividuals, Event $event): array
    {
        $disciplineCosts = [];

        foreach ($selectedIndividuals as $individual) {
            if (
                $individual['role'] === EvtEventEnrollmentRoleEnum::ATHLETE->value &&
                isset($individual['discipline_price']) &&
                $individual['discipline_price'] > 0
            ) {
                $disciplineId = $individual['discipline_id'];
                if (! isset($disciplineCosts[$disciplineId])) {
                    $disciplineCosts[$disciplineId] = [
                        'description' => 'Price per Discipline - ' . $event->name . ' (Discipline)',
                        'unit_value' => $individual['discipline_price'],
                        'quantity' => 0,
                    ];
                }
                $disciplineCosts[$disciplineId]['quantity']++;
            }
        }

        return $disciplineCosts;
    }

    private function createDocumentDetailData(Enrollment $enrollment, string $description, float $unitValue, int $quantity, ?string $reference = null): DocumentDetailData
    {
        return DocumentDetailData::fromArray([
            'owner_id' => $enrollment->id,
            'owner_type' => Enrollment::class,
            'description' => $description,
            'reference' => $reference,
            'unit_value' => $unitValue,
            'quantity' => $quantity,
            'customer_name' => '',
            'tax_percentage' => 0,
        ]);
    }

    private function groupEnrollmentsByPricing(array $selectedIndividuals, Event $event): array
    {
        $groupedEnrollments = [];
        foreach ($selectedIndividuals as $individual) {
            $pricingId = $individual['pricing_id'] ?? $this->getDefaultPricingId($individual, $event);
            $pricing = Pricing::find($pricingId);

            if (! $pricing) {
                Log::debug("Pricing not found for ID: {$pricingId}");

                continue;
            }

            // Use the actual Pricing ID to keep each unique pricing separated:
            $key = $pricing->id;

            if (! isset($groupedEnrollments[$key])) {
                $groupedEnrollments[$key] = [
                    'pricing' => $pricing,
                    'individuals' => [],
                ];
            }

            // Use individual_id for uniqueness check
            if (! in_array($individual['individual_id'], array_column($groupedEnrollments[$key]['individuals'], 'individual_id'))) {
                $groupedEnrollments[$key]['individuals'][] = $individual;
            }
        }

        Log::debug('Grouped Enrollments:', $groupedEnrollments);

        return $groupedEnrollments;
    }

    private function getDefaultPricingId(array $individual, Event $event): ?int
    {
        $role = EvtEventEnrollmentRoleEnum::from($individual['role']);
        $pricingService = new EnrollmentsCostCalculationService;
        $pricing = $pricingService->getPricing($event->id, $individual['discipline_id'] ?? null, $role, null);

        return $pricing->id ?? null;
    }
}
