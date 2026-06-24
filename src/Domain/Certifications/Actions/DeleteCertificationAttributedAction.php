<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\PendingCertificationAttributedState;

class DeleteCertificationAttributedAction
{
    public function __invoke(string $id)
    {
        $deleted = false;
        $certificationAttributed = CertificationAttributed::find($id);
        if (auth()->user()->federations()->exists()) {
            if ($certificationAttributed->status_class == PendingCertificationAttributedState::class) {
                $deleted = $certificationAttributed->delete();
            }
        } else {
            $deleted = $certificationAttributed->delete();
        }

        return $deleted;
    }
}
