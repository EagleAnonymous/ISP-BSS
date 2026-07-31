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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            // Not DB-enum-locked: manual entries use cash/bank_transfer today,
            // but a future payment gateway would insert its own values here
            // without needing a schema change.
            $table->string('method');
            $table->string('reference_number')->nullable();
            $table->string('status')->default('successful');
            $table->timestamp('paid_at');
            // Who recorded it (manual entry). Null is reserved for a future
            // gateway-originated payment with no admin actor.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
