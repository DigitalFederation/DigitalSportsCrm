<?php

use Domain\Documents\Actions\CancelDocumentAction;
use Domain\Documents\Models\Document;
use Domain\Documents\States\CanceledDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\EvtEvents\Models\Enrollment;

it('loads document via belongsTo relationship', function () {
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
    ]);

    $enrollment = Enrollment::factory()->create([
        'document_id' => $document->id,
    ]);

    $enrollment->refresh();

    expect($enrollment->document)
        ->toBeInstanceOf(Document::class)
        ->and($enrollment->document->id)->toBe($document->id);
});

it('returns null when enrollment has no document', function () {
    $enrollment = Enrollment::factory()->create([
        'document_id' => null,
    ]);

    expect($enrollment->document)->toBeNull();
});

it('unlinks enrollment when document is canceled', function () {
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
    ]);

    $enrollment = Enrollment::factory()->create([
        'document_id' => $document->id,
    ]);

    $action = new CancelDocumentAction;
    $action->execute($document);

    $enrollment->refresh();
    $document->refresh();

    expect($document->status_class)->toBe(CanceledDocumentState::class)
        ->and($enrollment->document_id)->toBeNull();
});

it('only unlinks enrollments referencing the canceled document', function () {
    $documentToCancel = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
    ]);

    $otherDocument = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
    ]);

    $enrollmentToUnlink = Enrollment::factory()->create([
        'document_id' => $documentToCancel->id,
    ]);

    $enrollmentToKeep = Enrollment::factory()->create([
        'document_id' => $otherDocument->id,
    ]);

    $action = new CancelDocumentAction;
    $action->execute($documentToCancel);

    $enrollmentToUnlink->refresh();
    $enrollmentToKeep->refresh();

    expect($enrollmentToUnlink->document_id)->toBeNull()
        ->and($enrollmentToKeep->document_id)->toBe($otherDocument->id);
});
