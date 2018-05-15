<?php

namespace App\Console;


use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $filePath = env('APP_PATH',false).'cron.log';

        $schedule->command('delete:report')
            ->hourlyAt(1)
            ->appendOutputTo($filePath);

        $schedule->command('make:report')
            ->hourlyAt(3)
            ->appendOutputTo($filePath);

        $schedule->command('download:images')
            ->hourlyAt(6)
            ->appendOutputTo($filePath);

        $schedule->command('write:report')
            ->hourlyAt(11)
            ->appendOutputTo($filePath);

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
