<?php

namespace Database\Seeders;

use Domain\Documents\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Invoice',
                'code' => 'INV',
                'prefix' => 'INV',
            ],
            [
                'name' => 'Receipt',
                'code' => 'RCP',
                'prefix' => 'RCP',
            ],
            [
                'name' => 'Payment',
                'code' => 'PAY',
                'prefix' => 'PMT',
            ],
            [
                'name' => 'Order',
                'code' => 'ORD',
                'prefix' => 'ORD',
            ],
            [
                'name' => 'Proforma',
                'code' => 'PROFORMA',
                'prefix' => 'PRF',
            ],
        ];

        foreach ($types as $type) {
            DocumentType::create($type);
        }
    }
}
