<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Paparee\BaleNawasara\App\Models\KumaMonitor;
use Paparee\BaleNawasara\App\Services\KumaProxyService;

class DeleteKumaUptimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public $monitorId)
    {
    }

    public function handle(KumaProxyService $kumaProxy): void
    {
        $kumaProxy->deleteMonitorById($this->monitorId);
    }
}
