<?php


use Illuminate\Support\Facades\Schedule;
use Paparee\BaleNawasara\App\Jobs\SyncDnsRecordsJob;
use Spatie\UptimeMonitor\Commands\CheckUptime;

Schedule::job(new SyncDnsRecordsJob())->daily();
Schedule::command(CheckUptime::class)->everyFiveMinutes();