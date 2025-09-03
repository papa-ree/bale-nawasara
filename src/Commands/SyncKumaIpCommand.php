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

        $monitors->chunk(5)->each(function ($chunk, $batchIndex) {
            $chunk->each(function ($monitor, $index) use ($batchIndex) {
                // jeda berdasarkan batch + urutan monitor dalam batch
                $delay = now()->addSeconds(($batchIndex * 60) + ($index * 3));

                SyncKumaJob::dispatch($monitor->id)->delay($delay);
            });
        });

        $this->info("Dispatched {$monitors->count()} Kuma monitor jobs to queue.");
    }
}
