<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Database\Factories\AttachmentFactory;
use Domain\Attachments\Models\Attachment;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    Storage::fake('public');
    Cache::flush();
});
it('shows only committee-specific attachments', function () {

    $group = Group::factory()->create(['code' => 'FEDERATION']);
    // Create a federation and committees
    $federation = Federation::factory()->create();
    $committee1 = Committee::factory()->create([
        'code' => 'SPORT',
        'name' => 'Sport Commitee',
    ]);
    $committee2 = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Technical Commitee',
    ]);

    // Create a user and attach to federation
    $user = User::factory()->for($group, 'group')->create();
    $user->federations()->attach($federation->id);
    $this->actingAs($user);

    // Create attachments for different committees
    $attachmentsCommittee1 = AttachmentFactory::new()->count(2)
        ->state([
            'recipient_name' => 'all',
            'recipient_id' => null,
            'committee_id' => $committee1->id,
        ])
        ->create();

    $attachmentsCommittee2 = AttachmentFactory::new()->count(2)
        ->state(['recipient_name' => 'all', 'recipient_id' => null, 'committee_id' => $committee2->id])
        ->create();

    // Fetch attachments for committee 1
    $committee1Attachments = Attachment::ofCommittee($committee1->id)->get()->pluck('id')->toArray();
    // Fetch attachments for committee 2
    $committee2Attachments = Attachment::ofCommittee($committee2->id)->get()->pluck('id')->toArray();

    // Assert that only committee 1 attachments are returned
    foreach ($attachmentsCommittee1 as $attachment) {
        expect($committee1Attachments)->toContain($attachment->id);
    }

    // Assert that only committee 1 attachments are returned
    foreach ($attachmentsCommittee2 as $attachment) {
        expect($committee2Attachments)->toContain($attachment->id);
    }

});
