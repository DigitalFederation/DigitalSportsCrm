<?php

use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Invoicing\Actions\CreateMoloniInvoiceReceiptAction;
use Domain\Invoicing\Models\MoloniInvoice;
use Domain\Invoicing\Models\MoloniToken;
use Domain\Invoicing\Services\MoloniClient;
use Domain\Invoicing\Services\MoloniSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returns null when moloni is disabled', function () {
    config(['invoicing.providers.moloni.enabled' => false]);

    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
    ]);

    $action = app(CreateMoloniInvoiceReceiptAction::class);
    $result = $action($document);

    expect($result)->toBeNull();
});

test('returns null when document is not paid', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    $document = Document::factory()->create([
        'status_class' => 'Domain\Documents\States\DraftDocumentState',
    ]);

    $action = app(CreateMoloniInvoiceReceiptAction::class);
    $result = $action($document);

    expect($result)->toBeNull();
});

test('returns null when document has no owner', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => null,
        'owner_id' => null,
    ]);

    $action = app(CreateMoloniInvoiceReceiptAction::class);
    $result = $action($document);

    expect($result)->toBeNull();
});

test('returns existing invoice when document already has one', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    $individual = Individual::factory()->create();
    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
    ]);

    $existingInvoice = MoloniInvoice::create([
        'document_id' => $document->id,
        'moloni_document_id' => 12345,
        'moloni_number' => 'FR 2024/1',
        'moloni_status' => 'closed',
        'moloni_total' => 100.00,
        'synced_at' => now(),
    ]);

    $action = app(CreateMoloniInvoiceReceiptAction::class);
    $result = $action($document);

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($existingInvoice->id);
});

test('returns null when moloni is not configured', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    $individual = Individual::factory()->create();
    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
    ]);

    $action = app(CreateMoloniInvoiceReceiptAction::class);
    $result = $action($document);

    expect($result)->toBeNull();
});

test('creates invoice when fully configured with mocked API response', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $settingsService = app(MoloniSettingsService::class);
    $settingsService->saveConfiguration([
        'document_set_id' => 1,
        'default_tax_id' => 2,
        'default_unit_id' => 3,
        'default_category_id' => 4,
        'payment_method_id' => 5,
        'use_invoice_receipts' => true,
    ]);

    $individual = Individual::factory()->create([
        'name' => 'Test Customer',
        'email' => 'test@example.com',
    ]);
    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
        'total_value' => 100.00,
        'number_extended' => 'ORD-2024/001',
    ]);
    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'net_value' => 81.30,
        'tax_value' => 18.70,
        'total_value' => 100.00,
    ]);

    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('customers/getByVat/', \Mockery::any())
        ->andReturn([]);

    $mockClient->shouldReceive('post')
        ->with('customers/insert/', \Mockery::any())
        ->andReturn(['customer_id' => 999]);

    $mockClient->shouldReceive('post')
        ->with('products/getByReference/', \Mockery::any())
        ->andReturn([]);

    $mockClient->shouldReceive('post')
        ->with('products/insert/', \Mockery::any())
        ->andReturn(['product_id' => 888]);

    $mockClient->shouldReceive('post')
        ->with('invoiceReceipts/insert/', \Mockery::on(function ($data) {
            return isset($data['document_set_id'])
                && isset($data['customer_id'])
                && isset($data['products'])
                && isset($data['payments']);
        }))
        ->andReturn([
            'document_id' => 12345,
            'document_number' => 'FR 2024/1',
            'status' => 1,
        ]);

    $action = app(CreateMoloniInvoiceReceiptAction::class);
    $result = $action($document);

    expect($result)->not->toBeNull();
    expect($result)->toBeInstanceOf(MoloniInvoice::class);
    expect($result->moloni_document_id)->toBe(12345);
    expect($result->moloni_number)->toBe('FR 2024/1');
    expect($result->moloni_status)->toBe('closed');
    expect($result->document_id)->toBe($document->id);

    $this->assertDatabaseHas('moloni_invoices', [
        'document_id' => $document->id,
        'moloni_document_id' => 12345,
        'moloni_number' => 'FR 2024/1',
    ]);
});

test('works with entity owner', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $settingsService = app(MoloniSettingsService::class);
    $settingsService->saveConfiguration([
        'document_set_id' => 1,
        'default_tax_id' => 2,
        'default_unit_id' => 3,
        'default_category_id' => 4,
        'payment_method_id' => 5,
        'use_invoice_receipts' => true,
    ]);

    $entity = Entity::factory()->create([
        'name' => 'Test Company',
    ]);
    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 250.00,
    ]);
    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'net_value' => 203.25,
        'tax_value' => 46.75,
        'total_value' => 250.00,
    ]);

    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('customers/getByVat/', \Mockery::any())
        ->andReturn([]);

    $mockClient->shouldReceive('post')
        ->with('customers/insert/', \Mockery::any())
        ->andReturn(['customer_id' => 999]);

    $mockClient->shouldReceive('post')
        ->with('products/getByReference/', \Mockery::any())
        ->andReturn([]);

    $mockClient->shouldReceive('post')
        ->with('products/insert/', \Mockery::any())
        ->andReturn(['product_id' => 888]);

    $mockClient->shouldReceive('post')
        ->with('invoiceReceipts/insert/', \Mockery::any())
        ->andReturn([
            'document_id' => 67890,
            'document_number' => 'FR 2024/2',
            'status' => 1,
        ]);

    $action = app(CreateMoloniInvoiceReceiptAction::class);
    $result = $action($document);

    expect($result)->not->toBeNull();
    expect((float) $result->moloni_total)->toEqual(250.00);

    $this->assertDatabaseHas('moloni_invoices', [
        'document_id' => $document->id,
        'moloni_document_id' => 67890,
    ]);
});

test('handles API error gracefully', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $settingsService = app(MoloniSettingsService::class);
    $settingsService->saveConfiguration([
        'document_set_id' => 1,
        'default_tax_id' => 2,
        'default_unit_id' => 3,
        'default_category_id' => 4,
        'payment_method_id' => 5,
        'use_invoice_receipts' => true,
    ]);

    $individual = Individual::factory()->create();
    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
        'total_value' => 100.00,
    ]);
    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'net_value' => 81.30,
        'tax_value' => 18.70,
        'total_value' => 100.00,
    ]);

    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('customers/getByVat/', \Mockery::any())
        ->andReturn([]);

    $mockClient->shouldReceive('post')
        ->with('customers/insert/', \Mockery::any())
        ->andReturn(['customer_id' => 999]);

    $mockClient->shouldReceive('post')
        ->with('products/getByReference/', \Mockery::any())
        ->andReturn([]);

    $mockClient->shouldReceive('post')
        ->with('products/insert/', \Mockery::any())
        ->andReturn(['product_id' => 888]);

    $mockClient->shouldReceive('post')
        ->with('invoiceReceipts/insert/', \Mockery::any())
        ->andThrow(new \Domain\Invoicing\Exceptions\MoloniApiException(
            'API Error',
            400,
            'invoiceReceipts/insert/',
            ['error' => 'Invalid data']
        ));

    $action = app(CreateMoloniInvoiceReceiptAction::class);

    expect(fn () => $action($document))
        ->toThrow(\Domain\Invoicing\Exceptions\MoloniApiException::class);

    $this->assertDatabaseMissing('moloni_invoices', [
        'document_id' => $document->id,
    ]);
});
