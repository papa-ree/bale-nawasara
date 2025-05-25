<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class HeartBeat implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(env('WHATSAPP_GO_URL').'/send/message', [
                'phone' => '6285239146416@s.whatsapp.net',
                'message' => "i'm Available!",
            ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
