<?php

namespace App\Console\Commands;

class ApplyScheduledDowngrades extends Command
{
    protected $signature = 'billing:apply-downgrades';

    public function handle()
    {
        Subscription::whereNotNull('next_plan_id')
            ->where('ends_at', '<=', now())
            ->each(function ($subscription) {

                $subscription->update([
                    'plan_id' => $subscription->next_plan_id,
                    'next_plan_id' => null,
                ]);

                BillingLog::create([
                    'tenant_id' => $subscription->tenant_id,
                    'type' => 'downgrade_applied',
                ]);
            });
    }
}
