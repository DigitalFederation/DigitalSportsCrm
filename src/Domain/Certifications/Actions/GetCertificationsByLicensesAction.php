<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\Certification;
use Domain\Licenses\Models\License;
use Illuminate\Support\Collection;

class GetCertificationsByLicensesAction
{
    public function __invoke(?Collection $licenses)
    {
        // Return all Certifications associcated with above license
        // Collection choose only certifications
        if (! empty($licenses)) {
            $certifications = Certification::whereIn('license_id', $licenses->pluck('id'))->get();
        }

        return $certifications ?? null;
    }
}
