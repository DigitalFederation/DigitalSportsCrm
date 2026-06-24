<?php

declare(strict_types=1);

namespace Domain\Entities\Actions;

use Domain\Entities\Models\Entity;
use Illuminate\Http\UploadedFile;

class UpdateEntityPublicSettingsAction
{
    public function __invoke(Entity $entity, array $data): Entity
    {
        $entity->update([
            'public_description' => $data['public_description'] ?? null,
        ]);

        if (isset($data['entity_background']) && $data['entity_background'] instanceof UploadedFile) {
            $entity->addMedia($data['entity_background'])
                ->toMediaCollection('entity-background');
        }

        return $entity->fresh();
    }
}
