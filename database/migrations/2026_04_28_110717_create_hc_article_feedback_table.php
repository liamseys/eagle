<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hc_article_feedback', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('article_id')->constrained('hc_articles')->cascadeOnDelete();
            $table->string('value');
            $table->timestamp('created_at')->nullable();

            $table->index(['article_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hc_article_feedback');
    }
};
