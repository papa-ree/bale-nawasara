<?php
namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Paparee\BaleNawasara\App\Services\MikrotikService;

class CacheMikroTikArp extends Command
{
    protected $signature = 'nawasara:cache-arp';
    protected $description = 'Get ARP list from MikroTik and cache it';

    public function handle(MikrotikService $mikroTik)
    {
        try {
            $arpList = $mikroTik->getArpList();

            Cache::put('mikrotik_arp_list', $arpList, now()->addMinutes(config('bale-nawasara.mikrotik.cache_lifetime')));

            $this->info('ARP list cached successfully.');
        } catch (\Throwable $e) {
            $this->error("Failed to fetch ARP: " . $e->getMessage());
        }
    }
}