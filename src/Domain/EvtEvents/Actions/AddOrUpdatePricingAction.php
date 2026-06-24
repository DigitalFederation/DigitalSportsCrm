<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\DataTransferObjects\PricingData;
use Domain\EvtEvents\Models\Pricing;
use Exception;
use Illuminate\Support\Facades\DB;

class AddOrUpdatePricingAction
{
    /**
     * Handle the action of adding or updating pricing.
     *
     * @throws Exception
     */
    public function execute(PricingData $pricingData): Pricing
    {
        // Validate the provided data
        $validationResult = $pricingData->validate();
        if ($validationResult !== true) {
            throw new Exception('Validation failed: ' . json_encode($validationResult));
        }

        // Begin transaction
        DB::beginTransaction();

        // Add or update the pricing
        try {
            $pricing = Pricing::updateOrCreate(
                [
                    'id' => $pricingData->id,
                    'event_id' => $pricingData->eventId,
                ],
                [
                    'discipline_id' => $pricingData->disciplineId,
                    'enrollment_role' => $pricingData->enrollmentRole,
                    'start_date' => $pricingData->startDate,
                    'end_date' => $pricingData->endDate,
                    'price_type' => $pricingData->priceType,
                    'target_group' => $pricingData->targetGroup,
                    'price' => $pricingData->price,
                    'is_active' => $pricingData->isActive,
                    'pricing_option' => $pricingData->pricingOption,
                    'description' => $pricingData->description,
                ]
            );

            DB::commit();

            return $pricing;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
