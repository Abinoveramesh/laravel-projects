<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\AssignDriver;
use App\Console\Commands\NotifyIdealOrder;
use App\Console\Commands\CheckIdealDriverStatus;
use App\Console\Commands\DriverZoneAlertNotification;
use App\Console\Commands\RefundStatus;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        AssignDriver::class,
        NotifyIdealOrder::class,
        CheckIdealDriverStatus::class,
        DriverZoneAlertNotification::class,
        RefundStatus::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('notify:idealorder')->hourly();
        $schedule->command('check:idealdriverstatus')->everyFiveMinutes();
        $schedule->command('notify:driverzone')->everyFiveMinutes();
        // $schedule->command('refund:status')->cron('0 */3 * * *'); // every 3 hours
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
