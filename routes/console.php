<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-expired --hours=24')
    ->daily();

Schedule::command('tus:prune')
    ->hourly()->runInBackground();

Schedule::command('app:version:reindex')
    ->everyFiveMinutes()->withoutOverlapping();
