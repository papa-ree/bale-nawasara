<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Paparee\BaleNawasara\App\Models\KumaMonitor;
use Paparee\BaleNawasara\App\Services\KumaProxyService;

class SyncKumaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $monitorId) {}

    public function handle(KumaProxyService $kumaProxy): void
    {
        $monitor = KumaMonitor::find($this->monitorId);

        if (! $monitor) {
            logger()->warning("SyncKumaJob: Monitor {$this->monitorId} not found.");

            return;
        }

        $kumaProxy->addMonitor($monitor);
    }
}
