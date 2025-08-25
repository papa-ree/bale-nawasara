<?php

namespace Paparee\BaleNawasara\App\Services;

use Illuminate\Support\Facades\Cache;
use Paparee\BaleNawasara\App\Models\KumaMonitor;

class IpAddressMonitorService
{
    /**
     * Simpan IP Mikrotik ke table kuma_monitors
     */
    public function syncIpFromCache(): void
    {
        // Ambil dari cache
        $arpList = Cache::get('mikrotik_arp_list', []);

        if (empty($arpList) || ! is_array($arpList)) {
            logger()->warning('IpAddressMonitorService: mikrotik_arp_list kosong atau invalid.');

            return;
        }

        foreach ($arpList as $arp) {
            $address = $arp['address'] ?? null;

            if (! $address) {
                continue; // skip jika tidak ada IP
            }

            // Cek apakah sudah ada di database
            KumaMonitor::updateOrCreate(
                ['hostname' => $address], // unique
                [
                    'name' => $arp['comment'] ?? $address,
                    'type' => 'ping',
                    'url' => null, // untuk ping, biasanya pakai hostname saja
                    'method' => null,
                    'active' => 1,
                    'timeout' => 48,
                    'interval' => 60,
                    'retry_interval' => 60,
                    'resend_interval' => 0,
                    'expiry_notification' => 0,
                    'uptime_check_enabled' => 0,
                    'tags' => [1],
                    'notification_id_list' => [1, 2],
                ]
            );
        }
    }

    /**
     * Simpan IP Mikrotik ke table kuma_monitors
     */
    public function sendIpToMonitor($id, $address, $comment): void
    {
        KumaMonitor::updateOrCreate(
            ['ip_public_id' => $id], // unique
            [
                'name' => $comment ?? $address,
                'type' => 'ping',
                'url' => $address,
                'hostname' => $address,
                'method' => null,
                'active' => 1,
                'timeout' => 48,
                'interval' => 60,
                'retry_interval' => 60,
                'resend_interval' => 0,
                'expiry_notification' => 0,
                'uptime_check_enabled' => 0,
                'tags' => [1],
                'notification_id_list' => [1, 2],
            ]
        );
    }
}
