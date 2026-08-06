<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /* Add service address to subscribers. */
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->text('service_address')->nullable()->after('contact');
        });
    }

    /* Drop the service address column. */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('service_address');
        });
    }
};
