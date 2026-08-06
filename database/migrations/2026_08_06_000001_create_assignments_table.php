<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('technical_staff')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('assigned_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'staff_id', 'status']);
            $table->index(['staff_id', 'status']);
            $table->index(['ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
