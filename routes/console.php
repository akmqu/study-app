<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command(
    'inspire',
    function () {
        $this->comment(
            Inspiring::quote()
        );
    }
)->purpose(
    'Display an inspiring quote'
);

/*
|--------------------------------------------------------------------------
| Lessons
|--------------------------------------------------------------------------
*/

Schedule::command(
    'lessons:complete-past'
)
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command(
    'lessons:send-reminders'
)
    ->everyMinute()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
|
| On the first day of every month we generate invoices
| for completed monthly lessons from the previous month.
|
*/

Schedule::command(
    'payments:generate-monthly'
)
    ->monthlyOn(
        1,
        '00:05'
    )
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Horizon
|--------------------------------------------------------------------------
*/

Schedule::command(
    'horizon:snapshot'
)
    ->everyFiveMinutes();