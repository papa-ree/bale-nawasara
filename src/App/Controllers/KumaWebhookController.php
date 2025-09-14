<?php

namespace Paparee\BaleNawasara\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Paparee\BaleNawasara\App\Models\KumaMonitor;
use Paparee\BaleNawasara\App\Services\WagoService;

class KumaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        $heartbeat = $data['heartbeat'] ?? [];
        $monitor = $data['monitor'] ?? [];
        $message = $data['msg'] ?? null;

        // Jika webhook berupa expiry certificate
        if ($message && str_contains($message, 'server certificate')) {
            // Ambil [name] dan [url] dari msg
            preg_match('/\[(.*?)\]\[(.*?)\]/', $message, $matches);
            $name = $matches[1] ?? null;
            $url = $matches[2] ?? null;

            // Ambil jumlah hari dari pesan
            preg_match('/(\d+)\s+days/', $message, $dayMatch);
            $days = $dayMatch[1] ?? null;

            if ($name && $url && $days) {
                $kuma = KumaMonitor::where('name', $name)
                    ->where('url', $url)
                    ->first();

                if ($kuma) {
                    $kuma->update([
                        'certificate_expiration_date' => now()->addDays((int) $days),
                    ]);
                }

                // Kirim notifikasi via WhatsApp
                (new WagoService())->sendMessageGroup("120363402020043689", message:
                    "🔒 *SSL WARNING*  
                        *Monitor:* {$kuma->name}  
                        *Domain:* {$kuma->url}
                        _Status:_ ⚠️ SSL akan kedaluwarsa  
                        _Sisa:_ {$kuma->certificate_expiration_date} hari
                    ");
            }

            // info("Certificate expiry handled", $data);
            return response()->json(['status' => 'ok']);
        }

        // --- Normal heartbeat monitor handler ---
        $kuma_id = $heartbeat['monitorID'] ?? null;
        $uptime_status = $heartbeat['status'] ?? null;

        $uptime_check_failure_reason = null;
        $uptime_check_failure_duration = null;

        if ($uptime_status === 0) {
            $uptime_check_failure_reason = $heartbeat['msg'] ?? null;
            $uptime_check_failure_duration = $heartbeat['duration'] ?? null;
        }

        $kuma = KumaMonitor::whereKumaId($kuma_id)->first();

        if ($kuma) {
            $kuma->update([
                'uptime_status' => $uptime_status,
                'uptime_check_failure_reason' => $uptime_check_failure_reason,
                'uptime_check_failure_duration' => $uptime_check_failure_duration,
            ]);

            if ($uptime_status === 1) {
                // UP
                $msg = "✅ *UPTIME ALERT*  
*Monitor:* {$kuma->name}  
*Jenis:* {$kuma->type}  
*Target:* {$kuma->url}  "
                ;
            } elseif ($uptime_status === 0) {
                // DOWN
                $msg = "❌ *DOWNTIME ALERT*  
*Monitor:* {$kuma->name}  
*Jenis:* {$kuma->type}  
*Target:* {$kuma->url}  
_Error:_ {$uptime_check_failure_reason}"
                ;
            } else {
                $msg = "✅ *UPTIME ALERT*  
*Monitor:* Trial Monitor  
*Jenis:* HTTP/PING  
*Target:* url/hostname  
_Status:_ ✅ *UP*"
                ;
            }

            if (isset($msg)) {
                (new WagoService())->sendMessageGroup("120363402020043689", $msg);
            }

            // if (!$this->isQuietHours()) {

            // }
        }

        return response()->json(['status' => 'ok']);
    }

    protected function isQuietHours(): bool
    {
        $start = config('bale-nawasara.notification.quiet_hours.start');
        $end = config('bale-nawasara.notification.quiet_hours.end');

        $now = now()->format('H:i');

        if ($start < $end) {
            // Kasus normal: contoh 21:00 - 23:00
            return $now >= $start && $now < $end;
        } else {
            // Kasus melewati tengah malam: contoh 21:00 - 07:00
            return $now >= $start || $now < $end;
        }
    }

    // array (
    //  'heartbeat' =>
    //  array (
    //    'monitorID' => 2,
    //    'status' => 0,
    //    'time' => '2025-08-11 01:12:02.114',
    //    'msg' => 'getaddrinfo EAI_AGAIN ponorogo.go.id',
    //    'important' => true,
    //    'duration' => 67,
    //    'timezone' => 'Asia/Jakarta',
    //    'timezoneOffset' => '+07:00',
    //    'localDateTime' => '2025-08-11 08:12:02',
    //  ),
    //  'monitor' =>
    //  array (
    //    'id' => 2,
    //    'name' => 'Ponorogo',
    //    'description' => NULL,
    //    'pathName' => 'Ponorogo',
    //    'parent' => NULL,
    //    'childrenIDs' =>
    //    array (
    //    ),
    //    'url' => 'https://ponorogo.go.id',
    //    'method' => 'GET',
    //    'hostname' => NULL,
    //    'port' => NULL,
    //    'maxretries' => 0,
    //    'weight' => 2000,
    //    'active' => true,
    //    'forceInactive' => false,
    //    'type' => 'http',
    //    'timeout' => 48,
    //    'interval' => 60,
    //    'retryInterval' => 60,
    //    'resendInterval' => 0,
    //    'keyword' => NULL,
    //    'invertKeyword' => false,
    //    'expiryNotification' => true,
    //    'ignoreTls' => false,
    //    'upsideDown' => false,
    //    'packetSize' => 56,
    //    'maxredirects' => 10,
    //    'accepted_statuscodes' =>
    //    array (
    //      0 => '200-299',
    //    ),
    //    'dns_resolve_type' => 'A',
    //    'dns_resolve_server' => '1.1.1.1',
    //    'dns_last_result' => NULL,
    //    'docker_container' => NULL,
    //    'docker_host' => NULL,
    //    'proxyId' => NULL,
    //    'notificationIDList' =>
    //    array (
    //      1 => true,
    //      2 => true,
    //    ),
    //    'tags' =>
    //    array (
    //    ),
    //    'maintenance' => false,
    //    'mqttTopic' => NULL,
    //    'mqttSuccessMessage' => NULL,
    //    'databaseQuery' => NULL,
    //    'authMethod' => NULL,
    //    'grpcUrl' => NULL,
    //    'grpcProtobuf' => NULL,
    //    'grpcMethod' => NULL,
    //    'grpcServiceName' => NULL,
    //    'grpcEnableTls' => false,
    //    'radiusCalledStationId' => NULL,
    //    'radiusCallingStationId' => NULL,
    //    'game' => NULL,
    //    'gamedigGivenPortOnly' => true,
    //    'httpBodyEncoding' => 'json',
    //    'jsonPath' => NULL,
    //    'expectedValue' => NULL,
    //    'kafkaProducerTopic' => NULL,
    //    'kafkaProducerBrokers' =>
    //    array (
    //    ),
    //    'kafkaProducerSsl' => false,
    //    'kafkaProducerAllowAutoTopicCreation' => false,
    //    'kafkaProducerMessage' => NULL,
    //    'screenshot' => NULL,
    //    'includeSensitiveData' => false,
    //  ),
    //  'msg' => '[Ponorogo] [:red_circle: Down] getaddrinfo EAI_AGAIN ponorogo.go.id',
    // )
}
