<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveToExpiredCertificationAttributedTransition;
use Domain\Users\Actions\SyncUserEntityCommitteeAction;
use Domain\Users\Actions\SyncUserRolesAction;

class ExpireCertificationAttributedAction
{
    public function __invoke(CertificationAttributed $certification): void
    {
        $certification->loadMissing(['entity.users', 'individual.user']);

        $activeToExpired = new ActiveToExpiredCertificationAttributedTransition;
        $certification = $activeToExpired($certification);

        $entity = $certification->getRelation('entity');
        $individual = $certification->getRelation('individual');

        if ($certification->entity_id && $entity) {
            $syncAction = new SyncUserEntityCommitteeAction;
            $firstUser = $entity->getRelation('users')->first();
            if ($firstUser) {
                $syncAction->execute($firstUser);
            }
        } elseif ($certification->individual_id && $individual) {
            $user = $individual->getRelation('user');
            if ($user) {
                $syncRolesAction = new SyncUserRolesAction;
                $syncRolesAction->execute($user);
            }
        }
    }
}
