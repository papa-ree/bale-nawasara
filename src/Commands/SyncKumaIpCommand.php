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

        $delaySeconds = 0; // mulai dari 0 detik

        $monitors->each(function ($monitor) use (&$delaySeconds): void {
            // kasih delay berjenjang, misalnya tiap monitor 5 detik
            SyncKumaJob::dispatch($monitor->id)->delay(now()->addSeconds($delaySeconds));

            $delaySeconds += 3; // jeda 3 detik antar job
        });

        $this->info("Dispatched {$monitors->count()} Kuma monitor jobs to queue.");
    }
}
