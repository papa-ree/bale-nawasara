<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GetInstansiLocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::get(config('bale-nawasara.instansi_location.api'))->json();

        $data = $response['data'] ?? [];

        $instansi = collect($data)->map(function ($item) {
            return [
                'id' => $item['id'] ?? '',
                'name' => $item['name'] ?? '',
                'description' => $item['description'] ?? '',
                'alamat' => $item['alamat'] ?? '',
                'map' => $item['map'] ?? '',
            ];
        });

        Cache::put('nawasara_instansi', $instansi);
    }
}
