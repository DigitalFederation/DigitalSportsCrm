<?php

namespace Domain\Entities\Actions;

use Domain\Entities\Models\Entity;

class DeleteEntityAction
{
    public function __invoke(int $id): void
    {
        $entity = Entity::whereDoesntHave('individuals')
            ->whereDoesntHave('licenses')
            ->whereDoesntHave('entityProfessionals')
            ->where(compact('id'))
            ->firstOrFail();

        $entity->users()->detach();
        $entity->federations()->detach();
        $entity->delete();

        activity('Entity')
            ->performedOn($entity)
            ->event('deleted')
            ->withProperties((array) $entity)
            ->log('Entity deleted.');
    }
}
