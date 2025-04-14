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
        Schema::table('hc_forms', function (Blueprint $table) {
            $table->foreignUlid('section_id')
                ->after('user_id')
                ->nullable()
                ->constrained('hc_sections')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hc_forms', function (Blueprint $table) {
            $table->dropColumn('section_id');
        });
    }
};
