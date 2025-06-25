<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Paparee\BaleNawasara\App\Models\DnsRecord;
use Paparee\BaleNawasara\App\Models\NawasaraMonitor;
use Paparee\BaleNawasara\App\Services\CloudflareService;

class SyncDnsRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $cf = new CloudflareService;
        $response = $cf->getDnsRecords();

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
                NawasaraMonitor::updateOrCreate(
                    ['dns_record_id' => $record['id']],
                    [
                        'url' => 'https://'.$record['name'],
                        'look_for_string' => '',
                        'uptime_check_method' => 'get',
                        'certificate_check_enabled' => true,
                        'uptime_check_interval_in_minutes' => config('uptime-monitor.uptime_check.run_interval_in_minutes'),
                    ]
                );
            }
        }

        // Hapus DNS record yang tidak ada di Cloudflare
        DnsRecord::whereNotIn('id', $cloudflareIds)->each(function ($record) {
            // Hapus relasi monitor terlebih dahulu
            $record->monitor()->delete(); // asumsi relasi: $dnsRecord->monitor()
            $record->delete();
        });

        cache()->forget('dns_sync_status');
    }
}
