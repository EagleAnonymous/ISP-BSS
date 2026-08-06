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
            $table->index('subscriber_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('created_by');
            $table->index(['status', 'assigned_to']);
        });

        Schema::table('technical_staff', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('subscriber_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->index('created_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['subscriber_id']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status', 'assigned_to']);
        });

        Schema::table('technical_staff', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['subscriber_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status']);
        });
    }
};
