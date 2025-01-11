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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('form_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('label');
            $table->string('description')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->unsignedSmallInteger('sort')->default(1);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['form_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
