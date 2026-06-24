<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\Certification;
use Exception;

class DeleteCertificationAction
{
    public function __invoke(int $id)
    {
        $certification = Certification::with('childs', 'certificationsAttributed')->findOrFail($id);

        if (! empty($certification->certificationsAttributed->toArray())) {
            throw new Exception(__('To delete this certification, you must delete all the certification attributed'));
        }

        if (! empty($certification->childs->toArray())) {
            throw new Exception(__('To delete this certification, you must delete all the certification childs'));
        }

        return $certification->delete();
    }
}
