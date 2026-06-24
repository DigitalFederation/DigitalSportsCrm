<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reference data the application requires to function.
 *
 * Before the migration history was squashed into a schema dump, this data was
 * inserted by individual create-table migrations. A schema dump captures
 * structure only, so this migration re-seeds the essential rows. It runs after
 * the schema dump on every fresh database (including the test database), and is
 * idempotent so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Member-number counters (App\...\MemberNumberService reads these).
        foreach ([
            ['key' => 'individual_counter', 'description' => 'Current counter for individual member numbers'],
            ['key' => 'entity_counter', 'description' => 'Current counter for entity member numbers'],
        ] as $row) {
            DB::table('member_number_settings')->updateOrInsert(
                ['key' => $row['key']],
                ['value' => 1, 'description' => $row['description'], 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    public function down(): void
    {
        DB::table('member_number_settings')->whereIn('key', ['individual_counter', 'entity_counter'])->delete();
    }
};
