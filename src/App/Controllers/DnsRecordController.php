<?php

namespace Paparee\BaleNawasara\App\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SyncDnsRecordsJob;
use Illuminate\Support\Facades\Cache;

class DnsRecordController extends Controller
{
    public function sync()
    {
        if (Cache::get('dns_sync_status') === 'syncing') {
            return response()->json(['message' => 'Sinkronisasi sedang berlangsung'], 409);
        }

        Cache::put('dns_sync_status', 'syncing', now()->addMinutes(10));
        SyncDnsRecordsJob::dispatch();

        return response()->json(['message' => 'Sinkronisasi DNS dimulai']);
    }

    public function status()
    {
        return response()->json([
            'syncing' => Cache::get('dns_sync_status') === 'syncing',
        ]);
    }
}
