<?php

namespace Paparee\BaleNawasara\App\Services;

use Paparee\BaleNawasara\App\Models\KumaMonitor;

class DnsRecordMonitorService
{
    /**
     * Simpan DNS Record ke table kuma_monitors
     */
    public function sendDnsRecordToMonitor($id, $name): void
    {
        $url = "https://{$name}";
        $kuma_name = explode('.ponorogo.go.id', $name);

        KumaMonitor::updateOrCreate(
            [
                'dns_record_id' => $id,
            ], // unique
            [
                'name' => $kuma_name[0],
                'type' => 'http',
                'url' => $url,
                'hostname' => $url,
                'max_retries' => 3,
                'expiry_notification' => 1,
                'tags' => [2],
                'notification_id_list' => [1, 2],
            ]
        );
    }
}
