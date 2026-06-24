<?php

use App\Models\Group;
use App\Models\User;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\EventApplication;
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

/*
|--------------------------------------------------------------------------
| Single document download (ApplicationDocumentController@download)
| Uses Storage::disk()->download() which works with local AND cloud disks.
|--------------------------------------------------------------------------
*/

test('single document download uses storage disk and streams the file', function () {
    $file = UploadedFile::fake()->create('contract.pdf', 200, 'application/pdf');
    $path = $file->store('application-documents', ApplicationDocument::STORAGE_DISK);

    $document = ApplicationDocument::factory()->create([
        'file_name' => 'contract.pdf',
        'file_path' => $path,
    ]);

    Storage::disk(ApplicationDocument::STORAGE_DISK)->assertExists($path);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.application-documents.download', $document));

    $response->assertOk();
    $response->assertDownload('contract.pdf');
});

/*
|--------------------------------------------------------------------------
| Bulk ZIP download (ManagesEventApplications@downloadDocuments)
| Reads files from Storage disk (cloud-safe), creates temp ZIP.
|--------------------------------------------------------------------------
*/

test('download documents creates a zip with all application documents', function () {
    $application = EventApplication::factory()->create();

    $file1 = UploadedFile::fake()->create('insurance.pdf', 100, 'application/pdf');
    $path1 = $file1->store('application-documents', ApplicationDocument::STORAGE_DISK);

    $file2 = UploadedFile::fake()->create('authorization.pdf', 150, 'application/pdf');
    $path2 = $file2->store('application-documents', ApplicationDocument::STORAGE_DISK);

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'insurance.pdf',
        'file_path' => $path1,
    ]);

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'authorization.pdf',
        'file_path' => $path2,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.event-applications.download-documents', $application));

    $response->assertOk();
    $response->assertDownload('application_'.$application->id.'_documents.zip');
});

test('download documents redirects with error when application has no documents', function () {
    $application = EventApplication::factory()->create();

    $response = $this->actingAs($this->admin)
        ->from(route('admin.event-applications.show', $application))
        ->get(route('admin.event-applications.download-documents', $application));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('download documents skips files missing from storage without failing', function () {
    $application = EventApplication::factory()->create();

    $file = UploadedFile::fake()->create('real.pdf', 100, 'application/pdf');
    $realPath = $file->store('application-documents', ApplicationDocument::STORAGE_DISK);

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'real.pdf',
        'file_path' => $realPath,
    ]);

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'ghost.pdf',
        'file_path' => 'application-documents/nonexistent.pdf',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.event-applications.download-documents', $application));

    $response->assertOk();
    $response->assertDownload('application_'.$application->id.'_documents.zip');
});

test('download documents redirects with error when all files are missing from storage', function () {
    $application = EventApplication::factory()->create();

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'ghost1.pdf',
        'file_path' => 'application-documents/missing1.pdf',
    ]);

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'ghost2.pdf',
        'file_path' => 'application-documents/missing2.pdf',
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.event-applications.show', $application))
        ->get(route('admin.event-applications.download-documents', $application));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('download documents deduplicates identical filenames in zip', function () {
    $application = EventApplication::factory()->create();

    $file1 = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');
    $path1 = $file1->store('application-documents', ApplicationDocument::STORAGE_DISK);

    $file2 = UploadedFile::fake()->create('contract.pdf', 150, 'application/pdf');
    $path2 = $file2->store('application-documents', ApplicationDocument::STORAGE_DISK);

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'contract.pdf',
        'file_path' => $path1,
    ]);

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'contract.pdf',
        'file_path' => $path2,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.event-applications.download-documents', $application));

    $response->assertOk();
    $response->assertDownload('application_'.$application->id.'_documents.zip');

    // BinaryFileResponse exposes the file path -- verify ZIP contents directly
    $zipPath = $response->getFile()->getPathname();

    $zip = new ZipArchive;
    $zip->open($zipPath);
    expect($zip->numFiles)->toBe(2);
    expect($zip->getNameIndex(0))->toBe('contract.pdf');
    expect($zip->getNameIndex(1))->toBe('contract_2.pdf');
    $zip->close();
});

test('download documents succeeds via storage disk when no local file exists', function () {
    $application = EventApplication::factory()->create();

    $file = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf');
    $path = $file->store('application-documents', ApplicationDocument::STORAGE_DISK);

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_name' => 'proof.pdf',
        'file_path' => $path,
    ]);

    // Storage::fake() guarantees no local file exists -- a successful response
    // proves the code goes through Storage::disk() rather than file_exists().
    Storage::disk(ApplicationDocument::STORAGE_DISK)->assertExists($path);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.event-applications.download-documents', $application));

    $response->assertOk();
    $response->assertDownload('application_'.$application->id.'_documents.zip');
});
