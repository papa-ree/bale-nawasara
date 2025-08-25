<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Paparee\BaleNawasara\App\Jobs\SyncKumaJob;
use Paparee\BaleNawasara\App\Models\KumaMonitor;

class SyncKumaIpCommand extends Command
{
    protected $signature = 'nawasara:sync-ip-kuma';

    protected $description = 'Sync Kuma Monitors IP (type ping) to Kuma';

    public function handle()
    {
        $monitors = KumaMonitor::where('type', 'ping')
            ->where('uptime_check_enabled', true)
            ->whereNull('kuma_id')
            ->get();

        if ($monitors->isEmpty()) {
            $this->warn('No unsynced Kuma monitors found.');
            return;
        }

        $monitors->each(function ($monitor, $index): void {
            // jeda 5 detik per urutan
            $delay = now()->addSeconds($index * 5);

            SyncKumaJob::dispatch($monitor->id)->delay($delay);
        });

        $this->info("Dispatched {$monitors->count()} Kuma monitor jobs to queue.");
    }
}
