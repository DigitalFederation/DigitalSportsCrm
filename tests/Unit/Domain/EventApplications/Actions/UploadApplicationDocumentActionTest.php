<?php

use App\Models\User;
use Domain\EventApplications\Actions\UploadApplicationDocumentAction;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('secure-media');
});

test('uploads document and creates record', function () {
    $application = EventApplication::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $action = new UploadApplicationDocumentAction;
    $document = $action->execute([
        'application_id' => $application->id,
        'document_type' => 'insurance',
        'file' => $file,
    ]);

    expect($document)->toBeInstanceOf(ApplicationDocument::class)
        ->and($document->file_name)->toBe('document.pdf')
        ->and($document->application_id)->toBe($application->id)
        ->and($document->document_type)->toBe('insurance');
});

test('stores file in private storage', function () {
    $application = EventApplication::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $action = new UploadApplicationDocumentAction;
    $document = $action->execute([
        'application_id' => $application->id,
        'document_type' => 'authorization',
        'file' => $file,
    ]);

    Storage::disk('secure-media')->assertExists($document->file_path);
});

test('saves file metadata', function () {
    $application = EventApplication::factory()->create();
    $file = UploadedFile::fake()->create('test.pdf', 250, 'application/pdf');

    $action = new UploadApplicationDocumentAction;
    $document = $action->execute([
        'application_id' => $application->id,
        'document_type' => 'venue_agreement',
        'file' => $file,
    ]);

    expect($document->file_size)->toBeGreaterThan(0)
        ->and($document->mime_type)->toBe('application/pdf')
        ->and($document->file_name)->toBe('test.pdf');
});

test('can upload for template', function () {
    $template = ApplicationTemplate::factory()->create();
    $file = UploadedFile::fake()->create('template-doc.pdf', 100);

    $action = new UploadApplicationDocumentAction;
    $document = $action->execute([
        'template_id' => $template->id,
        'document_type' => 'requirements',
        'file' => $file,
    ]);

    expect($document->template_id)->toBe($template->id)
        ->and($document->application_id)->toBeNull();
});

test('can save uploaded by information', function () {
    $application = EventApplication::factory()->create();
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('doc.pdf', 100);

    $action = new UploadApplicationDocumentAction;
    $document = $action->execute([
        'application_id' => $application->id,
        'document_type' => 'other',
        'uploaded_by_type' => User::class,
        'uploaded_by_id' => $user->id,
        'file' => $file,
    ]);

    expect($document->uploaded_by_type)->toBe(User::class)
        ->and($document->uploaded_by_id)->toBe($user->id);
});

test('can mark document as required', function () {
    $application = EventApplication::factory()->create();
    $file = UploadedFile::fake()->create('required.pdf', 100);

    $action = new UploadApplicationDocumentAction;
    $document = $action->execute([
        'application_id' => $application->id,
        'document_type' => 'insurance',
        'is_required' => true,
        'file' => $file,
    ]);

    expect($document->is_required)->toBeTrue();
});

test('throws exception for invalid file', function () {
    $application = EventApplication::factory()->create();

    $action = new UploadApplicationDocumentAction;

    expect(fn () => $action->execute([
        'application_id' => $application->id,
        'document_type' => 'insurance',
        'file' => 'not-a-file',
    ]))->toThrow(\InvalidArgumentException::class);
});
