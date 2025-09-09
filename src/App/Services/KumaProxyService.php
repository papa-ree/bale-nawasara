<?php

namespace Paparee\BaleNawasara\App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Paparee\BaleNawasara\App\Jobs\DeleteKumaUptimeJob;
use Paparee\BaleNawasara\App\Models\KumaMonitor;

class KumaProxyService
{
    public function addMonitor(KumaMonitor $monitor): array
    {
        try {

            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->post(config('bale-nawasara.kuma_proxy.url') . '/add-monitor', [
                    'name' => $monitor->name,
                    'url' => $monitor->url,
                    'hostname' => $monitor->hostname,
                    'interval' => $monitor->interval ?? 60,
                    'retryInterval' => $monitor->retry_interval,
                    'resendInterval' => $monitor->resend_interval,
                    'maxretries' => $monitor->max_retries,
                    'timeout' => $monitor->timeout,
                    'type' => $monitor->type,
                    'tags' => $monitor->tags,
                    'notificationIDList' => $monitor->notification_id_list,
                    'expiryNotification' => $monitor->expiry_notification,
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'msg' => 'Failed to add monitor',
                    'status' => $response->status(),
                    'error' => $response->body(),
                ];
            }

            $json = $response->json();

            // info($response->json());

            // Update model jika sukses & ada monitorID
            if (isset($json['result']['monitorID'])) {
                $monitor->update([
                    'kuma_id' => $json['result']['monitorID'],
                    'kuma_synced' => true,
                    'uptime_status' => true,
                ]);
            }

            return $json ?? [
                'ok' => false,
                'msg' => 'Invalid JSON response from Kuma proxy',
            ];
        } catch (\Throwable $th) {
            return [
                'ok' => false,
                'msg' => 'Exception occurred',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function updateMonitor(KumaMonitor $monitor, $new_tags = null): array
    {
        try {

            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->post(config('bale-nawasara.kuma_proxy.url') . '/update-monitor', [
                    'id_' => $monitor->kuma_id,
                    'name' => $monitor->name,
                    'url' => $monitor->url,
                    'hostname' => $monitor->hostname,
                    'interval' => $monitor->interval ?? 60,
                    'retryInterval' => $monitor->retry_interval,
                    'resendInterval' => $monitor->resend_interval,
                    'maxretries' => $monitor->max_retries,
                    'timeout' => $monitor->timeout,
                    'type' => $monitor->type,
                    'oldTags' => $monitor->tags,
                    'newTags' => $new_tags ?? $monitor->tags,
                    'notificationIDList' => $monitor->notification_id_list,
                    'expiryNotification' => $monitor->expiry_notification,
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'msg' => 'Failed to add monitor',
                    'status' => $response->status(),
                    'error' => $response->body(),
                ];
            }

            $json = $response->json();

            // Update model jika sukses & ada monitorID
            if (isset($json['result']['monitorID'])) {
                $monitor->update([
                    'kuma_id' => $json['result']['monitorID'],
                    'kuma_synced' => true,
                ]);
            }

            return $json ?? [
                'ok' => false,
                'msg' => 'Invalid JSON response from Kuma proxy',
            ];
        } catch (\Throwable $th) {
            return [
                'ok' => false,
                'msg' => 'Exception occurred',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function updateMonitorName(KumaMonitor $monitor): array
    {
        try {

            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->post(config('bale-nawasara.kuma_proxy.url') . '/update-monitor-name', [
                    'id_' => $monitor->kuma_id,
                    'name' => $monitor->name,
                    'kuma_synced' => true,
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'msg' => 'Failed to update monitor name',
                    'status' => $response->status(),
                    'error' => $response->body(),
                ];
            }

            $json = $response->json();

            if (isset($json['status'])) {
                $monitor->update([
                    'kuma_synced' => true,
                ]);
            }

            return $json ?? [
                'ok' => false,
                'msg' => 'Invalid JSON response from Kuma proxy',
            ];
        } catch (\Throwable $th) {
            return [
                'ok' => false,
                'msg' => 'Exception occurred',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function getTags(): array
    {
        try {
            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->get(config('bale-nawasara.kuma_proxy.url') . '/get-tags');

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'msg' => 'Failed to fetch tags',
                    'status' => $response->status(),
                    'error' => $response->body(),
                ];
            }

            $tags = $response->json();

            // Simpan ke cache
            Cache::put('kuma_tags', $tags);

            return [
                'ok' => true,
                'msg' => 'Tags fetched and cached successfully',
                'data' => $tags,
            ];
        } catch (\Throwable $th) {
            return [
                'ok' => false,
                'msg' => 'Exception occurred while fetching tags',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function getIpMonitors(): array
    {
        try {
            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->get(config('bale-nawasara.kuma_proxy.url') . '/get-monitors');

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'msg' => 'Failed to fetch monitors',
                    'status' => $response->status(),
                    'error' => $response->body(),
                ];
            }

            $monitors = collect($response->json('result'));

            // Group berdasarkan hostname
            $grouped = $monitors->groupBy('hostname');

            foreach ($grouped as $hostname => $items) {
                if (blank($hostname)) {
                    continue; // skip jika hostname kosong
                }

                // Ambil id terkecil
                $primary = $items->sortBy('id')->first();

                // Update kuma_monitors pakai id terkecil
                KumaMonitor::whereHostname($hostname)->update([
                    'kuma_id' => $primary['id'],
                    'kuma_synced' => true,
                ]);

                // Ambil id duplikat selain id terkecil
                $duplicates = $items->where('id', '!=', $primary['id']);

                // Jalankan penghapusan per chunk 5
                $duplicates->chunk(5)->each(function ($chunk) {
                    foreach ($chunk as $dup) {
                        // Bisa dispatch sync atau async sesuai kebutuhan
                        DeleteKumaUptimeJob::dispatch($dup['id']);
                    }

                    // beri jeda sedikit supaya tidak spike (opsional)
                    usleep(200000); // 0.2 detik
                });
            }

            return [
                'ok' => true,
                'msg' => 'Monitors synced and duplicates cleaned up',
                'data' => $monitors,
            ];
        } catch (\Throwable $th) {
            return [
                'ok' => false,
                'msg' => 'Exception occurred while syncing monitors',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function deleteMonitor(KumaMonitor $monitor): bool
    {
        try {
            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->post(config('bale-nawasara.kuma_proxy.url') . "/delete-monitor/{$monitor->kuma_id}");

            if ($response->successful()) {
                Log::info("[KumaProxyService] Monitor {$monitor->kuma_id} deleted from Kuma Proxy");
                return true;
            }

            Log::warning("[KumaProxyService] Failed to delete monitor {$monitor->kuma_id}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error("[KumaProxyService] Exception deleting monitor {$monitor->kuma_id}", [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function deleteMonitorById($id): bool
    {
        try {
            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->post(config('bale-nawasara.kuma_proxy.url') . "/delete-monitor/{$id}");

            if ($response->successful()) {
                Log::info("[KumaProxyService] Monitor {$id} deleted from Kuma Proxy");
                return true;
            }

            Log::warning("[KumaProxyService] Failed to delete monitor {$id}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error("[KumaProxyService] Exception deleting monitor {$id}", [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
