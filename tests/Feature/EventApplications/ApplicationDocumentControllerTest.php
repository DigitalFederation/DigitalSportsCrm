<?php

use App\Models\Group;
use App\Models\User;
use Domain\EventApplications\Models\ApplicationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake(ApplicationDocument::STORAGE_DISK);

    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');

    $adminGroup = Group::where('code', 'ADMIN')->first();
    $this->admin = User::factory()->create([
        'group_id' => $adminGroup->id,
        'active' => true,
    ]);
    $this->admin->assignRole('admin');
});

test('admin can download a document from the secure-media disk', function () {
    $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');
    $path = $file->store('application-documents', ApplicationDocument::STORAGE_DISK);

    $document = ApplicationDocument::factory()->create([
        'file_name' => 'report.pdf',
        'file_path' => $path,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.application-documents.download', $document));

    $response->assertOk();
    $response->assertDownload('report.pdf');
});

test('download returns back with error when file is missing from disk', function () {
    $document = ApplicationDocument::factory()->create([
        'file_name' => 'ghost.pdf',
        'file_path' => 'application-documents/nonexistent.pdf',
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.application-documents.download', $document))
        ->get(route('admin.application-documents.download', $document));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('admin can delete a document and its file from the secure-media disk', function () {
    $file = UploadedFile::fake()->create('to-delete.pdf', 100, 'application/pdf');
    $path = $file->store('application-documents', ApplicationDocument::STORAGE_DISK);

    $document = ApplicationDocument::factory()->create([
        'file_name' => 'to-delete.pdf',
        'file_path' => $path,
    ]);

    Storage::disk(ApplicationDocument::STORAGE_DISK)->assertExists($path);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.application-documents.download', $document))
        ->delete(route('admin.application-documents.destroy', $document));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('application_documents', ['id' => $document->id]);
    Storage::disk(ApplicationDocument::STORAGE_DISK)->assertMissing($path);
});

test('non-admin user is blocked from downloading a document', function () {
    $adminGroup = Group::where('code', 'ADMIN')->first();
    $nonAdminUser = User::factory()->create([
        'group_id' => $adminGroup->id,
        'active' => true,
    ]);

    $document = ApplicationDocument::factory()->create([
        'file_path' => 'application-documents/secret.pdf',
    ]);

    $response = $this->actingAs($nonAdminUser)
        ->get(route('admin.application-documents.download', $document));

    $response->assertForbidden();
});

test('non-admin user is blocked from deleting a document', function () {
    $adminGroup = Group::where('code', 'ADMIN')->first();
    $nonAdminUser = User::factory()->create([
        'group_id' => $adminGroup->id,
        'active' => true,
    ]);

    $document = ApplicationDocument::factory()->create([
        'file_path' => 'application-documents/secret.pdf',
    ]);

    $response = $this->actingAs($nonAdminUser)
        ->delete(route('admin.application-documents.destroy', $document));

    $response->assertForbidden();
    $this->assertDatabaseHas('application_documents', ['id' => $document->id]);
});

test('delete handles missing file gracefully without erroring', function () {
    $document = ApplicationDocument::factory()->create([
        'file_name' => 'already-gone.pdf',
        'file_path' => 'application-documents/already-gone.pdf',
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.application-documents.download', $document))
        ->delete(route('admin.application-documents.destroy', $document));

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('application_documents', ['id' => $document->id]);
});
