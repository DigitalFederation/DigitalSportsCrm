<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\DataTransferObject\AffiliationPlanData;
use Domain\Memberships\Models\AffiliationPlan;

class UpdateAffiliationPlanAction
{
    public function __invoke(AffiliationPlan $plan, AffiliationPlanData $data): AffiliationPlan
    {
        $plan->update([
            'federation_id' => $data->federation_id,
            'name' => $data->name,
            'description' => $data->description,
            'duration_months' => $data->duration_months,
            'individual_fee' => $data->individual_fee ?? null,
            'entity_fee' => $data->entity_fee ?? null,
            'type' => $data->type,
            'vat_rate' => $data->vat_rate,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'is_validation_plan' => $data->is_validation_plan,
        ]);

        return $plan->fresh();
    }
}
