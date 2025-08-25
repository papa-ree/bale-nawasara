<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Paparee\BaleNawasara\App\Jobs\SyncKumaJob;
use Paparee\BaleNawasara\App\Models\KumaMonitor;

class SyncKumaDnsRecordCommand extends Command
{
    protected $signature = 'nawasara:sync-dns-kuma';

    protected $description = 'Sync Kuma Monitors DNS Record (type http) to Kuma';

    public function handle()
    {
        $monitors = KumaMonitor::where('type', 'http')
            ->where('uptime_check_enabled', true)
            ->whereNull('kuma_id')
            ->get();

        if ($monitors->isEmpty()) {
            $this->warn('No unsynced Kuma monitors found.');

            return;
        }

        $monitors->each(function ($monitor): void {
            SyncKumaJob::dispatch($monitor->id);
        });

        $this->info("Dispatched {$monitors->count()} Kuma monitor jobs to queue.");
    }
}
