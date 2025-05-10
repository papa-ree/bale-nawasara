<?php

use Illuminate\Support\Facades\Schedule;
use Paparee\BaleNawasara\App\Jobs\SyncDnsRecordsJob;

Schedule::job(new SyncDnsRecordsJob)->daily();
