<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\States\CanceledCertificationAttributedState;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;

class CheckIndividualHasCertificationAction
{
    public function __invoke(string $individual_id, int $certification_id)
    {
        $check = Individual::where('id', $individual_id)
            ->whereHas('certificationsAttributed', function (Builder $query) use ($certification_id) {
                return $query->whereNot('status_class', CanceledCertificationAttributedState::class)->where('certification_id', $certification_id);
            })->exists();

        return $check;
    }
}
