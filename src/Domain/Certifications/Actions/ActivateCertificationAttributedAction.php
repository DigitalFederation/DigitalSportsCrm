<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\PendingToActiveCertificationAttributedTransition;
use Domain\Individuals\Actions\AssociateIndividualToProfessionalRoleAction;
use Domain\Users\Actions\SyncUserRolesAction;

/**
 * Class ActivateCertificationAttributedAction
 *
 * Responsible for activating a certification.
 * This action can be performed by different types of users, such as international, FEDERATION, or INSTRUCTOR.
 */
class ActivateCertificationAttributedAction
{
    /**
     * Activates a given certification.
     *
     * This method will transition a certification from a 'pending' state to an 'active' state.
     * Additionally, it will sync user roles and associate the individual to a professional role if applicable.
     *
     * @param  CertificationAttributed  $certificationAttributed  The certification that needs to be activated.
     * @return CertificationAttributed Returns the activated certification.
     */
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {
        $certificationAttributed->load('certification', 'individual');

        $pendingToActiveTransition = new PendingToActiveCertificationAttributedTransition;
        $syncUserRolesAction = new SyncUserRolesAction;
        $professionalRoleAction = new AssociateIndividualToProfessionalRoleAction;

        $individual = $certificationAttributed->individual()->firstOrFail();

        $user = $individual->user;
        $certificationAttributed->activated_at = now();
        $activateAction = $pendingToActiveTransition($certificationAttributed);
        $syncUserRolesAction->execute($user);

        if (! empty($certificationAttributed->certification->professional_role_id)) {
            $professionalRoleAction($individual, $certificationAttributed->certification->professional_role_id);
        }

        return $activateAction;
    }
}
