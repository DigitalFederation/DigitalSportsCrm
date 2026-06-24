<?php

namespace Database\Factories;

use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $created = User::factory()->create();

        return [
            'type_id' => DocumentType::factory()->create(),
            'status_class' => $this->faker->word,
            'customer_name' => $this->faker->name,
            'tax_number' => $this->faker->numerify('###########'),
            'net_value' => $this->faker->randomFloat(2, 0, 999999.99),
            'tax_value' => $this->faker->randomFloat(2, 0, 999999.99),
            'tax_percentage' => $this->faker->randomFloat(2, 0, 100),
            'total_value' => $this->faker->randomFloat(2, 0, 999999.99),
            'method_id' => PaymentMethod::factory()->create(['driver' => 'offline']),
            'due_date' => $this->faker->date(),
            'notes' => $this->faker->paragraph,
            'created_by' => $created->id,
            'updated_by' => $created->id,
        ];
    }
}
