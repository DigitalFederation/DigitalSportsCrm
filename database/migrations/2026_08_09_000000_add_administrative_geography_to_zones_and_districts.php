<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->foreignId('country_id')
                ->nullable()
                ->after('id')
                ->constrained('country')
                ->nullOnDelete();
            $table->string('kind', 40)->default('operational')->after('code');
            $table->string('external_code', 50)->nullable()->after('kind');
            $table->unique(
                ['country_id', 'kind', 'external_code'],
                'zones_country_kind_external_unique'
            );
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->foreignId('administrative_zone_id')
                ->nullable()
                ->after('country_id')
                ->constrained('zones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('districts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('administrative_zone_id');
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->dropUnique('zones_country_kind_external_unique');
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn(['kind', 'external_code']);
        });
    }
};
