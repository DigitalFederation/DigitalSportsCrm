<?php

use Domain\Documents\Actions\CreateDocumentAction;
use Domain\Documents\DataTransferObject\DocumentData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=DocumentTypeSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');
    $this->actingAs(\App\Models\User::factory()->create(['active' => true]));
});

test('a new document is stamped with the installation currency', function () {
    config(['app.currency' => 'BRL']);

    $data = DocumentData::fromArray([
        'customer_name' => 'Cliente Teste',
        'net_value' => 100,
        'total_value' => 100,
    ]);

    $document = app(CreateDocumentAction::class)($data, 'ORD', true);

    expect($document->currency)->toBe('BRL');
});

test('an explicit currency on the DTO wins over the installation default', function () {
    config(['app.currency' => 'BRL']);

    $data = DocumentData::fromArray([
        'customer_name' => 'Cliente Teste',
        'total_value' => 50,
        'currency' => 'EUR',
    ]);

    $document = app(CreateDocumentAction::class)($data, 'ORD', true);

    expect($document->currency)->toBe('EUR');
});

test('a historical document keeps its own currency after the installation changes', function () {
    config(['app.currency' => 'EUR']);
    $data = DocumentData::fromArray(['customer_name' => 'X', 'total_value' => 75]);
    $document = app(CreateDocumentAction::class)($data, 'ORD', true);

    config(['app.currency' => 'BRL']);
    app()->setLocale('en');

    $formatted = money($document->total_value, $document->currency);

    expect($document->currency)->toBe('EUR');
    expect($formatted)->toContain('€');
    expect($formatted)->not->toContain('R$');
});
