<?php

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Models\EventApplication;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can only view own federation\'s applications', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    $retrieved = EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->first();

    expect($retrieved)->not->toBeNull()
        ->and($retrieved->id)->toBe($application->id);
});

test('user cannot view other federation\'s applications', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation1);

    $application = EventApplication::factory()->create([
        'entity_id' => $federation2->id,
        'entity_type' => Federation::class,
    ]);

    $userFederationApps = EventApplication::where('entity_id', $federation1->id)
        ->where('entity_type', Federation::class)
        ->get();

    expect($userFederationApps)->not->toContain($application);
});

test('user cannot create for federation they\'re not part of', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();

    $isMember = $user->federations()->where('federation.id', $federation->id)->exists();

    expect($isMember)->toBeFalse();
});

test('user can edit own federation\'s draft', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Original',
    ]);

    expect($application->state->canEdit())->toBeTrue();

    $application->update(['event_name' => 'Updated']);

    expect($application->fresh()->event_name)->toBe('Updated');
});

test('user cannot edit other federation\'s application', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation1);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation2->id,
        'entity_type' => Federation::class,
    ]);

    expect($application->entity_id)->not->toBe($federation1->id);
});

test('entity users get 403 on federation routes', function () {
    $entity = Entity::factory()->create();
    $federation = Federation::factory()->create();
    $user = User::factory()->create();

    $entityApp = EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
    ]);

    $federationApp = EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    expect($entityApp->entity_type)->toBe(Entity::class)
        ->and($federationApp->entity_type)->toBe(Federation::class);
});

test('federation users get 403 on entity routes', function () {
    $entity = Entity::factory()->create();
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $entityApp = EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
    ]);

    $isFederationMember = $user->federations()->where('federation.id', $federation->id)->exists();

    expect($isFederationMember)->toBeTrue()
        ->and($entityApp->entity_type)->toBe(Entity::class)
        ->and($entityApp->entity_type)->not->toBe(Federation::class);
});

test('application index filtered by federation', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation1->id,
        'entity_type' => Federation::class,
    ]);

    EventApplication::factory()->create([
        'entity_id' => $federation2->id,
        'entity_type' => Federation::class,
    ]);

    $federation1Apps = EventApplication::where('entity_id', $federation1->id)
        ->where('entity_type', Federation::class)
        ->get();

    expect($federation1Apps)->toHaveCount(1);
});

test('show route checks federation ownership', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation1);

    $application = EventApplication::factory()->create([
        'entity_id' => $federation1->id,
        'entity_type' => Federation::class,
    ]);

    $otherApplication = EventApplication::factory()->create([
        'entity_id' => $federation2->id,
        'entity_type' => Federation::class,
    ]);

    $canAccess = $application->entity_id === $federation1->id;
    $cannotAccess = $otherApplication->entity_id === $federation1->id;

    expect($canAccess)->toBeTrue()
        ->and($cannotAccess)->toBeFalse();
});

test('destroy route checks federation ownership', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation1);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation1->id,
        'entity_type' => Federation::class,
    ]);

    $otherApplication = EventApplication::factory()->draft()->create([
        'entity_id' => $federation2->id,
        'entity_type' => Federation::class,
    ]);

    $canDelete = $application->entity_id === $federation1->id && $application->state->canDelete();
    $cannotDelete = $otherApplication->entity_id === $federation1->id;

    expect($canDelete)->toBeTrue()
        ->and($cannotDelete)->toBeFalse();
});
