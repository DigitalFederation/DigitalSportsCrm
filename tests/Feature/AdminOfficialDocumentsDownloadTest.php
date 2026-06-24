<?php

use App\Models\Country;
use App\Models\Group;
use Database\Factories\UserFactory;
use Domain\Federations\Models\Federation;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::factory()->create();
    $this->group = Group::factory()->create(['code' => 'ADMIN']);
});

it('allows admin to download an official document', function () {
    Storage::fake('secure-media');

    $federation = Federation::factory()->create();
    $user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
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

    // Add a media file to the document
    $document->addMedia(UploadedFile::fake()->create('test-document.pdf', 100, 'application/pdf'))
        ->toMediaCollection('media');

    $this->actingAs($user);

    $response = $this->post(route('admin.official-documents.download', $document->id));

    $response->assertSuccessful();
});

it('allows admin to preview an official document', function () {
    Storage::fake('secure-media');

    $federation = Federation::factory()->create();
    $user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
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

    // Add a media file to the document
    $document->addMedia(UploadedFile::fake()->create('test-document.pdf', 100, 'application/pdf'))
        ->toMediaCollection('media');

    $this->actingAs($user);

    $response = $this->get(route('admin.official-documents.preview', $document->id));

    $response->assertSuccessful();
});

it('returns error when document has no media file', function () {
    $federation = Federation::factory()->create();
    $user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
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

    $this->actingAs($user);

    $response = $this->post(route('admin.official-documents.download', $document->id));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});
