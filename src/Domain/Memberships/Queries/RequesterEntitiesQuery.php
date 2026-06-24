<?php

namespace Domain\Memberships\Queries;

use Domain\Entities\Models\Entity;
use Illuminate\Support\Collection;

class RequesterEntitiesQuery
{
    /**
     * Get all entities that have requested member subscriptions.
     */
    public function execute(): Collection
    {
        return Entity::whereIn('id', function ($query) {
            $query->select('requester_id')
                ->from('member_subscriptions')
                ->where('requester_type', 'entity')
                ->whereNotNull('requester_id');
        })->orderBy('name')->pluck('name', 'id');
    }
}
