<?php

namespace Paparee\BaleNawasara\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Paparee\BaleNawasara\App\Models\HelpdeskForm;
use Paparee\BaleNawasara\App\Services\WagoService;

class WagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('bale-nawasara.whatsapp.secret');

        // Ambil raw payload
        $payload = $request->getContent();

        // Ambil header signature
        $signatureHeader = $request->header('X-Hub-Signature-256');

        if (! $signatureHeader) {
            return response()->json(['error' => 'Missing signature'], 400);
        }

        // Generate signature dengan HMAC SHA256
        $expectedSignature = 'sha256='.hash_hmac('sha256', $payload, $secret);

        // Validasi signature
        if (! hash_equals($expectedSignature, $signatureHeader)) {
            logger()->warning('Webhook signature mismatch', [
                'expected' => $expectedSignature,
                'got' => $signatureHeader,
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Decode payload menjadi array
        $data = json_decode($payload, true);

        if (! $data) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        // Ambil data penting
        $chatId = $data['chat_id'] ?? null;
        $from = $data['from'] ?? null;
        $pushname = $data['pushname'] ?? null;
        $replied_id = $data['message']['replied_id'] ?? null;
        $quotedMessage = $data['message']['quoted_message'] ?? null; // JSON string

        if (preg_match('/#([A-Z]+-\d{8}-\d{3})/', $quotedMessage, $matches)) {
            $ticketNumber = '#'.$matches[1];
        }

        // Group yang diizinkan
        $allowedGroupId = env('ADUAN_GROUP_ID');

        // Cek apakah From mengandung group id yang diizinkan
        if ($chatId == $allowedGroupId) {

            $unassign_ticket = HelpdeskForm::wherePic(null)->count();

            $item = HelpdeskForm::whereTicketNumber($ticketNumber)->first();

            // cek apakah sudah memiliki PIC
            if ($item->pic) {

                // pesan jika sudah memiiliki PIC namun
                $msg = " ℹ️ *Aduan sedang ditangani* 
*Petugas:* {$item->pic}  
*Waktu:* {$item->updated_at}  

Aduan yang belum terkonfirmasi:  {$unassign_ticket}  
";

            } else {
                // jika PIC masih kosong
                $item->update([
                    'pic' => $pushname,
                    'message_id' => $replied_id,
                    'status' => 'handled',
                ]);

                $new = $unassign_ticket - 1;

                $msg = "✅ *Aduan dikonfirmasi petugas* 
*Petugas:* {$pushname}  
   
Aduan yang belum terkonfirmasi:  {$new}  
Terima kasih kak   
";

                $client_msg = "*Aduan telah dikonfirmasi petugas*
*No. Tiket* : {$item->ticket_number}";

                //send to client
                (new WagoService)->sendMessage($item->phone, $client_msg);
            }

            (new WagoService)->sendMessageGroup($allowedGroupId, $msg);

            logger()->info('Payload disimpan ke HelpdeskForm', [
                'ticket_number' => $ticketNumber,
                'replied_id' => $replied_id,
                'pushname' => $pushname,
            ]);
            // } else {
            //     logger()->info('Payload diabaikan karena bukan grup terdaftar', [
            //         'from' => $from,
            //     ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
