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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained();
            $table->string('invoice_number')->unique();
            // Original billed amount, snapshotted from subscriptions.locked_price.
            // Never mutated after creation — adjustments are append-only rows,
            // never edits to this column. See Invoice::amountDue().
            $table->decimal('amount', 8, 2);
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->date('due_date');
            // Only two stored ground-truth states. "Overdue" is derived on read
            // (unpaid + due_date passed) rather than stored, so it's always
            // correct without depending on a scheduled job to flip it.
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
