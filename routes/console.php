<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Marca lotes vencidos todos los días a la 1:00 AM. Requiere que el
// cron del servidor tenga configurado el scheduler de Laravel
// (`* * * * * php artisan schedule:run`) para que esto corra solo.
Schedule::command('lotes:marcar-vencidos')->dailyAt('01:00');

// Respaldo automático de la base de datos todos los días a las 2:00 AM
// (conserva los últimos 30). Requiere el mismo scheduler que arriba.
Schedule::command('backup:database --auto')->dailyAt('02:00')->withoutOverlapping();
