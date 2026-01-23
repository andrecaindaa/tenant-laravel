<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
       $schedule->command('billing:apply-downgrades')->hourly();

        $schedule->command('billing:notify-trial-ending')->daily();

          $schedule->command('billing:process-downgrades')->daily();
        $schedule->command('billing:check-trials')->daily();
    }
}
