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

    // get ip address mikrotik
    public function getIpAddresses(): array
    {
        try {
            $query = new Query('/ip/address/print');

            return $this->client->query($query)->read();

            // return ['ok' => true, 'count' => count($response), "data" => $response];

        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getArpLists(): array
    {
        $query = new Query('/ip/arp/print');
        $arpList = $this->client->query($query)->read();

        // Rename MAC address key
        $arpList = array_map(function ($item) {
            $item['mac_address'] = $item['mac-address'] ?? null;
            unset($item['mac-address']);

            return $item;
        }, $arpList);

        // Sort by IP address
        usort($arpList, function ($a, $b) {
            return ip2long($a['address']) <=> ip2long($b['address']);
        });

        // Get cached address list
        $addressList = collect(cache()->get('mikrotik_address_list', []));

        // Map with CIDR, subnet mask, gateway
        $arpList = array_map(function ($item) use ($addressList) {
            $ipLong = ip2long($item['address']);

            foreach ($addressList as $address) {
                if (! isset($address['address'])) {
                    continue;
                }

                // Parse CIDR
                [$gatewayIp, $cidr] = explode('/', $address['address']);
                $subnet = (int) $cidr;

                // Get subnet mask in decimal format
                $mask = -1 << (32 - $subnet);
                $networkLong = ip2long($gatewayIp) & $mask;

                // Check if ARP IP is in this subnet
                if (($ipLong & $mask) === $networkLong) {
                    $item['gateway'] = $gatewayIp;
                    $item['subnet'] = $subnet;
                    $item['network'] = long2ip($networkLong);
                    $item['subnet_mask'] = long2ip($mask);
                    break;
                }
            }

            return $item;
        }, $arpList);

        return $arpList;
    }

    /**
     * Update comment pada ARP entry
     *
     * @param  string  $arpId  ID dari ARP (bisa didapat dari getArpList)
     * @param  string  $comment  Komentar baru
     */
    public function updateArpComment(string $arpId, $comment): bool
    {
        try {
            $query = (new Query('/ip/arp/set'))
                ->equal('.id', $arpId)
                ->equal('comment', $comment);

            $this->client->query($query)->read();

            return true;
        } catch (\Throwable $e) {
            info($e->getMessage());

            return false;
        }
    }

    /**
     * Jadikan IP address static pada ARP entry
     *
     * @param  string  $arpId  ID dari ARP
     */
    public function makeStaticArp(string $arpId, ?string $comment = null): bool
    {
        try {
            // 1. Ambil data ARP berdasarkan ID
            $queryGet = (new Query('/ip/arp/print'))
                ->where('.id', $arpId);
            $arpEntry = $this->client->query($queryGet)->read();

            if (empty($arpEntry)) {
                return false; // Tidak ditemukan
            }

            $entry = $arpEntry[0];

            $isDynamic = isset($entry['dynamic']) && $entry['dynamic'] === 'true';
            $existingComment = $entry['comment'] ?? '';

            // 2. Jika sudah static dan comment berbeda → update comment saja
            if (! $isDynamic) {
                if (! empty($comment) && $comment !== $existingComment) {
                    $querySet = (new Query('/ip/arp/set'))
                        ->equal('.id', $arpId)
                        ->equal('comment', $comment);
                    $this->client->query($querySet)->read();
                }

                return true;
            }

            // 3. Jika dynamic → hapus
            $queryRemove = (new Query('/ip/arp/remove'))
                ->equal('.id', $arpId);
            $this->client->query($queryRemove)->read();

            // 4. Tambahkan sebagai static
            $queryAdd = (new Query('/ip/arp/add'))
                ->equal('address', $entry['address'])
                ->equal('interface', $entry['interface']);

            // Gunakan MAC address palsu jika tidak ada comment
            $mac = (! empty($comment)) ? $entry['mac-address'] : '00:00:00:00:00:00';
            $queryAdd->equal('mac-address', $mac);

            // Tambahkan comment jika ada
            if (! empty($comment)) {
                $queryAdd->equal('comment', $comment);
            }

            $this->client->query($queryAdd)->read();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function removeArpEntry(string $arpId): bool
    {
        try {
            // Pastikan ID ada di ARP list
            $queryCheck = (new Query('/ip/arp/print'))->where('.id', $arpId);
            $result = $this->client->query($queryCheck)->read();

            if (empty($result)) {
                return false; // Tidak ditemukan
            }

            // Hapus berdasarkan ID
            $queryRemove = (new Query('/ip/arp/remove'))
                ->equal('.id', $arpId);

            $this->client->query($queryRemove)->read();

            return true;
        } catch (\Exception $e) {
            // Log error jika perlu
            info('Mikrotik ARP error: '.$e->getMessage());

            return false;
        }
    }

    // public function getIpAddressList(): array
    // {
    //     $query = new Query('/ip/address/print');
    //     $ipList = $this->client->query($query)->read();

    //     // Ambil data ARP dari cache
    //     $arpList = collect(cache()->get('mikrotik_arp_list', []));
    //     // $arpList = collect(Cache::get('mikrotik_arp_list', []));

    //     $ipList = array_map(function ($item) use ($arpList) {
    //         [$ip, $subnet] = explode('/', $item['address']);
    //         $subnet = (int) $subnet;

    //         $ipLong = ip2long($ip);
    //         $mask = -1 << (32 - $subnet);
    //         $networkLong = $ipLong & $mask;
    //         $broadcastLong = $networkLong | ~$mask;

    //         $network = long2ip($networkLong);
    //         $broadcast = long2ip($broadcastLong);

    //         $firstHost = $networkLong + 1;
    //         $lastHost = $broadcastLong - 1;

    //         $range = long2ip($firstHost) . ' - ' . long2ip($lastHost);
    //         $maxHost = max(0, $lastHost - $firstHost + 1);

    //         // Hitung used host dari data ARP
    //         $usedHost = $arpList->filter(function ($arp) use ($networkLong, $broadcastLong) {
    //             if (!isset($arp['address']))
    //                 return false;
    //             $ipLong = ip2long($arp['address']);
    //             return $ipLong >= $networkLong && $ipLong <= $broadcastLong;
    //         })->count();

    //         $freeHost = max(0, $maxHost - $usedHost);

    //         return [
    //             ...$item,
    //             'broadcast' => $broadcast,
    //             'range' => $range,
    //             'max_host' => $maxHost,
    //             'used_host' => $usedHost,
    //             'free_host' => $freeHost,
    //         ];
    //     }, $ipList);

    //     // Urutkan berdasarkan IP
    //     usort($ipList, function ($a, $b) {
    //         $ipA = explode('/', $a['address'])[0];
    //         $ipB = explode('/', $b['address'])[0];
    //         return ip2long($ipA) <=> ip2long($ipB);
    //     });

    //     return $ipList;

    //     // [
    //     //     ".id" => "*D11FF",
    //     //     "address" => "103.109.207.222",
    //     //     "interface" => "bridge1-public",
    //     //     "published" => "false",
    //     //     "invalid" => "false",
    //     //     "DHCP" => "false",
    //     //     "dynamic" => "true",
    //     //     "complete" => "false",
    //     //     "disabled" => "false",
    //     //     "mac_address" => null,
    //     //     "gateway" => "103.109.207.193",
    //     //     "subnet" => 27,
    //     //     "network" => "103.109.207.192/27",
    //     // ],
    // }

    // public function generateIpAddressGroups(): void
    // {
    //     $arpList = collect(cache()->get('mikrotik_arp_list', []));
    //     $addressList = collect(cache()->get('mikrotik_address_list', []));

    //     $groups = [];

    //     foreach ($addressList as $address) {
    //         if (!isset($address['address']))
    //             continue;

    //         [$gatewayIp, $cidr] = explode('/', $address['address']);
    //         $subnet = (int) $cidr;

    //         $networkLong = ip2long($gatewayIp) & (-1 << (32 - $subnet));
    //         $networkIp = long2ip($networkLong);
    //         $rangeKey = "{$networkIp}/{$subnet}";

    //         // Dapatkan semua IP dari ARP yang berada dalam subnet ini
    //         $groupIps = $arpList->filter(function ($arp) use ($networkLong, $subnet) {
    //             if (!isset($arp['address']))
    //                 return false;

    //             $ipLong = ip2long($arp['address']);
    //             $mask = -1 << (32 - $subnet);
    //             return ($ipLong & $mask) === $networkLong;
    //         })->pluck('address')->values()->all();

    //         $groups[] = [
    //             'gateway' => $gatewayIp,
    //             'subnet' => '/' . $subnet,
    //             'network' => $rangeKey,
    //             'ip_list' => $groupIps,
    //         ];
    //     }

    //     cache()->put('mikrotik_address_group_list', $groups);
    // }

    public function calculateIpHostRange(): array
    {
        $addressList = cache()->get('mikrotik_address_list', []);
        $arpList = cache()->get('mikrotik_arp_list', []);

        $result = [];

        foreach ($addressList as $entry) {
            if (! isset($entry['address'])) {
                continue;
            }

            [$ip, $cidr] = explode('/', $entry['address']);
            $cidr = (int) $cidr;

            // Skip invalid CIDR
            if ($cidr < 0 || $cidr > 32) {
                continue;
            }

            $ipLong = ip2long($ip);
            $netmask = -1 << (32 - $cidr);
            $network = $ipLong & $netmask;
            $broadcast = $network | ~$netmask;

            // Hitung host usable (exclude network & broadcast jika subnet > 30)
            $maxHost = ($cidr < 31) ? ($broadcast - $network - 1) : ($broadcast - $network + 1);

            // Hitung IP yang digunakan (arpList yang masuk dalam range ini)
            $usedHost = collect($arpList)
                ->pluck('address')
                ->filter(function ($hostIp) use ($network, $broadcast) {
                    $long = ip2long($hostIp);

                    return $long >= ($network + 1) && $long <= ($broadcast - 1);
                })->count();

            $freeHost = max(0, $maxHost - $usedHost);

            $result[] = [
                'address' => $entry['address'],
                'network' => long2ip($network),
                'broadcast' => long2ip($broadcast),
                'range' => long2ip($network + 1).' - '.long2ip($broadcast - 1),
                'max_host' => $maxHost,
                'used_host' => $usedHost,
                'free_host' => $freeHost,
            ];
        }

        cache()->put('mikrotik_ip_address_host', $result);

        return $result;

        // [
        // "address" => "172.17.1.75/32",
        // "network" => "172.17.1.75",
        // "broadcast" => "172.17.1.75",
        // "range" => "172.17.1.76 - 172.17.1.74",
        // "max_host" => 1,
        // "used_host" => 0,
        // "free_host" => 1,
        // ],
    }
}
