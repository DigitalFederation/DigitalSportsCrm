<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\Models\License;
use Exception;

class DeleteLicenseAction
{
    /**
     * @throws Exception
     */
    public function __invoke($id)
    {
        $license = License::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
            ->where(compact('id'))
            ->doesntHave('licensesAttributed')
            ->doesntHave('certifications')
            ->first();

        if (! empty($license)) {
            return $license->delete();
        } else {
            throw new Exception(__('This license is referenced in another table.'), 801);
        }
    }
}
