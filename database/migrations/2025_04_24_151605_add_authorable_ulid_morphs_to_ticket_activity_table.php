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
        Schema::table('ticket_activity', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');

            $table->after('ticket_id', function (Blueprint $table) {
                $table->ulidMorphs('authorable');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_activity', function (Blueprint $table) {
            $table->dropMorphs('authorable');
            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });
    }
};
