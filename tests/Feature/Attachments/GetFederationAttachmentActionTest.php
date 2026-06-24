<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Database\Factories\AttachmentFactory;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;

it('fetches attachments for all federations', function () {
    $committee1 = Committee::factory()->create([
        'code' => 'SPORT',
        'name' => 'Sport Commitee',
    ]);
    // Create an individual using the factory
    $group = Group::factory()->create(['code' => 'FEDERATION']);
    $group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);

    $federation = Federation::factory()->create();
    $user = User::factory()->for($group, 'group')->create();
    $user->federations()->attach($federation->id);

    // Individual
    $user_individual = User::factory()->for($group_individual, 'group')->create();
    $individual = Individual::factory()->for($user_individual, 'user')->create();
    $individual->federations()->attach($federation->id);

    $this->actingAs($user);

    // Create some general attachments for all individuals
    $generalAttachments = AttachmentFactory::new()->count(2)
        ->state(['recipient_name' => 'all_federations', 'recipient_id' => null, 'committee_id' => $committee1->id])
        ->withOwner(User::class, $user->id)
        ->create();

    // Get Federation Attachments
    $action = new \Domain\Attachments\Actions\GetFederationAttachmentsAction;
    $fetchedAttachments = $action->execute($federation->id, $committee1->id)->get();

    // Assertions
    expect($fetchedAttachments)->toHaveCount(2)
        ->and($fetchedAttachments->pluck('id'))->toContain($generalAttachments->first()->id);

});
