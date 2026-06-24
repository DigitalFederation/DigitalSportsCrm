<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\DataTransferObject\MembershipData;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;

class EditMembershipAction
{
    public function __invoke(MembershipData $data, int $id): bool
    {
        // If name is empty, use the name of the first plan.
        if (empty($data->name) && ! empty($data->plans)) {
            $firstPlanId = $data->plans[0];
            $firstPlan = MembershipPlan::find($firstPlanId);
            if ($firstPlan) {
                $data->name = $firstPlan->name;
            }
        }

        if (empty($data->current_term_ends_at)) {
            $calculateAction = new CalculateMembershipEndTermDateAction;
            $membershipPlans = MembershipPlan::whereIn('id', $data->plans)->get()->unique(fn (MembershipPlan $plan) => $plan->interval . ':' . $plan->interval_unit);
            $membershipPlan = $membershipPlans->first();

            // If we have only one plan, and we have interval and interval unit, we calculate the end term
            if ($membershipPlans->count() === 1 && $membershipPlan && ! empty($membershipPlan->interval) && ! empty($membershipPlan->interval_unit)) {
                $data->current_term_ends_at = $calculateAction(
                    $data->current_term_starts_at,
                    $data->current_term_ends_at,
                    $membershipPlan->interval,
                    $membershipPlan->interval_unit
                );
            }
        }

        $membership = Membership::find($id);
        $updated = $membership->update((array) $data);
        $membership->plans()->sync($data->plans);

        if ($updated) {
            activity('Membership')
                ->performedOn($membership)
                ->event('updated')
                ->log('Membership: '.$membership->name.', was updated to Federation');
        }

        return $updated;
    }
}
