<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Paparee\BaleNawasara\App\Models\DnsRecord;
use Paparee\BaleNawasara\App\Services\CloudflareService;
use Spatie\UptimeMonitor\Models\Monitor;

class SyncDnsRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nawasara:sync-dns-record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync DNS Record from Cloudflare';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Start Sync...');
        $cf = new CloudflareService;
        $response = $cf->getDnsRecords();

        $records = collect($response ?? []);

        foreach ($records as $record) {
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
                $monitor = Monitor::updateOrCreate(
                    ['dns_record_id' => $record['id']],
                    [
                        'url' => 'https://'.$record['name'],
                        'look_for_string' => '',
                        'uptime_check_method' => 'head',
                        'uptime_check_interval_in_minutes' => config('uptime-monitor.uptime_check.run_interval_in_minutes'),
                    ]);
            }
        }

        $this->info('Sync Successfully');

        cache()->forget('dns_sync_status');
    }
}
