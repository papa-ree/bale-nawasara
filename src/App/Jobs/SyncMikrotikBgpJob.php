<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Paparee\BaleNawasara\App\Services\MikrotikService;

class SyncMikrotikBgpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(MikrotikService $mikroTik): void
    {
        $arpList = $mikroTik->getArpList();

        Cache::put('mikrotik_arp_list', $arpList, now()->addMinutes(config('bale-nawasara.mikrotik.cache_lifetime')));
    }
}
