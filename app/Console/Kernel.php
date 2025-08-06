<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CheckInactiveCompanies::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('companies:check-inactive')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
} 