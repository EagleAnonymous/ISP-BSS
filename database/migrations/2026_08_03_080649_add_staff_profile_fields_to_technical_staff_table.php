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
        Schema::table('technical_staff', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->after('user_id');
            $table->string('role')->nullable()->after('employee_id');
            $table->string('location')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technical_staff', function (Blueprint $table) {
            $table->dropColumn(['employee_id', 'role', 'location']);
        });
    }
};
