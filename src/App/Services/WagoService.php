<?php

namespace Paparee\BaleNawasara\App\Services;

use Illuminate\Support\Facades\Http;

class WagoService
{
    public function sendMessage($phone, $message, $replyMessageId = null)
    {
        $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(env('WHATSAPP_GO_URL').'/send/message', [
                'phone' => "{$phone}@s.whatsapp.net",
                'message' => $message,
            ]);

        return $response;
    }

    public function sendMessageGroup($groupId, $message)
    {
        $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(env('WHATSAPP_GO_URL').'/send/message', [
                'phone' => "{$groupId}@g.us",
                'message' => $message,
            ]);

        return $response;
    }

    public function userCheck($phone)
    {
        $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->get(env('WHATSAPP_GO_URL').'/user/check', [
                'phone' => "{$phone}@s.whatsapp.net",
                // 'phone' => "6285239146416777@s.whatsapp.net",
            ]);

        return $response;
    }
}
