<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:send-task-reminders')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

Schedule::command('erp:check-reminders')
    ->dailyAt('07:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

Schedule::command('app:send-kasir-deposit-reminder')
    ->cron('0 0 */2 * *')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

Schedule::command('erp:run-retention')
    ->monthlyOn(1, '02:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

Schedule::command('automation:calculate-backflush')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('automation:send-reminders')
    ->everyTenMinutes()
    ->withoutOverlapping();
