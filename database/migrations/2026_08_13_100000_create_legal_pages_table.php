<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40)->comment('terms-of-service | privacy-policy | data-sharing-policy');
            $table->string('locale', 10);
            $table->unsignedInteger('version')->default(1);
            $table->string('title');
            $table->mediumText('body')->comment('Sanitized HTML, may contain {{placeholder}} tokens');
            $table->date('effective_date')->nullable();
            $table->timestamp('published_at')->nullable()->comment('NULL = draft');
            // users.id is char(36) (UUID) in this schema, not an auto-increment
            // bigint — foreignId()/foreignUuid() must match or the FK is rejected.
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'locale', 'version']);
            $table->index(['type', 'locale', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
