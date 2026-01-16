<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
            'limits' => [
                'projects' => 1,
                'users' => 2,
            ],
        ]);

        Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 29,
            'interval' => 'monthly',
            'limits' => [
                'projects' => 10,
                'users' => 10,
            ],
        ]);
    }
}
