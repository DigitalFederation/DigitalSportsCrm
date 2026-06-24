<?php

namespace Domain\Insurance\Actions;

use Domain\Insurance\Models\Insurance;
use Domain\Insurance\States\ActiveToExpiredInsuranceTransition;

class ExpireInsuranceAction
{
    public function __invoke(Insurance $insurance): void
    {
        $transition = new ActiveToExpiredInsuranceTransition;
        $transition($insurance);
    }
}
