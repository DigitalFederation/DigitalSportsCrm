<?php

namespace Domain\Insurance\Actions;

use Domain\Insurance\DataTransferObject\InsuranceData;
use Domain\Insurance\Models\Insurance;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateInsuranceAction
{
    /**
     * Update an existing insurance record.
     *
     * This action updates an insurance record with the provided data. It performs
     * the update within a database transaction to ensure data integrity.
     *
     * @param  Insurance  $insurance  The insurance model to be updated.
     * @param  InsuranceData  $data  The data transfer object containing the new insurance data.
     * @return Insurance The updated insurance model.
     *
     * @throws Exception If the update fails or the transaction cannot be committed.
     */
    public function __invoke(Insurance $insurance, InsuranceData $data): Insurance
    {

        try {
            DB::beginTransaction();

            $updateData = [
                'start_date' => $data->start_date,
                'end_date' => $data->end_date,
                'is_external' => $data->is_external,
            ];

            if ($data->member_type === 'individual' && $data->individual_fee !== null) {
                $updateData['individual_fee'] = $data->individual_fee;
            } elseif ($data->member_type === 'entity' && $data->entity_fee !== null) {
                $updateData['entity_fee'] = $data->entity_fee;
            }

            $insurance->update($updateData);

            // If the insurance plan is not a group plan, update the policy number
            if (! $insurance->insurancePlan->isGroupPlan() && $data->policy_number !== null) {
                $insurance->update(['policy_number' => $data->policy_number]);
            }
            // Perform any additional logic or related updates here

            DB::commit();

            // Log the update
            activity()
                ->performedOn($insurance)
                ->withProperties(['insurance_id' => $insurance->id])
                ->log('Insurance updated');

            return $insurance->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to update insurance: ' . $e->getMessage());
        }
    }
}
