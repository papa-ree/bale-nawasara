<?php

namespace Paparee\BaleNawasara\App\Services;

use Paparee\BaleNawasara\App\Models\KumaMonitor;
use Paparee\BaleNawasara\App\Models\UptimeKumaMonitor;

class UptimeKumaService
{
    public function updateComment($uptimeKumaMonitorId, string $comment)
    {
        UptimeKumaMonitor::find($uptimeKumaMonitorId)->update([
            'name' => $comment,
        ]);

        return true;
    }
}
