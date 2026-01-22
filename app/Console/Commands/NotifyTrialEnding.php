<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Notifications\TrialEndingNotification;

class NotifyTrialEnding extends Command
{
    protected $signature = 'billing:notify-trial-ending';

    public function handle()
    {
        Subscription::whereNotNull('trial_ends_at')
            ->get()
            ->each(function ($subscription) {

                $daysLeft = now()->diffInDays($subscription->trial_ends_at, false);

                if (in_array($daysLeft, [3, 1])) {
                    $subscription
                        ->tenant
                        ->users()
                        ->wherePivot('role', 'owner')
                        ->each(function ($user) use ($subscription, $daysLeft) {
                            $user->notify(
                                new TrialEndingNotification(
                                    $subscription->tenant,
                                    $daysLeft
                                )
                            );
                        });
                }
            });
    }
}
