<?php

namespace Domain\Attachments\Actions;

use App\Enums\AttachmentRecipientTypeEnum;
use Domain\Attachments\Models\Attachment;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;

class GetIndividualAttachmentsAction
{
    public function execute(
        $individualId,
        ?int $committee_id = null
    ): Builder {

        $individual = Individual::findOrFail($individualId);
        // Fetch the federations the individual is part of
        $individualFederations = $individual->federations->pluck('id')->toArray();

        // Fetch attachments based on recipient type
        $generalAttachments = Attachment::query()
            ->where(function (Builder $query) use ($individual, $individualFederations) {
                $query->where(function (Builder $query) {
                    $query->whereIn('recipient_name', [
                        AttachmentRecipientTypeEnum::All->toString(),
                        AttachmentRecipientTypeEnum::AllIndividuals->toString(),
                        AttachmentRecipientTypeEnum::AllEntitiesAndIndividuals->toString(),
                    ]);
                })
                    ->orWhere(function (Builder $query) use ($individual) {
                        $query->where('recipient_name', AttachmentRecipientTypeEnum::Individual->toString())
                            ->where('recipient_id', $individual->id);
                    })
                    ->orWhere(function (Builder $query) use ($individualFederations) {
                        $query->where('owner_type', Federation::class)
                            ->whereIn('owner_id', $individualFederations)
                            ->where('recipient_name', AttachmentRecipientTypeEnum::Individual->toString())
                            ->whereNull('recipient_id');
                    });
            });

        if (! is_null($committee_id)) {
            $generalAttachments->where('committee_id', $committee_id);
        } else {
            $generalAttachments->whereNull('committee_id');
        }

        // Get all licenses, certifications, and professional roles associated with the Individual
        // Licenses must be active
        $licenseIds = $individual->licenses->where('status_class', ActiveLicenseAttributedState::class)->pluck('license_id');
        $certificationIds = $individual->certificationsAttributed->pluck('certification_id');
        $professionalRoleIds = $individual->professionalRoles->pluck('id');

        // Fetch attachments related to those licenses, certifications, and professional roles
        // Only include attachments that are meant for individuals (exclude entity-only and federation-only)
        $relatedAttachments = Attachment::query()
            ->whereIn('recipient_name', [
                AttachmentRecipientTypeEnum::All->toString(),
                AttachmentRecipientTypeEnum::AllIndividuals->toString(),
                AttachmentRecipientTypeEnum::AllEntitiesAndIndividuals->toString(),
                AttachmentRecipientTypeEnum::Individual->toString(),
            ])
            ->where(function ($query) use ($licenseIds, $certificationIds, $professionalRoleIds) {
                $query->whereHas('licenses', function ($query) use ($licenseIds) {
                    $query->whereIn('license.id', $licenseIds);
                })
                    ->orWhereHas('certifications', function ($query) use ($certificationIds) {
                        $query->whereIn('certification.id', $certificationIds);
                    })
                    ->orWhereHas('professionalRoles', function ($query) use ($professionalRoleIds) {
                        $query->whereIn('professional_roles.id', $professionalRoleIds);
                    });
            });

        // Same for relatedAttachments
        if (! is_null($committee_id)) {
            $relatedAttachments->where('committee_id', $committee_id);
        } else {
            $relatedAttachments->whereNull('committee_id');
        }

        // Ensure the attachments are only returned if the federation matches for federation-specific attachments
        $relatedAttachments->where(function (Builder $query) use ($individualFederations) {
            $query->where('owner_type', '!=', Federation::class)
                ->orWhereIn('owner_id', $individualFederations);
        });

        return $generalAttachments->union($relatedAttachments);
    }
}
