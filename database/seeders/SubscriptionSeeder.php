<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::where('slug', 'free')->first();

        Tenant::all()->each(function ($tenant) use ($plan) {
            Subscription::firstOrCreate([
                'tenant_id' => $tenant->id,
            ], [
                'plan_id' => $plan->id,
                'status'  => 'active',
            ]);
        });
    }

}
