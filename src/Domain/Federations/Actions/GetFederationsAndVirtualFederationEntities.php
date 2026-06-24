<?php

namespace Domain\Federations\Actions;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Illuminate\Database\Eloquent\Builder;

class GetFederationsAndVirtualFederationEntities
{
    public function execute(): array
    {
        $federations = Federation::all();

        $entities = Entity::whereHas('federations', function (Builder $query) {
            return $query->where('is_default_federation', true);
        })->get();

        return ['federations' => $federations, 'entities' => $entities];
    }
}
