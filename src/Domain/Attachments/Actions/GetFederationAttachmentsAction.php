<?php

namespace Domain\Attachments\Actions;

use Domain\Attachments\Models\Attachment;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Database\Eloquent\Builder;

class GetFederationAttachmentsAction
{
    /**
     * Create a query builder instance to fetch attachments based on the federation and committee.
     *
     * The method constructs a query builder instance to get general attachments for a given
     * federation and committee, as well as license-specific attachments. The query is not executed
     * within this method, allowing for additional query constraints to be added later.
     *
     * @param  int  $federationId  The ID of the federation for which to fetch attachments.
     * @param  int|null  $committee_id  The ID of the committee for which to fetch attachments.
     * @return Builder Query builder instance for fetching attachments.
     */
    public function execute(int $federationId, ?int $committee_id = null)
    {

        // Query for license-specific attachments
        // Get all licenses related to the Federation's membership plans
        $licenseIds = Membership::query()
            ->where('federation_id', $federationId)
            ->where('status_class', ActiveMembershipState::class)
            ->with('plans.licenses')
            ->get()
            ->pluck('plans.*.licenses.*.id')
            ->flatten()
            ->unique();

        // Query for general and federation-specific attachments
        $generalAttachments = Attachment::query()
            ->where(function ($query) use ($federationId) {
                $query->whereIn('recipient_name', ['all', 'all_federations'])
                    ->orWhere(function ($query) use ($federationId) {
                        $query->where('recipient_type', Federation::class)
                            ->where('recipient_id', $federationId);
                    })
                    ->orWhere(function ($query) use ($federationId) {
                        $query->where('owner_type', Federation::class)
                            ->where('owner_id', $federationId);
                    })
                    // Include attachments linked through filterFederation() relationship
                    ->orWhereHas('filterFederation', function ($query) use ($federationId) {
                        $query->where('federation_id', $federationId);
                    });
            })
            ->when($committee_id !== null, function ($query) use ($committee_id) {
                return $query->where('committee_id', $committee_id);
            }, function ($query) {
                return $query->whereNull('committee_id');
            });

        // Get attachments related to those licenses but respecting federation-specific rules
        $licenseAttachments = Attachment::query()
            ->whereHas('licenses', function ($query) use ($licenseIds) {
                $query->whereIn('license.id', $licenseIds);
            })
            ->where(function ($query) use ($federationId) {
                $query->whereIn('recipient_name', ['all', 'all_federations'])
                    ->orWhere(function ($query) use ($federationId) {
                        $query->where('recipient_type', Federation::class)
                            ->where('recipient_id', $federationId);
                    })
                    ->orWhere(function ($query) use ($federationId) {
                        $query->where('owner_type', Federation::class)
                            ->where('owner_id', $federationId);
                    })
                    ->orWhereHas('filterFederation', function ($query) use ($federationId) {
                        $query->where('federation_id', $federationId);
                    });
            })
            ->when($committee_id, function ($query, $committee_id) {
                return $query->where('committee_id', $committee_id);
            });

        // Merge the two sets of attachments
        // $attachments = $generalAttachments->concat($licenseAttachments)->unique('id');
        // return $attachments;

        return $generalAttachments->union($licenseAttachments);
    }
}
