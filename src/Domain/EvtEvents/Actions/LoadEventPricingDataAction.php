<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\EvtEvents\Models\Event;
use Illuminate\Support\Collection;

class LoadEventPricingDataAction
{
    /**
     * Execute the action to load pricing data for the given event and enrollment type.
     *
     * @param  Event  $event  The event for which pricing data is to be loaded.
     * @param  int|null  $disciplineId  The discipline ID to filter pricing data, if applicable.
     * @param  EvtEventEnrollmentRoleEnum  $enrollmentType  The enrollment type for which pricing data is to be loaded.
     * @return array An array containing the filtered pricing data and selected pricing IDs.
     */
    public function execute(Event $event, ?int $disciplineId, EvtEventEnrollmentRoleEnum $enrollmentType): array
    {
        $pricingData = (new GetPricesFromEventAction)->execute(
            $event->id,
            $disciplineId,
            $enrollmentType
        );

        // Filter and select pricing IDs
        $filteredPricingData = $this->filterPricingData($pricingData, $enrollmentType);

        $selectedPricingIds = $this->selectPricingIds($filteredPricingData, $disciplineId);

        return [
            'pricingData' => $filteredPricingData,
            'selectedPricingIds' => $selectedPricingIds,
        ];
    }

    /**
     * Filter pricing data based on enrollment role and price type.
     */
    private function filterPricingData(Collection $pricingData, EvtEventEnrollmentRoleEnum $enrollmentType): Collection
    {
        return $pricingData->filter(function ($pricing) use ($enrollmentType) {
            $roleMatch = $pricing->enrollment_role === null || $pricing->enrollment_role === $enrollmentType->value;
            $validPriceType = in_array($pricing->price_type, [
                EvtEventFeeTypeEnum::PER_PERSON->value,
                EvtEventFeeTypeEnum::PER_DISCIPLINE->value,
                EvtEventFeeTypeEnum::EVENT_FEE->value,
                EvtEventFeeTypeEnum::FLAT_FEE->value,
            ]);

            return $roleMatch && $validPriceType;
        });
    }

    /**
     * Select pricing IDs for each price type.
     */
    private function selectPricingIds(Collection $filteredPricingData, ?int $disciplineId): array
    {
        $pricingIds = [];

        $pricingIds['perPerson'] = $filteredPricingData->where('price_type', EvtEventFeeTypeEnum::PER_PERSON->value)->pluck('id');
        $pricingIds['discipline'] = $filteredPricingData->where('price_type', EvtEventFeeTypeEnum::PER_DISCIPLINE->value)
            ->where('discipline_id', $disciplineId)
            ->pluck('id');
        $pricingIds['eventFee'] = $filteredPricingData->where('price_type', EvtEventFeeTypeEnum::EVENT_FEE->value)->pluck('id');

        return $pricingIds;
    }
}
