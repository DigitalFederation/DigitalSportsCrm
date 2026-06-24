<?php

namespace Domain\EvtEvents\Actions;

use Domain\Documents\Actions\CreateDocumentWithDetailsAction;
use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Documents\Models\Document;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CreateIndividualEnrollmentOrderAction
{
    private $costCalculationService;

    public function __construct(EnrollmentsCostCalculationService $costCalculationService)
    {
        $this->costCalculationService = $costCalculationService;
    }

    public function execute(
        Event $event,
        Enrollment $enrollment,
        string $ownerId,
        string $ownerType,
        EloquentCollection $selectedIndividuals,
        array $individualPricingTiers,
        ?array $recalculatedCosts = null
    ): ?Document {

        if ($recalculatedCosts) {
            $costBreakdown = $recalculatedCosts;
        } else {
            // Use the original cost calculation method for initial enrollments
            $pendingEnrollments = new EloquentCollection([$enrollment]);
            $costBreakdown = $this->costCalculationService->getCostBreakdown(
                $event,
                $pendingEnrollments,
                true,
                $selectedIndividuals->toArray()
            );
        }

        $documentDetailsData = $this->generateDocumentDetailsData(
            $costBreakdown,
            $enrollment->id,
            $selectedIndividuals,
            $event
        );

        return (new CreateDocumentWithDetailsAction)(
            $documentDetailsData,
            'ORD',
            $ownerId,
            $ownerType
        );
    }

    private function generateDocumentDetailsData(
        array $costBreakdown,
        string $enrollmentId,
        EloquentCollection $selectedIndividuals,
        Event $event
    ): array {
        $documentDetailsData = [];
        $reference = $event->competition?->moloni_reference ?? $event->moloni_reference;

        foreach ($costBreakdown as $item) {
            if ($item['type'] === 'Per Person Fee') {
                // Generate individual entries for per-person fees
                foreach ($selectedIndividuals as $individual) {
                    $documentDetailsData[] = DocumentDetailData::fromArray([
                        'owner_id' => $enrollmentId,
                        'owner_type' => Enrollment::class,
                        'description' => $this->generateDescription(
                            'Per Person Fee',
                            new EloquentCollection([$individual]),
                            $event
                        ),
                        'reference' => $reference,
                        'unit_value' => $item['cost'] / count($selectedIndividuals),
                        'quantity' => 1,
                        'tax_percentage' => 0,
                    ]);
                }
            } else {
                // For flat fees, generate single entry with all names
                $documentDetailsData[] = DocumentDetailData::fromArray([
                    'owner_id' => $enrollmentId,
                    'owner_type' => Enrollment::class,
                    'description' => $this->generateFlatFeeDescription(
                        $item['type'],
                        $selectedIndividuals,
                        $event
                    ),
                    'reference' => $reference,
                    'unit_value' => $item['cost'],
                    'quantity' => 1,
                    'tax_percentage' => 0,
                ]);
            }
        }

        return $documentDetailsData;
    }

    private function generateFlatFeeDescription(
        string $type,
        EloquentCollection $individuals,
        Event $event
    ): string {
        $names = $individuals->map(fn ($i) => "{$i['name']} {$i['surname']}")->join(', ');

        return "{$type} for {$names} - {$event->name}";
    }

    private function generateDescription(
        string $type,
        EloquentCollection $selectedIndividuals,
        Event $event
    ): string {
        $description = '';

        foreach ($selectedIndividuals as $individual) {
            $individualName = $individual['name'];
            $individualSurname = $individual['surname'];
            $disciplineId = $individual['discipline_id'] ?? null;
            $disciplineName = $disciplineId ? Discipline::find($disciplineId)->name : '';

            if ($type === 'Per Person Fee') {
                $description = "Per Person Fee for {$individualName} {$individualSurname} - {$event->name}";
            } elseif ($type === 'Discipline Fee') {
                $description = "Discipline Fee for {$individualName} {$individualSurname} in {$disciplineName} - {$event->name}";
            } elseif ($type === 'Event Fee') {
                $description = "Event Fee for {$individualName} {$individualSurname} - {$event->name}";
            } elseif ($type === 'Individual Fee') {
                $description = "Event Fee for {$individualName} {$individualSurname} - {$event->name}";
            }
        }

        return $description;
    }
}
