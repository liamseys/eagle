<?php

use App\Enums\Tickets\TicketSlaStatus;
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
        Schema::create('ticket_slas', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('ticket_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUlid('group_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('type');
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->string('status')->default(TicketSlaStatus::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_slas');
    }
};
