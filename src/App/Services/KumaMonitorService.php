<?php

namespace Paparee\BaleNawasara\App\Services;

use Illuminate\Support\Facades\Cache;
use Paparee\BaleNawasara\App\Jobs\UpdateKumaMonitorNameJob;
use Paparee\BaleNawasara\App\Models\KumaMonitor;

class KumaMonitorService
{
    public function updateComment(KumaMonitor $monitor, string $comment, ?int $ipPublicId = null): KumaMonitor
    {
        $data = [
            'name' => $comment
        ];

        if ($ipPublicId) {
            $data['ip_public_id'] = $ipPublicId;
        }

        $monitor->update($data);

        return $monitor;
    }

    /**
     * Simpan IP Mikrotik ke table kuma_monitors
     */
    public function syncIpFromCache(): void
    {
        // Ambil dari cache
        $arpList = Cache::get('mikrotik_arp_list', []);

        if (empty($arpList) || !is_array($arpList)) {
            logger()->warning('IpAddressMonitorService: mikrotik_arp_list kosong atau invalid.');

            return;
        }

        foreach ($arpList as $arp) {
            $address = $arp['address'] ?? null;

            if (!$address) {
                continue; // skip jika tidak ada IP
            }

            // Cek apakah sudah ada di database
            KumaMonitor::updateOrCreate(
                [
                    'hostname' => $address,
                ], // unique
                [
                    'ip_public_id' => $arp['.id'],
                    'url' => $address,
                    'name' => $arp['comment'] ?? $address,
                    'type' => 'ping',
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
                'tags' => [1],
                'notification_id_list' => [1, 2],
            ]
        );
    }
}
