<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\PackagePricing;
use Illuminate\Support\Facades\DB;

class DeletePackagePricingAction
{
    public function __invoke(PackagePricing $packagePricing): bool
    {
        return DB::transaction(function () use ($packagePricing) {
            return $packagePricing->delete();
        });
    }
}
