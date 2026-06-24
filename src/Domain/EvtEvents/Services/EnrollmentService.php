<?php

namespace Domain\EvtEvents\Services;

use App\Enums\EvtEventFeeTypeEnum;
use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Actions\GetPricesFromEventAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Collection;

class EnrollmentService
{
    public function getActivePricing(Event $event): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Pricing> $allPrices */
        $allPrices = (new GetPricesFromEventAction)->execute($event->id, null, null);
        // Filter fixed prices like EVENT_FEE from the list
        $filteredPrices = $allPrices->filter(function (Pricing $price) {
            return $price->price_type != EvtEventFeeTypeEnum::EVENT_FEE->value;
        });

        return $filteredPrices;
    }

    public function getDisciplines(Event $event, string $individualId): Collection
    {
        $individual = Individual::find($individualId);
        if (! $individual) {
            return collect(); // Return an empty collection if the individual is not found
        }

        $getDisciplines = new GetDisciplinesFromEventAction;
        $allDisciplines = collect($getDisciplines->execute($event)['disciplines'] ?? []);

        // Example: Filter disciplines by gender
        $gender = $individual->gender; // Assuming `gender` is a column in the `individuals` table

        $filteredDisciplines = $allDisciplines->filter(function ($discipline) use ($gender) {
            return $discipline->attributes->contains(function ($attribute) use ($gender) {
                return $attribute->name == 'gender' && $attribute->value == $gender;
            });
        });

        $enrollments = $event->enrollments()
            ->where('enrollable_type', Individual::class)
            ->where('enrollable_id', $individualId)
            ->with('athleteEnrollments.discipline')
            ->first();

        $enrolledDisciplines = $enrollments ? $enrollments->athleteEnrollments->pluck('discipline.id') : collect();

        return $filteredDisciplines->reject(function ($discipline) use ($enrolledDisciplines) {
            return $enrolledDisciplines->contains($discipline->id);
        });
    }

    public function calculateTotalCost(Event $event, Collection $selectedEnrollments): float
    {
        return (new EnrollmentsCostCalculationService)->calculateTotalCost($event, $selectedEnrollments);
    }

    public function addEventFee(Collection $documentDetailsData, Event $event): Collection
    {
        $eventFee = Pricing::active()
            ->where('event_id', $event->id)
            ->where('price_type', EvtEventFeeTypeEnum::EVENT_FEE->value)
            ->first();

        if ($eventFee) {
            $documentDetailsData[] = DocumentDetailData::fromArray([
                'owner_id' => $event->id,
                'owner_type' => Event::class,
                'description' => 'Event Fee - ' . $event->name,
                'unit_value' => $eventFee->price,
                'quantity' => 1,
                'customer_name' => '',
                'tax_percentage' => 0,
            ]);
        }

        return $documentDetailsData;
    }
}
