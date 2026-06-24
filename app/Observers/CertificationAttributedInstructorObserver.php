<?php

namespace App\Observers;

use Domain\Certifications\Models\CertificationAttributedInstructor;
use Domain\Individuals\Models\Individual;

class CertificationAttributedInstructorObserver
{
    public function creating(CertificationAttributedInstructor $attributedInstructor)
    {
        $individual = Individual::find($attributedInstructor->individual_id);
        if ($individual) {
            $attributedInstructor->instructor_name = $individual->full_name;
        }
    }
}
