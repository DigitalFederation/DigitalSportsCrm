<?php

namespace Domain\Attachments\Actions;

use Domain\Attachments\Models\Attachment;
use Domain\Entities\Models\Entity;
use Illuminate\Database\Eloquent\Builder;

class GetEntityAttachmentsAction
{
    public function execute(int $entityId, ?int $committee_id = null)
    {
        $entity = Entity::findOrFail($entityId);

        // Fetch attachments based on recipient type
        $generalAttachments = Attachment::query()
            ->where(function (Builder $query) use ($entity) {
                return $query->whereIn('recipient_name', ['all', 'all_entities', 'all_entities_&_individuals'])
                    ->orWhere(function ($query) use ($entity) {
                        $query->where('recipient_name', 'entity')
                            ->where('recipient_id', $entity->id);
                    })
                    ->orWhere(function ($query) use ($entity) {
                        $query->where('recipient_name', 'federation')
                            ->whereIn('recipient_id', $entity->federations->pluck('id'));
                    });
            });

        $generalAttachments->when($committee_id !== null, function (Builder $query) use ($committee_id) {
            return $query->where('committee_id', $committee_id);
        }, function (Builder $query) {
            return $query->whereNull('committee_id');
        });

        // Get all licenses associated with the Entity
        $licenseIds = $entity->licenses->pluck('license_id');

        // Fetch attachments related to those licenses
        $licenseAttachments = Attachment::query()
            ->where('committee_id', $committee_id)
            ->whereHas('licenses', function ($query) use ($licenseIds) {
                $query->whereIn('license.id', $licenseIds);
            });

        // Merge the two sets of attachments
        // $attachments = $generalAttachments->concat($licenseAttachments)->unique('id');
        // return $attachments;

        return $generalAttachments->union($licenseAttachments);
    }
}
