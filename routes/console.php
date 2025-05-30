<?php

use App\Console\Commands\SyncDnsRecord;
use Illuminate\Support\Facades\Schedule;
use Spatie\UptimeMonitor\Commands\CheckUptime;

Schedule::command(SyncDnsRecord::class)->daily()->onOneServer()->runInBackground();
Schedule::command(CheckUptime::class)->everyTenMinutes()->onOneServer()->runInBackground();
