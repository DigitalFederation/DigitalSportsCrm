<?php

use App\Livewire\Federation\TemplateDocumentManager;
use App\Models\User;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\ApplicationTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->template = ApplicationTemplate::factory()->create();
});

test('upload stores document and shows success message', function () {
    $file = UploadedFile::fake()->create('guide.pdf', 1024, 'application/pdf');

    Livewire::test(TemplateDocumentManager::class, ['template' => $this->template])
        ->set('newAttachment', $file)
        ->call('saveAttachment')
        ->assertSet('successMessage', __('event_applications.document_uploaded_success'))
        ->assertSet('errorMessage', '');

    expect(ApplicationDocument::where('template_id', $this->template->id)->count())->toBe(1);

    $document = ApplicationDocument::where('template_id', $this->template->id)->first();
    expect($document->file_name)->toBe('guide.pdf')
        ->and($document->uploaded_by_type)->toBe('App\Models\User');

    Storage::disk('public')->assertExists($document->file_path);
});

test('upload uses custom name when provided', function () {
    $file = UploadedFile::fake()->create('guide.pdf', 1024, 'application/pdf');

    Livewire::test(TemplateDocumentManager::class, ['template' => $this->template])
        ->set('newAttachment', $file)
        ->set('newAttachmentName', 'Custom Document Name')
        ->call('saveAttachment')
        ->assertSet('successMessage', __('event_applications.document_uploaded_success'));

    $document = ApplicationDocument::where('template_id', $this->template->id)->first();
    expect($document->file_name)->toBe('Custom Document Name');
});

test('uploaded document appears in the document list', function () {
    $file = UploadedFile::fake()->create('rules.pdf', 512, 'application/pdf');

    Livewire::test(TemplateDocumentManager::class, ['template' => $this->template])
        ->set('newAttachment', $file)
        ->call('saveAttachment')
        ->assertSee('rules.pdf');
});

test('confirm delete sets modal state', function () {
    $document = ApplicationDocument::factory()->forTemplate()->create([
        'template_id' => $this->template->id,
    ]);

    Livewire::test(TemplateDocumentManager::class, ['template' => $this->template])
        ->call('confirmDelete', $document->id)
        ->assertSet('confirmingDeletion', true)
        ->assertSet('documentIdToRemove', $document->id);
});

test('delete removes document and shows success message', function () {
    $document = ApplicationDocument::factory()->forTemplate()->create([
        'template_id' => $this->template->id,
    ]);

    Storage::disk('public')->put($document->file_path, 'dummy');

    Livewire::test(TemplateDocumentManager::class, ['template' => $this->template])
        ->call('confirmDelete', $document->id)
        ->call('delete')
        ->assertSet('successMessage', __('event_applications.document_deleted_success'))
        ->assertSet('errorMessage', '')
        ->assertSet('confirmingDeletion', false);

    expect(ApplicationDocument::find($document->id))->toBeNull();
});

test('delete rejects document belonging to another template', function () {
    $otherTemplate = ApplicationTemplate::factory()->create();
    $document = ApplicationDocument::factory()->forTemplate()->create([
        'template_id' => $otherTemplate->id,
    ]);

    Livewire::test(TemplateDocumentManager::class, ['template' => $this->template])
        ->call('confirmDelete', $document->id)
        ->call('delete')
        ->assertSet('errorMessage', __('event_applications.document_not_found'));

    expect(ApplicationDocument::find($document->id))->not->toBeNull();
});

test('upload requires a file', function () {
    Livewire::test(TemplateDocumentManager::class, ['template' => $this->template])
        ->call('saveAttachment')
        ->assertHasErrors(['newAttachment' => 'required']);
});

test('upload rejects invalid mime type', function () {
    $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

    Livewire::test(TemplateDocumentManager::class, ['template' => $this->template])
        ->set('newAttachment', $file)
        ->call('saveAttachment')
        ->assertHasErrors(['newAttachment' => 'mimes']);
});
