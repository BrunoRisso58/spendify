<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:create-transactions-from-recurrence')
    ->everyFourHours();

Schedule::command('alerts:process')->everyTenMinutes();