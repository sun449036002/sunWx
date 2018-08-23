<?php

namespace App\Console;

use App\Console\Commands\NotifyRedPackExpiredCommand;
use App\Console\Commands\TestCommand;
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
        NotifyRedPackExpiredCommand::class,
        TestCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command("sym:test")->everyMinute()->withoutOverlapping()->appendOutputTo(storage_path() . "/logs/test-" . date("Ymd") . ".log");
        $schedule->command("NotifyRedPackExpiredCommand --type=normal")->everyMinute()->withoutOverlapping()->appendOutputTo(storage_path() . "/logs/command/NotifyRedPackExpiredCommand-normal/" . date("Ymd") . ".log");
        $schedule->command("NotifyRedPackExpiredCommand --type=use")->everyMinute()->withoutOverlapping()->appendOutputTo(storage_path() . "/logs/command/NotifyRedPackExpiredCommand-use/" . date("Ymd") . ".log");
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
