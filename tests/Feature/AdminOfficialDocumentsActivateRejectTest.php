<?php

use App\Models\Country;
use App\Models\Group;
use Database\Factories\UserFactory;
use Domain\Federations\Models\Federation;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Domain\OfficialDocuments\States\RejectedOfficialDocumentState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::factory()->create();
    $this->group = Group::factory()->create(['code' => 'ADMIN']);
});
it('activates an official document', function () {
    // Arrange: Create a test user and an official document
    $federation = Federation::factory()->create();
    $user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    Permission::findOrCreate('access official documents');
    $user->givePermissionTo('access official documents');

    $document = OfficialDocument::factory()->create([
        'country_id' => $this->country->id,
        'individual_id' => null,
        'federation_id' => $federation->id,
        'expiry_date' => null,
        'type' => \App\Enums\OfficialDocumentTypeEnum::Statutes,
        'status_class' => PendingOfficialDocumentState::class,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    // Act: Act as the user and send a request to activate the document
    $this->actingAs($user);

    $response = $this->put(route('admin.official-documents.activate', $document->id), [
        'start_date' => now()->format('Y-m-d'),
        'expire_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // Assert: Check if the document's status has been updated
    $document->refresh();
    expect($document->status_class)->toBe(ActiveOfficialDocumentState::class);
});
it('rejects an official document', function () {
    // Arrange: Create a test user and an official document
    $federation = Federation::factory()->create();
    $user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    Permission::findOrCreate('access official documents');
    $user->givePermissionTo('access official documents');

    $document = OfficialDocument::factory()->create([
        'country_id' => $this->country->id,
        'individual_id' => null,
        'federation_id' => $federation->id,
        'expiry_date' => null,
        'type' => \App\Enums\OfficialDocumentTypeEnum::Statutes,
        'status_class' => PendingOfficialDocumentState::class,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    // Act: Act as the user and send a request to activate the document
    $this->actingAs($user);

    $response = $this->put(route('admin.official-documents.reject', $document->id));

    // Assert: Check if the document's status has been updated
    $document->refresh();
    expect($document->status_class)->toBe(RejectedOfficialDocumentState::class);
});
