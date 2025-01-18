<?php

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
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
        Schema::create('tickets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('requester_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->foreignUlid('assignee_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignUlid('group_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->unsignedInteger('ticket_id')
                ->unique();
            $table->string('subject');
            $table->string('priority')
                ->default(TicketPriority::NORMAL);
            $table->string('type');
            $table->string('status')
                ->default(TicketStatus::OPEN);
            $table->boolean('is_escalated')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
