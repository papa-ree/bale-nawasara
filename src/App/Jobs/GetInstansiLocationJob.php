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

    protected string $url = 'https://sadap.ponorogo.go.id/api/dataPeta';

    protected string $cacheKey = 'bale_inv_maps';

    protected int $cacheMinutes = 1440;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Cache::remember($this->cacheKey, now()->addMinutes($this->cacheMinutes), function () {
            $response = Http::get($this->url)->json();

            $data = $response['data'] ?? [];

            return collect($data)->map(function ($item) {
                return [
                    'id' => $item['id'] ?? '',
                    'name' => $item['name'] ?? '',
                    'description' => $item['description'] ?? '',
                    'alamat' => $item['alamat'] ?? '',
                    'map' => $item['map'] ?? '',
                ];
            });
        });
    }
}
