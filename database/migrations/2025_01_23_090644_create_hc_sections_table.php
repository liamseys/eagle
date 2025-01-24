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
        Schema::create('hc_sections', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('category_id')
                ->constrained('hc_categories')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('sort')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hc_sections');
    }
};
