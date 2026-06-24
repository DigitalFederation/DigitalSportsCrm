<?php

namespace Database\Factories;

use Domain\Documents\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentType>
 */
class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
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
        ];

        return $types[array_rand($types)];
    }
}
