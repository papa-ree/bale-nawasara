<?php

namespace Paparee\BaleNawasara\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Paparee\BaleNawasara\App\Models\HelpdeskForm;
use Paparee\BaleNawasara\App\Services\WagoService;

class WagoWebhookController extends Controller
{
    // public $aduanGroupId = "120363422711216219";
    public $aduanGroupId = '120363403973800965';

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
        $allowedGroupId = $this->aduanGroupId;

        // Cek apakah From mengandung group id yang diizinkan
        if ($chatId == $allowedGroupId) {
            // $messageData = json_decode($message, true);

            $unassign_ticket = HelpdeskForm::wherePic(null)->count();

            $item = HelpdeskForm::whereTicketNumber($ticketNumber)->first();

            if ($item->pic) {

                $msg = " ℹ️ *Aduan sedang ditangani* 
*Petugas:* {$item->pic}  
*Waktu:* {$item->updated_at}  

Aduan yang belum terkonfirmasi:  {$unassign_ticket}  
";

            } else {
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
            }

            (new WagoService)->sendMessageGroup('120363403973800965', $msg);

            // logger()->info('Payload disimpan ke HelpdeskForm', [
            //     'ticket_number' => $ticketNumber,
            //     'replied_id' => $replied_id,
            //     'pushname' => $pushname,
            // ]);
            // } else {
            //     logger()->info('Payload diabaikan karena bukan grup terdaftar', [
            //         'from' => $from,
            //     ]);
        }

        return response()->json(['status' => 'ok']);
    }

    // public function handle(Request $request)
    // {
    //     $secret = config('bale-nawasara.whatsapp.secret');

    //     // Ambil raw payload (string JSON)
    //     $payload = $request->getContent();

    //     // Ambil header signature dari request
    //     $signatureHeader = $request->header('X-Hub-Signature-256');

    //     // Validasi keberadaan signature
    //     if (!$signatureHeader) {
    //         return response()->json(['error' => 'Missing signature'], 400);
    //     }

    //     // Generate signature dengan HMAC SHA256
    //     $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

    //     // Validasi signature
    //     if (!hash_equals($expectedSignature, $signatureHeader)) {
    //         logger()->warning('Webhook signature mismatch', [
    //             'expected' => $expectedSignature,
    //             'got' => $signatureHeader,
    //             'payload' => $payload,
    //         ]);

    //         return response()->json(['error' => 'Invalid signature'], 403);
    //     }

    //     // logger()->info('Webhook verified:', json_decode($payload, true));

    //     // Decode payload menjadi array
    //     $data = json_decode($payload, true);

    //     // Pastikan payload valid
    //     if (!$data) {
    //         return response()->json(['error' => 'Invalid JSON'], 400);
    //     }

    //     // Ambil nilai penting dari payload
    //     $chatId = $data['Chat_id'] ?? null;
    //     $from = $data['From'] ?? null; // berisi "nomor@s.whatsapp.net in groupid@g.us"
    //     $pushname = $data['Pushname'] ?? null;
    //     $message = $data['Message'] ?? null; // JSON string

    //     // Extract group_id dari field "From"
    //     $groupId = null;
    //     if ($from && str_contains($from, ' in ')) {
    //         [$sender, $group] = explode(' in ', $from);
    //         $groupId = str_replace('@g.us', '', $group); // ambil hanya angka ID grup
    //     }

    //     info('group id dari from', [$from]);

    //     // Hanya simpan jika group_id sesuai dengan whitelist
    //     $allowedGroupId = '120363403973800965';
    //     if ($groupId === $allowedGroupId) {
    //         // Decode message JSON agar lebih mudah disimpan
    //         $messageData = json_decode($message, true);

    //         // Simpan ke model HelpdeskForm
    //         // HelpdeskForm::create([
    //         //     'chat_id' => $chatId,
    //         //     'group_id' => $groupId,
    //         //     'message' => $messageData, // bisa disimpan sebagai JSON
    //         //     'pushname' => $pushname,
    //         // ]);

    //         logger()->info('Payload disimpan ke HelpdeskForm', [
    //             'chat_id' => $chatId,
    //             'group_id' => $groupId,
    //             'pushname' => $pushname,
    //         ]);
    //     } else {
    //         logger()->info('Payload diabaikan karena bukan grup terdaftar', [
    //             'group_id' => $groupId,
    //         ]);
    //     }

    //     return response()->json(['status' => 'ok']);
    // }
}
