<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\DataTransferObject\PackagePricingData;
use Domain\Memberships\Models\PackagePricing;
use Illuminate\Support\Facades\DB;

class CreatePackagePricingAction
{
    public function __invoke(PackagePricingData $data): PackagePricing
    {
        return DB::transaction(function () use ($data) {
            return PackagePricing::create([
                'package_id' => $data->membership_package_id,
                'price' => $data->price,
            ]);
        });
    }
}
