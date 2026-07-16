<?php

use Domain\Payments\Gateways\EasyPayGateway;
use Domain\Payments\Gateways\OfflineGateway;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Services\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the offline gateway supports any currency', function () {
    expect((new OfflineGateway)->supportedCurrencies())->toBe(['*']);
});

test('EasyPay only supports EUR', function () {
    expect((new EasyPayGateway)->supportedCurrencies())->toBe(['EUR']);
});

test('the manager filters gateways by currency', function () {
    config(['payment.gateways.easypay.enabled' => true]);
    $manager = PaymentGatewayManager::createFromConfig();

    expect($manager->gatewaysSupporting('EUR'))->toContain('offline', 'easypay');
    expect($manager->gatewaysSupporting('BRL'))->toContain('offline');
    expect($manager->gatewaysSupporting('BRL'))->not->toContain('easypay');
});

test('the payment method scope hides EUR-only methods under BRL', function () {
    config(['payment.gateways.easypay.enabled' => true, 'app.currency' => 'BRL']);

    PaymentMethod::factory()->create(['driver' => 'offline', 'is_enabled' => true, 'name' => 'Transferência']);
    PaymentMethod::factory()->create(['driver' => 'easypay', 'is_enabled' => true, 'name' => 'EasyPay']);

    $drivers = PaymentMethod::supportingCurrency()->pluck('driver');

    expect($drivers)->toContain('offline');
    expect($drivers)->not->toContain('easypay');
});

test('initiating payment with an unsupported gateway currency is rejected', function () {
    config(['payment.gateways.easypay.enabled' => true]);
    $this->artisan('db:seed --class=DocumentTypeSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');
    $this->actingAs(\App\Models\User::factory()->create(['active' => true]));

    $method = PaymentMethod::factory()->create(['driver' => 'easypay', 'is_enabled' => true]);

    config(['app.currency' => 'BRL']);
    $data = \Domain\Documents\DataTransferObject\DocumentData::fromArray([
        'customer_name' => 'X',
        'total_value' => 10,
    ]);
    $document = app(\Domain\Documents\Actions\CreateDocumentAction::class)($data, 'ORD', true);

    expect($document->currency)->toBe('BRL');

    expect(fn () => app(\Domain\Payments\Actions\InitiatePaymentAction::class)->execute($document, $method->id))
        ->toThrow(Exception::class, 'does not support BRL');
});
