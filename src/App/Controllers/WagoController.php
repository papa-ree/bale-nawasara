<?php

namespace Paparee\BaleNawasara\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Paparee\BaleNawasara\App\Jobs\SendWagoMessageJob;
use Paparee\BaleNawasara\App\Services\WagoService;

class WagoController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string',
            'reply_message_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => 'field cannot be blank',
                'results' => (object) [],
            ], 400);
        }

        try {
            $key = 'wago-send-message:' . $request->ip();
            $limit = 10;

            // cek rate limiter
            if (RateLimiter::tooManyAttempts($key, $limit)) {
                // masuk ke job antrian
                SendWagoMessageJob::dispatch(
                    $request->phone,
                    $request->message,
                    $request->reply_message_id
                );

                return response()->json([
                    'code' => 202,
                    'message' => 'Queued. Too many requests, message will be sent via background job.',
                    'results' => (object) [],
                ], 202);
            }

            RateLimiter::hit($key, 60); // reset setiap 60 detik

            // langsung kirim
            $response = (new WagoService)->sendMessage($request->phone, $request->message, $request->reply_message_id);

            if ($response->successful()) {
                return response()->json([
                    'code' => 200,
                    'message' => 'Success',
                    'results' => [
                        'message_id' => $response['message_id'] ?? '',
                        'status' => $response['status'] ?? 'Message Delivered',
                    ],
                ]);
            }

            return response()->json([
                'code' => $response->status(),
                'message' => $response->json('message') ?? ($response->status() == 401 ? 'Unauthorized, Please contact Administrator' : 'Unknown error'),
                'results' => (object) [],
            ], $response->status());

        } catch (\Exception $e) {
            info('Wago Error message: ' . $e->getMessage());

            return response()->json([
                'code' => 500,
                'message' => 'Please Contact Administrator',
                'results' => (object) [],
            ], 500);
        }
    }

    public function userCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => 'Validation fails',
                'results' => (object) [],
            ], 400);
        }

        $response = (new WagoService)->userCheck($request->phone);

        if ($response->successful()) {
            return response()->json([
                'code' => 200,
                'message' => 'Success check user',
                'results' => [
                    'is_on_whatsapp' => $response['results']['is_on_whatsapp'],
                ],
            ]);
        }

        return response()->json([
            'code' => $response->status(),
            'message' => $response->json('message') ?? ($response->status() == 401 ? 'Unauthorized, Please contact Administrator' : 'Unknown error'),
            'results' => (object) [],
        ], $response->status());
    }
}
