<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivingLicenseCertificationRequirementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Based on PM requirements:
     * 1. Centro de Mergulho: Mergulhador de Nível 3, Instrutor Nível 1, 2 ou 3.
     * 2. Escola de Mergulho: Instrutor Nível 2 ou 3
     * 3. Aluguer de Equipamento: Mergulhador de Nível 3, Instrutor Nível 1, 2 ou 3.
     * 4. Estação de enchimento e Fornecimento de Misturas Respiratórias: Operador de Compressores
     * 5. Aluguer de Equipamento: Mergulhador de Nível 3, Instrutor Nível 1, 2 ou 3.
     */
    public function run(): void
    {
        // Clear existing requirements to avoid duplicates
        DB::table('license_required_certifications')->delete();

        $requirements = [
            // Centro de Mergulho (id: 10)
            [
                'license_id' => 10,
                'certification_id' => null, // No specific CMAS certification required, just national level
                'requester_type' => 'technical_director',
                'certification_level' => 'diver_level_3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 10,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 10,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 10,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_3',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Escola de Mergulho (id: 11)
            [
                'license_id' => 11,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 11,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_3',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Enchimento e Fornecimento de Misturas Respira (id: 12)
            [
                'license_id' => 12,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'compressor_operator',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Aluguer de Equipamento (id: 13)
            [
                'license_id' => 13,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'diver_level_3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 13,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 13,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 13,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_3',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Licença Centro de Mergulho international (id: 15) - same as Centro de Mergulho
            [
                'license_id' => 15,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'diver_level_3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 15,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 15,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 15,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_3',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Licença de Escola de Mergulho international (id: 16) - same as Escola de Mergulho
            [
                'license_id' => 16,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_id' => 16,
                'certification_id' => null,
                'requester_type' => 'technical_director',
                'certification_level' => 'instructor_level_3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('license_required_certifications')->insert($requirements);

        $this->command->info('Diving license certification requirements seeded successfully!');
    }
}
