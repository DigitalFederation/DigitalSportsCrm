<?php

use Domain\Attachments\Actions\GetEntityAttachmentsAction;
use Domain\Attachments\Models\Attachment;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;

it('fetches general, direct entity, and federation-targeted attachments for an entity', function (): void {
    $entity = Entity::factory()->create();
    $otherEntity = Entity::factory()->create();
    $federation = Federation::factory()->create();
    $entity->federations()->attach($federation->id);

    $general = Attachment::factory()->create([
        'recipient_name' => 'all_entities',
        'recipient_id' => null,
        'committee_id' => null,
    ]);

    $direct = Attachment::factory()->create([
        'recipient_name' => 'entity',
        'recipient_id' => $entity->id,
        'committee_id' => null,
    ]);

    $throughFederation = Attachment::factory()->create([
        'recipient_name' => 'federation',
        'recipient_id' => $federation->id,
        'committee_id' => null,
    ]);

    $other = Attachment::factory()->create([
        'recipient_name' => 'entity',
        'recipient_id' => $otherEntity->id,
        'committee_id' => null,
    ]);

    $attachments = app(GetEntityAttachmentsAction::class)
        ->execute($entity->id)
        ->get()
        ->pluck('id');

    expect($attachments)
        ->toContain($general->id)
        ->toContain($direct->id)
        ->toContain($throughFederation->id)
        ->not->toContain($other->id);
});
