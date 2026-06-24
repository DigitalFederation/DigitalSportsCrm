<?php

use Domain\Documents\Actions\GenerateDocumentInvoiceNumberAction;
use Domain\Documents\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('check if the first invoice number is generated', function () {

    $document = Document::factory()->create();

    expect($document->invoice_number)->toBeNull()->and($document->invoice_year)->toBeNull()->and($document->invoice_extended)->toBeNull();

    $generateAction = app(GenerateDocumentInvoiceNumberAction::class);
    $document = $generateAction($document);

    expect($document->invoice_number)->toBe(1)->and($document->invoice_year)->toBe(date('Y'))
        ->and($document->invoice_extended)
        ->toBe($document->invoice_year.'/'.str_pad(strval($document->invoice_number), $document->number_pad, '0', STR_PAD_LEFT));
});

it('check if the invoice number is generated incrementally', function () {
    $generateAction = app(GenerateDocumentInvoiceNumberAction::class);
    $documents = Document::factory(100)->create();

    foreach ($documents as $document) {
        expect($document->invoice_number)->toBeNull()->and($document->invoice_year)->toBeNull()->and($document->invoice_extended)->toBeNull();
    }

    foreach ($documents as $key => $document) {
        $document = $generateAction($document);
        expect($document->invoice_number)->toBe($key + 1)->and($document->invoice_year)->toBe(date('Y'))->and($document->invoice_extended)->toBe($document->invoice_year.'/'.str_pad(strval($document->invoice_number), $document->number_pad, '0', STR_PAD_LEFT));
    }
});
