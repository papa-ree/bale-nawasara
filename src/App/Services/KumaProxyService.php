<?php

namespace Paparee\BaleNawasara\App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Paparee\BaleNawasara\App\Models\KumaMonitor;

class KumaProxyService
{
    public function addPingMonitor(KumaMonitor $monitor): array
    {
        try {

            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->post(config('bale-nawasara.kuma_proxy.url').'/add-monitor', [
                    'name' => $monitor->name,
                    'url' => $monitor->url,
                    'hostname' => $monitor->hostname,
                    'interval' => $monitor->interval ?? 60,
                    'type' => $monitor->type,
                    'tags' => $monitor->tags,
                    'notificationIDList' => $monitor->notification_id_list,
                    'expiryNotification' => $monitor->expiry_notification,
                ]);

            if (! $response->successful()) {
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

    public function getTags(): array
    {
        try {
            $response = Http::withToken(config('bale-nawasara.kuma_proxy.token'))
                ->get(config('bale-nawasara.kuma_proxy.url').'/get-tags');

            if (! $response->successful()) {
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
}
