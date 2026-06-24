<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $languages = include database_path('data/languages.php');

        foreach ($languages as $iso => $lang) {
            Language::firstOrCreate([
                'iso' => $iso,
            ], [
                'name' => $lang,
            ]);
        }
    }
}
