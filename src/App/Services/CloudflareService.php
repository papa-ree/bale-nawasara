<?php 
namespace Paparee\BaleNawasara\App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CloudflareService
{
    protected string $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function getDnsRecords()
    {
        // $response = Http::withToken(config('bale-nawasara.cloudflare.api_token'))
        //     ->get("{$this->baseUrl}/zones/" . config('bale-nawasara.cloudflare.zone_id') . "/dns_records");

        // if ($response->successful()) {
        //     $data = $response->json();
        // }
        // return $data;

        return Cache::remember('cloudflare_dns_records', now()->addMinutes(5), function () {
            $allRecords = [];
            $page = 1;
            $perPage = 100;
    
            do {
                $response = Http::withToken(config('bale-nawasara.cloudflare.api_token'))
                    ->get("{$this->baseUrl}/zones/" . config('bale-nawasara.cloudflare.zone_id') . "/dns_records", [
                        'page' => $page,
                        'per_page' => $perPage,
                    ]);
    
                if (!$response->successful()) {
                    // Log error response if needed
                    logger()->error('Cloudflare API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return []; // fail gracefully
                }
    
                // dd($response->json());
                $json = $response->json();
                
                // Cek apakah key 'result' dan 'result_info' tersedia
                $records = $json['result'] ?? [];
                $resultInfo = $json['result_info'] ?? ['total_pages' => 1];
    
                $allRecords = array_merge($allRecords, $records);
                $page++;
    
            } while ($page <= ($resultInfo['total_pages'] ?? 1));
    
            return $allRecords;
        });
    }
}
