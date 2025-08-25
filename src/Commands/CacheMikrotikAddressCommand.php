<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Paparee\BaleNawasara\App\Jobs\SyncMikrotikBgpJob;

class CacheMikrotikAddressCommand extends Command
{
    protected $signature = 'nawasara:cache-address';

    protected $description = 'Get Address list from MikroTik and cache it';

    public function handle()
    {
        try {
            SyncMikrotikBgpJob::dispatch();
            $this->info('Address list cached successfully.');
        } catch (\Throwable $e) {
            $this->error('Failed to fetch ARP: ' . $e->getMessage());
        }
    }
}
