<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Basic', 'speed' => '25 Mbps', 'price' => 999.00, 'billing_cycle' => 'monthly'],
            ['name' => 'Standard', 'speed' => '50 Mbps', 'price' => 1499.00, 'billing_cycle' => 'monthly'],
            ['name' => 'Premium', 'speed' => '100 Mbps', 'price' => 1999.00, 'billing_cycle' => 'monthly'],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
