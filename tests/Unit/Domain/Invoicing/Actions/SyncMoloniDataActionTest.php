<?php

use Domain\Invoicing\Actions\SyncMoloniDataAction;
use Domain\Invoicing\Models\MoloniSetting;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Invoicing\Models\MoloniToken;
use Domain\Invoicing\Services\MoloniClient;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);
});

test('syncs companies from moloni api', function () {
    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('companies/getAll/')
        ->andReturn([
            [
                'company_id' => 123,
                'name' => 'Test Company',
                'vat' => 'PT123456789',
            ],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $result = $action();

    expect($result['companies'])->toBe(1);

    $companiesCache = MoloniSetting::getValue('companies_cache');
    expect($companiesCache)->toBeArray();
    expect($companiesCache[0]['id'])->toBe(123);
    expect($companiesCache[0]['name'])->toBe('Test Company');
});

test('syncs document sets from moloni api', function () {
    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('companies/getAll/')
        ->andReturn([]);

    $mockClient->shouldReceive('post')
        ->with('documentSets/getAll/')
        ->andReturn([
            [
                'document_set_id' => 1,
                'name' => 'Faturas',
                'abbreviation' => 'FR',
                'template_id' => 1,
            ],
            [
                'document_set_id' => 2,
                'name' => 'Recibos',
                'abbreviation' => 'RC',
                'template_id' => 15,
            ],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $result = $action();

    expect($result['document_sets'])->toBe(2);

    $cache = MoloniSetting::getValue('document_sets_cache');
    expect($cache)->toBeArray();
    expect(count($cache))->toBe(2);
});

test('syncs taxes from moloni api', function () {
    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('taxes/getAll/')
        ->andReturn([
            [
                'tax_id' => 1,
                'name' => 'IVA 23%',
                'value' => 23,
                'type' => 1,
            ],
            [
                'tax_id' => 2,
                'name' => 'IVA 13%',
                'value' => 13,
                'type' => 1,
            ],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $result = $action();

    expect($result['taxes'])->toBe(2);

    $cache = MoloniSetting::getValue('taxes_cache');
    expect($cache)->toBeArray();
    expect($cache[0]['id'])->toBe(1);
    expect($cache[0]['value'])->toBe(23);
});

test('syncs units from moloni api', function () {
    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('measurementUnits/getAll/')
        ->andReturn([
            [
                'unit_id' => 1,
                'name' => 'Unidade',
                'abbreviation' => 'un',
            ],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $result = $action();

    expect($result['units'])->toBe(1);

    $cache = MoloniSetting::getValue('units_cache');
    expect($cache[0]['abbreviation'])->toBe('un');
});

test('syncs categories with nested structure', function () {
    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('productCategories/getAll/', ['parent_id' => 0])
        ->andReturn([
            [
                'category_id' => 1,
                'name' => 'Services',
                'child_categories' => [
                    [
                        'category_id' => 2,
                        'name' => 'Training',
                        'child_categories' => [],
                    ],
                ],
            ],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $result = $action();

    expect($result['categories'])->toBe(1);

    $cache = MoloniSetting::getValue('categories_cache');
    expect(count($cache))->toBe(2);
    expect($cache[0]['name'])->toBe('Services');
    expect($cache[1]['name'])->toBe('Services > Training');
    expect($cache[1]['level'])->toBe(1);
});

test('syncs payment methods from moloni api', function () {
    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('paymentMethods/getAll/')
        ->andReturn([
            [
                'payment_method_id' => 1,
                'name' => 'Numerario',
            ],
            [
                'payment_method_id' => 2,
                'name' => 'Transferencia Bancaria',
            ],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $result = $action();

    expect($result['payment_methods'])->toBe(2);
});

test('syncs maturity dates from moloni api', function () {
    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('maturityDates/getAll/')
        ->andReturn([
            [
                'maturity_date_id' => 1,
                'name' => 'Pronto Pagamento',
                'days' => 0,
            ],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $result = $action();

    expect($result['maturity_dates'])->toBe(1);

    expect(MoloniSetting::getValue('default_maturity_date_id'))->toBe(1);
});

test('creates sync log on success', function () {
    $mockClient = $this->mock(MoloniClient::class);
    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $action();

    $log = MoloniSyncLog::where('sync_type', 'data_sync')
        ->where('status', 'success')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->duration_ms)->toBeGreaterThanOrEqual(0);
});

test('throws exception and creates failure log on api error', function () {
    $mockClient = $this->mock(MoloniClient::class);
    $mockClient->shouldReceive('post')
        ->with('companies/getAll/')
        ->andThrow(new \RuntimeException('API Error'));

    $action = app(SyncMoloniDataAction::class);

    expect(fn () => $action())->toThrow(\RuntimeException::class);

    $log = MoloniSyncLog::where('sync_type', 'data_sync')
        ->where('status', 'failed')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->error_message)->toContain('API Error');
});

test('sets default company id when none configured', function () {
    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('companies/getAll/')
        ->andReturn([
            ['company_id' => 999, 'name' => 'Default Company'],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $action();

    expect(MoloniSetting::getValue('company_id'))->toBe(999);
});

test('does not override existing company id', function () {
    MoloniSetting::setValue('company_id', 111, 'int');

    $mockClient = $this->mock(MoloniClient::class);

    $mockClient->shouldReceive('post')
        ->with('companies/getAll/')
        ->andReturn([
            ['company_id' => 999, 'name' => 'New Company'],
        ]);

    $mockClient->shouldReceive('post')->andReturn([]);

    $action = app(SyncMoloniDataAction::class);
    $action();

    expect(MoloniSetting::getValue('company_id'))->toBe(111);
});
