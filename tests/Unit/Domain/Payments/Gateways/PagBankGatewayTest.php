<?php

namespace Tests\Unit\Domain\Payments\Gateways;

use Domain\Payments\Gateways\PagBankGateway;
use PHPUnit\Framework\TestCase;

final class PagBankGatewayTest extends TestCase
{
    private function gateway(): PagBankGateway
    {
        $gateway = new PagBankGateway;

        $gateway->configure([
            'token' => 'sandbox-token',
            'sandbox' => true,
        ]);

        return $gateway;
    }

    public function test_it_validates_the_pagbank_webhook_authenticity_token_using_the_raw_payload(): void
    {
        $payload = '{"id":"CHAR_TEST","status":"PAID"}';
        $signature = hash('sha256', 'sandbox-token-' . $payload);

        $this->assertTrue(
            $this->gateway()->validateWebhookSignature(
                ['x-authenticity-token' => [$signature]],
                $payload
            )
        );
    }

    public function test_it_rejects_an_invalid_pagbank_webhook_authenticity_token(): void
    {
        $this->assertFalse(
            $this->gateway()->validateWebhookSignature(
                ['x-authenticity-token' => ['invalid']],
                '{"id":"CHAR_TEST","status":"PAID"}'
            )
        );
    }

    public function test_it_rejects_a_pagbank_webhook_without_an_authenticity_token(): void
    {
        $this->assertFalse(
            $this->gateway()->validateWebhookSignature(
                [],
                '{"id":"CHAR_TEST","status":"PAID"}'
            )
        );
    }
}
