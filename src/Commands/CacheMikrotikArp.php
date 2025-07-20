<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Paparee\BaleNawasara\App\Jobs\SyncMikrotikBgpJob;

class CacheMikroTikArp extends Command
{
    protected $signature = 'nawasara:cache-arp';

    protected $description = 'Get ARP list from MikroTik and cache it';

    public function handle()
    {
        try {
            SyncMikrotikBgpJob::dispatch();
            $this->info('ARP list cached successfully.');
        } catch (\Throwable $e) {
            $this->error('Failed to fetch ARP: '.$e->getMessage());
        }
    }
}
