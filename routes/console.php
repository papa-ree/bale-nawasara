<?php

use Illuminate\Support\Facades\Schedule;
use Spatie\UptimeMonitor\Commands\CheckUptime;

Schedule::command(CheckUptime::class)->everyTenMinutes()->onOneServer()->runInBackground();
