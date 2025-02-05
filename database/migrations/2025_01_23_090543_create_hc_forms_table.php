<?php

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
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
        Schema::create('hc_forms', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignUlid('default_group_id')
                ->nullable()
                ->constrained('groups')
                ->nullOnDelete();
            $table->string('default_ticket_priority')
                ->default(TicketPriority::NORMAL);
            $table->string('default_ticket_type')
                ->default(TicketType::TASK);
            $table->unsignedSmallInteger('sort')->default(1);
            $table->boolean('is_embeddable')->default(false);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hc_forms');
    }
};
