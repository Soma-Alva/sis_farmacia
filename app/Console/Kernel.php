<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Ejecutar respaldo automático cada día a las 2:00 AM
        $schedule->command('backup:database')->dailyAt('13:35')->withoutOverlapping();

        // Opcional: ejecutar cada 6 horas
        // $schedule->command('backup:database')->everyHours(6)->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
