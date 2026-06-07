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
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('first_response_due_at')->nullable()->after('is_escalated');
            $table->timestamp('first_responded_at')->nullable()->after('first_response_due_at');
            $table->timestamp('resolution_due_at')->nullable()->after('first_responded_at');
            $table->timestamp('resolved_at')->nullable()->after('resolution_due_at');

            // Records which SLA alerts have already been sent so each fires once.
            $table->json('sla_alerts')->nullable()->after('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'first_response_due_at',
                'first_responded_at',
                'resolution_due_at',
                'resolved_at',
                'sla_alerts',
            ]);
        });
    }
};
