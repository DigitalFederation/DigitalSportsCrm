<?php

namespace Domain\Insurance\States;

use Domain\Insurance\Models\Insurance;
use Exception;

class ActiveToExpiredInsuranceTransition
{
    /**
     * @throws Exception
     */
    public function __invoke(Insurance $insurance): Insurance
    {
        if ($insurance->status_class !== ActiveInsuranceState::class) {
            throw new Exception('Insurance must be in Active state to expire');
        }

        $insurance->status_class = ExpiredInsuranceState::class;
        $insurance->save();

        activity('Insurance')
            ->performedOn($insurance)
            ->event('expired')
            ->log('Insurance expired.');

        return $insurance;
    }
}
