<?php

namespace Paparee\BaleNawasara\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('bale-nawasara.whatsapp.secret');

        $payload = $request->getContent(); // Ambil raw payload
        $signatureHeader = $request->header('X-Hub-Signature-256');

        if (!$signatureHeader) {
            return response()->json(['error' => 'Missing signature'], 400);
        }

        // HMAC SHA256
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, $signatureHeader)) {
            logger()->warning('Webhook signature mismatch', [
                'expected' => $expectedSignature,
                'got' => $signatureHeader,
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Sukses diverifikasi
        logger()->info('Webhook verified:', json_decode($payload, true));

        return response()->json(['status' => 'ok']);

        // WhatsappMessage::create([
        //     'from' => $request->input('from'),
        //     'message' => $request->input('message'),
        //     'timestamp' => now(),
        // ]);

        // return response()->json(['status' => 'ok']);
    }
}
