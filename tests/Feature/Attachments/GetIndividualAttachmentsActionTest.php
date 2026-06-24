<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Database\Factories\AttachmentFactory;
use Domain\Attachments\Actions\GetIndividualAttachmentsAction;
use Domain\Attachments\Models\Attachment;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup necessary data for each test
    $this->group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $this->federation = Federation::factory()->create();
    $this->user = User::factory()->for($this->group, 'group')->create(); // Ensure the user is created with a group
    $this->individual = Individual::factory()->for($this->user, 'user')->create(); // Create an individual for the user
    $this->individual->federations()->attach($this->federation->id);

    // Create a ProfessionalRole for the individual
    $this->professionalRole = ProfessionalRole::factory()->create(['role' => 'INSTRUCTOR']);
    $this->individual->professionalRoles()->attach($this->professionalRole->id);

    // Create a Committee
    $this->committee = Committee::factory()->create([
        'code' => 'SPORT',
        'name' => 'Sport Committee',
    ]);

    $this->actingAs($this->user);

    // Mock file storage
    Storage::fake('public');
});

it('fetches attachments for an individual', function () {
    // Create general attachments for all individuals
    $generalAttachments = AttachmentFactory::new()->count(2)
        ->state(['recipient_name' => 'all_individuals', 'recipient_id' => null, 'committee_id' => $this->committee->id])
        ->withOwner(Federation::class, $this->federation->id)
        ->create();

    // Create individual-specific attachments
    $individualAttachments = AttachmentFactory::new()
        ->forIndividual($this->individual->id)
        ->create();

    // Execute the action
    $action = new GetIndividualAttachmentsAction;
    $fetchedAttachments = $action->execute($this->individual->id, $this->committee->id)->get();

    // Assertions
    expect($fetchedAttachments)->toHaveCount(2)
        ->and($fetchedAttachments->pluck('id'))->toContain($generalAttachments->first()->id)
        ->and($fetchedAttachments->pluck('id'))->toContain($individualAttachments->first()->id);
});

it('fetches attachments for an individual with INSTRUCTOR role', function () {
    // Create general attachments for all individuals
    $generalAttachments = AttachmentFactory::new()->count(2)
        ->state(['recipient_name' => 'all_individuals', 'recipient_id' => null, 'committee_id' => $this->committee->id])
        ->withOwner(Federation::class, $this->federation->id)
        ->create();

    // Create individual-specific attachments
    $individualAttachments = AttachmentFactory::new()
        ->forIndividual($this->individual->id)
        ->create();

    // Create attachments specific to the INSTRUCTOR role
    $roleAttachments = AttachmentFactory::new()->count(2)
        ->state(['recipient_name' => 'individual', 'recipient_id' => null, 'committee_id' => $this->committee->id])
        ->withOwner(Federation::class, $this->federation->id)
        ->create()
        ->each(function (Attachment $attachment) {
            $attachment->professionalRoles()->attach($this->professionalRole->id);
        });

    // Execute the action
    $action = new GetIndividualAttachmentsAction;
    $fetchedAttachments = $action->execute($this->individual->id, $this->committee->id)->get();

    // Assertions
    expect($fetchedAttachments)->toHaveCount(4) // Adjusted count to include role-based visibility attachments
        ->and($fetchedAttachments->pluck('id'))->toContain($generalAttachments->first()->id)
        ->and($fetchedAttachments->pluck('id'))->toContain($individualAttachments->first()->id)
        ->and($fetchedAttachments->pluck('id'))->toContain($roleAttachments->first()->id)
        ->and($fetchedAttachments->pluck('id'))->toContain($roleAttachments->last()->id);
});
