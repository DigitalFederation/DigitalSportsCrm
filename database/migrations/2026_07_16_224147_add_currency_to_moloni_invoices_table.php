<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('moloni_invoices', function (Blueprint $table) {
            $table->char('currency', 3)->default('EUR')->after('moloni_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moloni_invoices', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
