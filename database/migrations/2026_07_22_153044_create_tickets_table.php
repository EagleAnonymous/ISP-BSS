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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();

            $table->enum('category', [
                'no_connection', 'slow_connection', 'billing_concern',
                'installation_request', 'equipment_issue', 'other',
            ]);
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // open -> assigned -> in_progress -> resolved -> closed.
            // See Ticket status-transition comments in the controllers for who can move it between each state.
            $table->enum('status', ['open', 'assigned', 'in_progress', 'resolved', 'closed'])->default('open');

            // Null until a technical staff member claims it from the shared queue.
            $table->foreignId('assigned_to')->nullable()->constrained('technical_staff')->nullOnDelete();

            // Who logged the ticket. Nullable to match the same pattern used
            // by payments.created_by.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('resolution_notes')->nullable();

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
