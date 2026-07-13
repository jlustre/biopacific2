<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run-scheduled')
    ->dailyAt(config('backup.schedule.time', '02:00'))
    ->timezone(config('backup.schedule.timezone', config('app.timezone')))
    ->when(fn () => (bool) config('backup.schedule.enabled', false))
    ->name('backup-nightly')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('reports:run-scheduled')
    ->everyMinute()
    ->name('reports-run-scheduled')
    ->withoutOverlapping();
