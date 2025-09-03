<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Paparee\BaleNawasara\App\Models\DnsRecord;
use Paparee\BaleNawasara\App\Services\CloudflareService;
use Paparee\BaleNawasara\App\Services\DnsRecordMonitorService;
use Paparee\BaleNawasara\App\Services\KumaProxyService;

class SyncDnsRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $cf = new CloudflareService;
        $response = $cf->getDnsRecords();

        Cache::put('dns_sync_timestamp', now());

        $records = collect($response ?? []);

        // Kumpulkan semua ID yang masih aktif dari Cloudflare
        $cloudflareIds = [];

        foreach ($records as $record) {
            $cloudflareIds[] = $record['id'];

            DnsRecord::updateOrCreate(
                ['id' => $record['id']],
                [
                    'name' => $record['name'] ?? null,
                    'type' => $record['type'] ?? null,
                    'content' => json_encode($record['content']) ?? null,
                    'proxiable' => $record['proxiable'] ?? null,
                    'proxied' => $record['proxied'] ?? null,
                    'ttl' => $record['ttl'] ?? null,
                    'settings' => isset($record['settings']) ? json_encode($record['settings']) : null,
                    'meta' => isset($record['meta']) ? json_encode($record['meta']) : null,
                    'comment' => $record['comment'] ?? null,
                    'tags' => isset($record['tags']) ? json_encode($record['tags']) : null,
                    'created_on' => $record['created_on'] ?? null,
                    'modified_on' => $record['modified_on'] ?? null,
                    'comment_modified_on' => $record['comment_modified_on'] ?? null,
                    'tags_modified_on' => $record['tags_modified_on'] ?? null,
                ]
            );

            if ($record['type'] === 'A') {
                $kuma_monitor = new DnsRecordMonitorService;
                $kuma_monitor->sendDnsRecordToMonitor($record['id'], $record['name']);
            }
        }

        // Hapus DNS record yang tidak ada di Cloudflare
        DnsRecord::whereNotIn('id', $cloudflareIds)->each(function ($record) {
            if ($record->monitor) {
                // hapus di kuma-proxy
                $kumaProxy = new KumaProxyService;
                $kumaProxy->deleteMonitor($record->monitor);

                // hapus monitor di DB
                $record->monitor->delete();
            }
            $record->delete();
        });

        cache()->forget('dns_sync_status');
    }
}
