<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ProcessScheduledDowngrades extends Command
{
    protected $signature = 'billing:process-downgrades';
    protected $description = 'Processa downgrades agendados';

    public function handle()
    {
        $subscriptions = Subscription::whereNotNull('next_plan_id')
            ->where('next_plan_starts_at', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->applyScheduledDowngrade()) {
                $this->info("Downgrade aplicado para tenant {$subscription->tenant_id}");
            }
        }

        $this->info("Processados {$subscriptions->count()} downgrades.");
    }
}
