<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
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
    })
    ->create();
