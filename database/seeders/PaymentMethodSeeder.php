<?php

namespace Database\Seeders;

use Domain\Payments\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Bank Transfer',
                'driver' => 'offline',
                'instructions' => 'Use the following information to finish the payment:<br><br>Bank details for wire transfer:<br>Example Bank<br>To: Your Federation<br><strong>IBAN Code:</strong> XX00 0000 0000 0000 0000 0000 000 <br><strong>BIC Code: </strong> XXXXXXXX',
                'handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler',
            ],
            [
                'name' => 'EasyPay',
                'driver' => 'easypay',
                'instructions' => 'Secure online payment with credit card, Multibanco, MBWay, and other methods',
                'handler' => 'Domain\Payments\Handlers\EasyPayPaymentHandler',
            ],
        ];

        foreach ($types as $type) {
            PaymentMethod::create($type);
        }
    }
}
