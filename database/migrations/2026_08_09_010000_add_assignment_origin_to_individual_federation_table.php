<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('individual_federation', function (Blueprint $table) {
            $table->string('assignment_source', 30)->nullable()->after('status_class')->index();
            $table->foreignId('assignment_entity_id')
                ->nullable()
                ->after('assignment_source')
                ->constrained('entity')
                ->nullOnDelete();
            $table->foreignId('assignment_zone_id')
                ->nullable()
                ->after('assignment_entity_id')
                ->constrained('zones')
                ->nullOnDelete();
            $table->foreignId('assignment_district_id')
                ->nullable()
                ->after('assignment_zone_id')
                ->constrained('districts')
                ->nullOnDelete();
            $table->timestamp('assigned_at')->nullable()->after('assignment_district_id');
        });
    }

    public function down(): void
    {
        Schema::table('individual_federation', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_district_id');
            $table->dropConstrainedForeignId('assignment_zone_id');
            $table->dropConstrainedForeignId('assignment_entity_id');
            $table->dropIndex(['assignment_source']);
            $table->dropColumn(['assignment_source', 'assigned_at']);
        });
    }
};
