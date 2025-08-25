<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Paparee\BaleNawasara\App\Models\IpAddress;
use Paparee\BaleNawasara\App\Models\IpPublic;
use Paparee\BaleNawasara\App\Models\KumaMonitor;
use Paparee\BaleNawasara\App\Services\IpAddressMonitorService;
use Paparee\BaleNawasara\App\Services\MikrotikService;

class SyncMikrotikBgpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5; // Jumlah maksimal percobaan job

    public int $backoff = 10; // Waktu jeda antar retry (detik)

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Cache::put('mikrotik_sync_timestamp', now());

            $mikrotik = new MikrotikService;

            // Ambil IP Address dan cache-kan
            $addresses = $mikrotik->getIpAddresses();
            Cache::put('mikrotik_address_list', $addresses);

            foreach ($addresses as $addr) {
                IpAddress::updateOrCreate(
                    ['id' => $addr['.id']],
                    [
                        'address' => $addr['address'],
                        'network' => $addr['network'] ?? null,
                        'interface' => $addr['interface'] ?? null,
                        'actual_interface' => $addr['actual-interface'] ?? null,
                        'invalid' => $addr['invalid'] ?? null,
                        'dynamic' => $addr['dynamic'] ?? null,
                        'disabled' => $addr['disabled'] ?? null,
                        'comment' => $addr['comment'] ?? null,
                    ]
                );
            }

            // Ambil ARP List dan cache-kan
            $arpList = $mikrotik->getArpLists();
            Cache::put('mikrotik_arp_list', $arpList);

            // Kumpulkan semua ID yang masih aktif dari Mikrotik
            $mikrotikIds = [];

            foreach ($arpList as $item) {
                $mikrotikIds[] = $item['.id'];

                IpPublic::updateOrCreate(
                    ['id' => $item['.id'] ?? uniqid()],
                    [
                        'address' => $item['address'] ?? null,
                        'interface' => $item['interface'] ?? null,
                        'published' => $item['published'] ?? null,
                        'invalid' => $item['invalid'] ?? null,
                        'dhcp' => $item['dhcp'] ?? null,
                        'dynamic' => $item['dynamic'] ?? null,
                        'complete' => $item['complete'] ?? null,
                        'disabled' => $item['disabled'] ?? null,
                        'comment' => $item['comment'] ?? null,
                        'mac_address' => $item['mac_address'] ?? null,
                        'gateway' => $item['gateway'] ?? null,
                        'subnet' => $item['subnet'] ?? null,
                        'network' => $item['network'] ?? null,
                        'subnet_mask' => $item['subnet_mask'] ?? null,
                    ]
                );

                // send ip arp to table kuma_monitor
                $kuma_monitor = new IpAddressMonitorService();
                $kuma_monitor->sendIpToMonitor($item['.id'], $item['address'], $item['comment'] ?? $item['address']);

            }

            IpPublic::whereNotIn('id', $mikrotikIds)->each(function ($record) {
                $record->delete();
            });

        } catch (Exception $e) {
            // Cek apakah error adalah timeout
            if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'Stream timed out')) {
                Log::warning('[SyncMikrotikBgpJob] Timeout error: retrying job...', [
                    'message' => $e->getMessage()
                ]);

                // Re-dispatch ulang job agar dijalankan ulang
                self::dispatch()->delay(now()->addSeconds(10)); // retry after 10 seconds
                return;
            }

            // Log general exception
            Log::error('[SyncMikrotikBgpJob] Failed to sync due to error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // re-throw jika error selain timeout
        }
    }
}
