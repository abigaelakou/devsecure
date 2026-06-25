<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Toutes les minutes — soumet les devoirs expirés (filet de sécurité)
Schedule::command('devoirs:soumettre-expires')->everyMinute();

Schedule::command('notifications:rappels-echeance')->dailyAt('08:00');
Schedule::command('devoirs:soumettre-expires')->everyMinute();


