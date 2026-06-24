<?php

namespace Database\Seeders;

use Domain\Attachments\Models\AttachmentCategory;
use Illuminate\Database\Seeder;

class AttachmentCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Administrativo',
            'Circular',
            'Ofício',
            'Parecer',
            'Manual',
            'Tabela',
            'Regulamento',
            'Norma',
            'Programa',
            'Formulário',
            'Ata',
            'Material Pedagógico',
        ];

        foreach ($categories as $name) {
            AttachmentCategory::firstOrCreate(['name' => $name]);
        }
    }
}
