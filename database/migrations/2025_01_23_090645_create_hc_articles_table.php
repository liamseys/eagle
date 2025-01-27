<?php

use App\Enums\HelpCenter\Articles\ArticleStatus;
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
        Schema::create('hc_articles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('author_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUlid('section_id')
                ->constrained('hc_sections')
                ->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('body');
            $table->string('status')->default(ArticleStatus::DRAFT);
            $table->unsignedSmallInteger('sort')->default(1);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hc_articles');
    }
};
