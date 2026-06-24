<?php

use Domain\Individuals\Models\Individual;
use Domain\Invoicing\Models\MoloniCustomer;
use Domain\Invoicing\Services\MoloniClient;
use Domain\Invoicing\Services\MoloniCustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('different individuals get different moloni customer mappings', function () {
    $individualA = Individual::factory()->create([
        'vat_number' => '123456789',
        'name' => 'Person',
        'surname' => 'A',
    ]);
    $individualB = Individual::factory()->create([
        'vat_number' => '987654321',
        'name' => 'Person',
        'surname' => 'B',
    ]);

    $mockClient = $this->mock(MoloniClient::class);
    $mockClient->shouldReceive('post')
        ->with('customers/getByVat/', \Mockery::on(fn ($data) => $data['vat'] === '123456789'))
        ->andReturn([]);
    $mockClient->shouldReceive('post')
        ->with('customers/getByVat/', \Mockery::on(fn ($data) => $data['vat'] === '987654321'))
        ->andReturn([]);
    $mockClient->shouldReceive('post')
        ->with('customers/insert/', \Mockery::on(fn ($data) => $data['vat'] === '123456789'))
        ->andReturn(['customer_id' => 100]);
    $mockClient->shouldReceive('post')
        ->with('customers/insert/', \Mockery::on(fn ($data) => $data['vat'] === '987654321'))
        ->andReturn(['customer_id' => 200]);

    $service = app(MoloniCustomerService::class);

    $customerIdA = $service->findOrCreate($individualA);
    $customerIdB = $service->findOrCreate($individualB);

    expect($customerIdA)->toBe(100);
    expect($customerIdB)->toBe(200);
    expect($customerIdA)->not->toBe($customerIdB);

    // Verify correct cache entries were created
    $cachedA = MoloniCustomer::findByOwner($individualA);
    $cachedB = MoloniCustomer::findByOwner($individualB);

    expect($cachedA->moloni_customer_id)->toBe(100);
    expect($cachedB->moloni_customer_id)->toBe(200);
    expect($cachedA->customerable_id)->toBe($individualA->id);
    expect($cachedB->customerable_id)->toBe($individualB->id);
});

test('cached customer is returned on subsequent calls for same individual', function () {
    $individual = Individual::factory()->create([
        'vat_number' => '123456789',
        'name' => 'Test',
        'surname' => 'Person',
    ]);

    $mockClient = $this->mock(MoloniClient::class);
    $mockClient->shouldReceive('post')
        ->with('customers/getByVat/', \Mockery::any())
        ->once()
        ->andReturn([]);
    $mockClient->shouldReceive('post')
        ->with('customers/insert/', \Mockery::any())
        ->once()
        ->andReturn(['customer_id' => 100]);

    $service = app(MoloniCustomerService::class);

    $firstCall = $service->findOrCreate($individual);
    $secondCall = $service->findOrCreate($individual);

    expect($firstCall)->toBe(100);
    expect($secondCall)->toBe(100);

    // Only one cache record should exist
    expect(MoloniCustomer::count())->toBe(1);
});

test('individual uuid is stored correctly in customerable_id column', function () {
    $individual = Individual::factory()->create([
        'vat_number' => '123456789',
        'name' => 'Test',
        'surname' => 'Person',
    ]);

    // Ensure individual has a UUID
    expect(strlen($individual->id))->toBe(36);

    $mockClient = $this->mock(MoloniClient::class);
    $mockClient->shouldReceive('post')
        ->with('customers/getByVat/', \Mockery::any())
        ->andReturn([]);
    $mockClient->shouldReceive('post')
        ->with('customers/insert/', \Mockery::any())
        ->andReturn(['customer_id' => 100]);

    $service = app(MoloniCustomerService::class);
    $service->findOrCreate($individual);

    // Verify the UUID was stored without truncation
    $cached = MoloniCustomer::first();
    expect($cached->customerable_id)->toBe($individual->id);
    expect(strlen($cached->customerable_id))->toBe(36);
});

test('individuals without vat use 999999990 and do not collide', function () {
    $individualA = Individual::factory()->create([
        'vat_number' => null,
        'name' => 'No',
        'surname' => 'Vat A',
    ]);
    $individualB = Individual::factory()->create([
        'vat_number' => null,
        'name' => 'No',
        'surname' => 'Vat B',
    ]);

    $mockClient = $this->mock(MoloniClient::class);
    // No VAT lookup should happen for 999999990
    $mockClient->shouldNotReceive('post')
        ->with('customers/getByVat/', \Mockery::any());
    $mockClient->shouldReceive('post')
        ->with('customers/insert/', \Mockery::any())
        ->andReturn(['customer_id' => 100], ['customer_id' => 200]);

    $service = app(MoloniCustomerService::class);

    $customerIdA = $service->findOrCreate($individualA);
    $customerIdB = $service->findOrCreate($individualB);

    expect($customerIdA)->toBe(100);
    expect($customerIdB)->toBe(200);

    // Each individual has their own cache entry
    expect(MoloniCustomer::count())->toBe(2);
});
