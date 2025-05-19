<?php

namespace Paparee\BaleNawasara\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;

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
            $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post(env('WHATSAPP_GO_URL') .'/send/message', [
                    'phone' => $request->phone,
                    'message' => $request->message,
                    'reply_message_id' => $request->reply_message_id,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'code' => 200,
                    'message' => 'Success',
                    'results' => [
                        'message_id' => $response['message_id'] ?? '',
                        'status' => $response['status'] ?? '<feature> success ....',
                    ]
                ]);
            }

            return response()->json([
                'code' => $response->status(),
                'message' => $response->json('message') ?? $response->status() == 401 ? 'Unauthorized, Please contact Administrator' : 'Unknown error',
                'results' => (object) [],
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Please Contact Administrator',
                'results' => (object) [],
            ], 500);
        }
    }
}
