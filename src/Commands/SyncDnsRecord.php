<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Paparee\BaleNawasara\App\Jobs\SyncDnsRecordsJob;

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

        SyncDnsRecordsJob::dispatch();

        $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(env('WHATSAPP_GO_URL') . '/send/message', [
                'phone' => '6285239146416@s.whatsapp.net',
                'message' => 'DNS Record Sync Successfully',
            ]);

        $this->info('Sync Successfully');

        cache()->forget('dns_sync_status');
    }
}
