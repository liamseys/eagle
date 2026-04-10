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
        Schema::table('hc_articles', function (Blueprint $table) {
            $table->fullText(['title', 'description', 'body'], 'hc_articles_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hc_articles', function (Blueprint $table) {
            $table->dropFullText('hc_articles_fulltext');
        });
    }
};
