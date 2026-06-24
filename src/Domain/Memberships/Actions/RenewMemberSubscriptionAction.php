<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;

class RenewMemberSubscriptionAction
{
    public function __invoke(MemberSubscription $subscription): MemberSubscription
    {
        $newEndDate = $this->calculateNewEndDate($subscription);

        $subscription->update([
            'end_date' => $newEndDate,
            'status_class' => ActiveMemberSubscriptionState::class,
        ]);

        $this->renewAffiliation($subscription);
        $this->renewInsurance($subscription);

        return $subscription;
    }

    private function calculateNewEndDate(MemberSubscription $subscription): \Carbon\Carbon
    {
        return $subscription->end_date?->copy()->addYear() ?? now()->addYear();
    }

    private function renewAffiliation(MemberSubscription $subscription): void
    {
        // Implementation for renewing Affiliation
    }

    private function renewInsurance(MemberSubscription $subscription): void
    {
        // Implementation for renewing Insurance
    }
}
