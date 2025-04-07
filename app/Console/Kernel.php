<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Scrape high engagement profiles (100k+ likes) every 24 hours
        $schedule->command('scrape:high-engagement')
            ->daily()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/high-engagement-scraper.log'));

        // Scrape regular profiles (less than 100k likes) every 72 hours
        $schedule->command('scrape:regular')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/regular-scraper.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 