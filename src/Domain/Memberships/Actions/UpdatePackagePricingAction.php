<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\DataTransferObject\PackagePricingData;
use Domain\Memberships\Models\PackagePricing;
use Illuminate\Support\Facades\DB;

class UpdatePackagePricingAction
{
    public function __invoke(PackagePricing $packagePricing, PackagePricingData $data): PackagePricing
    {
        return DB::transaction(function () use ($packagePricing, $data) {
            $packagePricing->update([
                'package_id' => $data->membership_package_id,
                'price' => $data->price,
            ]);

            return $packagePricing->fresh();
        });
    }
}
