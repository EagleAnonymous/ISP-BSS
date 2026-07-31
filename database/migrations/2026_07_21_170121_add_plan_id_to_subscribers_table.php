<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('subscriber_id')->constrained()->nullOnDelete();
        });

        // The old `plan` column was a free string locked to these three
        // values. Only seed a matching Plan row if a subscriber actually
        // needs it backfilled — on a fresh install (or the test database)
        // there's nothing to migrate, and this shouldn't leave baseline
        // "Basic/Standard/Premium" plans lying around unconditionally.
        $legacyPlans = [
            'basic' => ['name' => 'Basic', 'speed' => '25 Mbps', 'price' => 999.00, 'billing_cycle' => 'monthly'],
            'standard' => ['name' => 'Standard', 'speed' => '50 Mbps', 'price' => 1499.00, 'billing_cycle' => 'monthly'],
            'premium' => ['name' => 'Premium', 'speed' => '100 Mbps', 'price' => 1999.00, 'billing_cycle' => 'monthly'],
        ];

        foreach ($legacyPlans as $key => $attributes) {
            $subscriberIds = DB::table('subscribers')->where('plan', $key)->pluck('id');

            if ($subscriberIds->isEmpty()) {
                continue;
            }

            $planId = DB::table('plans')->where('name', $attributes['name'])->value('id')
                ?? DB::table('plans')->insertGetId(array_merge($attributes, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

            DB::table('subscribers')->whereIn('id', $subscriberIds)->update(['plan_id' => $planId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
        });
    }
};
