<?php

namespace Domain\Entities\Actions;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FindInstructorAction
{
    public function execute(string $instructorCode, int $professionalRoleSelected, int $entityFederationId)
    {
        $currentEntityId = Auth::user()->entities()->first()->id;

        // For instructor certifications, check the main federation and all its children
        // Instructor certifications can be issued by child or modality federations.
        $mainFederation = Federation::where('is_default_federation', true)->first();
        $certificationFederationIds = [];
        if ($mainFederation) {
            $certificationFederationIds[] = $mainFederation->id;
            $childFederationIds = Federation::where('parent_id', $mainFederation->id)->pluck('id')->toArray();
            $certificationFederationIds = array_merge($certificationFederationIds, $childFederationIds);
        } else {
            $certificationFederationIds[] = $entityFederationId;
        }

        return Individual::where('member_code', $instructorCode)
            ->whereHas('licenses', function (Builder $query) use ($professionalRoleSelected) {
                $query->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $query) use ($professionalRoleSelected) {
                        $query->where('professional_role_id', $professionalRoleSelected)
                            ->whereHas('professionalRole', function (Builder $query) {
                                $query->whereIn('role', ['INSTRUCTOR', 'LEADER']);
                            });
                    });
            })
            ->whereHas('certificationsAttributed', function (Builder $query) use ($certificationFederationIds) {
                $query->where('status_class', ActiveCertificationAttributedState::class)
                    ->whereIn('federation_id', $certificationFederationIds);
            })
            ->whereHas('federations', function (Builder $query) use ($entityFederationId) {
                $query->where('federation_id', $entityFederationId)
                    ->where('status_class', ActiveIndividualFederationState::class);
            })
            ->whereDoesntHave('professionalRoleEntities', function (Builder $query) use ($professionalRoleSelected) {
                $query->where('entity_id', Auth::user()->entities()->first()->id)
                    ->whereNot('status_class', RejectedEntityProfessionalRoleState::class)
                    ->whereHas('professionalRole', function (Builder $query) use ($professionalRoleSelected) {
                        $query->where('id', $professionalRoleSelected);
                    });
            })
            ->whereHas('individualEntities', function (Builder $query) use ($currentEntityId) {
                $query->where('entity_id', $currentEntityId)
                    ->where('status_class', ActiveIndividualEntityState::class);
            })
            ->first();
    }
}
