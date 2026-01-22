<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\BillingLog;

class ApplyScheduledDowngrades extends Command
{
    protected $signature = 'billing:apply-downgrades';

    protected $description = 'Apply scheduled plan downgrades at the end of billing cycle';

    public function handle()
    {
        Subscription::whereNotNull('next_plan_id')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->each(function (Subscription $subscription) {

                $fromPlan = $subscription->plan_id;
                $toPlan   = $subscription->next_plan_id;

                $subscription->update([
                    'plan_id'      => $toPlan,
                    'next_plan_id' => null,
                ]);

                BillingLog::create([
                    'tenant_id' => $subscription->tenant_id,
                    'from_plan' => $fromPlan,
                    'to_plan'   => $toPlan,
                    'type'      => 'downgrade_applied',
                ]);
            });

        $this->info('Scheduled downgrades applied successfully.');
    }
}
