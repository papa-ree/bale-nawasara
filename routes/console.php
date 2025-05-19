<?php

use Illuminate\Support\Facades\Schedule;
use Paparee\BaleNawasara\App\Jobs\SyncDnsRecordsJob;
use Paparee\BaleNawasara\Commands\CacheMikroTikArp;
use Spatie\UptimeMonitor\Commands\CheckUptime;

Schedule::job(new SyncDnsRecordsJob())->daily();
Schedule::command(CheckUptime::class)->everyTenMinutes();
// Schedule::command(CacheMikroTikArp::class)->everyFiveMinutes();