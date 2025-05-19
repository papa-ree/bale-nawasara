<?php

namespace Paparee\BaleNawasara\App\Services;

use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'host' => env('ROUTEROS_HOST'),
            'user' => env('ROUTEROS_USER'),
            'pass' => env('ROUTEROS_PASS'),
            'port' => (int) env('ROUTEROS_PORT', 8728), // default port
            // 'ssl'  => env('MIKROTIK_SSL', false),
            // 'clientOptions' => [
            //     'verify' => false, // karena self-signed
            // ]
        ]);
    }

    public function getArpList(): array
    {
        $query = new Query('/ip/arp/print');

        return $this->client->query($query)->read();
    }
}
