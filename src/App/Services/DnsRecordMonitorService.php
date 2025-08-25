<?php

namespace Paparee\BaleNawasara\App\Services;

use Illuminate\Support\Facades\Cache;
use Paparee\BaleNawasara\App\Models\KumaMonitor;

class DnsRecordMonitorService
{
    /**
     * Simpan DNS Record ke table kuma_monitors
     */
    // public function syncIpFromCache(): void
    // {
    //     // Ambil dari cache
    //     $arpList = Cache::get('mikrotik_arp_list', []);

    //     if (empty($arpList) || !is_array($arpList)) {
    //         logger()->warning('IpnameMonitorService: mikrotik_arp_list kosong atau invalid.');
    //         return;
    //     }

    //     foreach ($arpList as $arp) {
    //         $name = $arp['name'] ?? null;

    //         if (!$name) {
    //             continue; // skip jika tidak ada IP
    //         }

    //         // Cek apakah sudah ada di database
    //         KumaMonitor::updateOrCreate(
    //             ['hostname' => $name], // unique
    //             [
    //                 'name' => $arp['content'] ?? $name,
    //                 'type' => 'ping',
    //                 'url' => null, // untuk ping, biasanya pakai hostname saja
    //                 'method' => null,
    //                 'active' => 1,
    //                 'timeout' => 48,
    //                 'interval' => 60,
    //                 'retry_interval' => 60,
    //                 'resend_interval' => 0,
    //                 'expiry_notification' => 0,
    //                 'uptime_check_enabled' => 0,
    //                 'tags' => [1],
    //                 'notification_id_list' => [1, 2],
    //             ]
    //         );
    //     }
    // }

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
                'method' => null,
                'active' => 1,
                'timeout' => 48,
                'interval' => 60,
                'retry_interval' => 60,
                'resend_interval' => 0,
                'expiry_notification' => 1,
                'uptime_check_enabled' => 0,
                'tags' => [2],
                'notification_id_list' => [1, 2],
            ]
        );
    }
}
