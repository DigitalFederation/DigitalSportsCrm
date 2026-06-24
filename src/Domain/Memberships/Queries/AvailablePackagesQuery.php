<?php

namespace Domain\Memberships\Queries;

use App\Enums\MembershipTargetType;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\MembershipPackage;

class AvailablePackagesQuery
{
    public function execute(Individual $individual)
    {
        return MembershipPackage::query()
            ->with(['affiliationPlans', 'insurancePlans'])
            ->where('is_active', true)
            ->where('target_type', MembershipTargetType::INDIVIDUAL)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('affiliationPlans')
            ->whereHas('federations', function ($query) use ($individual) {
                $query->whereIn('id', $individual->federations->pluck('id'));
            })
            ->get();
    }
}
