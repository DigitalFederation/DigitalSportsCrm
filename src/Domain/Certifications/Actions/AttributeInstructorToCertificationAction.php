<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\CertificationAttributedInstructor;

class AttributeInstructorToCertificationAction
{
    public function __invoke(string $certification_attributed_id, string $individual_id, bool $is_main = false): void
    {
        CertificationAttributedInstructor::create([
            'attributed_id' => $certification_attributed_id,
            'individual_id' => $individual_id,
            'is_main' => $is_main,
        ]);
    }
}
