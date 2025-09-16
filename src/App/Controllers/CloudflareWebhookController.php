<?php

namespace Paparee\BaleNawasara\App\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Paparee\BaleNawasara\App\Services\WagoService;
use Symfony\Component\HttpFoundation\Response;

class CloudflareWebhookController extends Controller
{
    public string $groupId = '120363402401197971';

    protected function verifyCloudflareWebhook(Request $request)
    {
        $secret = config('services.cloudflare.webhook_secret', env('CLOUDFLARE_WEBHOOK_SECRET'));

        if ($request->header('cf-webhook-auth') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return true;
    }

    public function availableNotice(Request $request)
    {
        // 🔐 Verifikasi otentikasi
        $auth = $this->verifyCloudflareWebhook($request);
        if ($auth !== true) {
            return $auth; // balikan response unauthorized
        }

        $data = $request->all();
        info($data);
    }

    public function ddosHandle(Request $request)
    {
        // 🔐 Verifikasi otentikasi
        $auth = $this->verifyCloudflareWebhook($request);
        if ($auth !== true) {
            return $auth; // balikan response unauthorized
        }

        // Kalau lolos verifikasi → proses payload
        $data = $request->all();

        $zone = $data['data']['target_zone_name'] ?? '-';
        $target = $data['data']['target_hostname'] ?? '-';
        $type = $data['data']['attack_type'] ?? '-';
        $mitigation = $data['data']['mitigation'] ?? '-';
        $rate = $data['data']['requests_per_second'] ?? '-';
        $ruleDesc = $data['data']['rule_description'] ?? '-';
        $ruleId = $data['data']['rule_id'] ?? '-';
        $plan = $data['data']['zone_plan'] ?? '-';
        $dashboard = $data['data']['dashboard_link'] ?? '-';
        $severity = strtoupper($data['data']['severity'] ?? 'INFO');
        $ts = $data['data']['start_time'] ?? now();

        $timeId = Carbon::parse($ts)
            ->timezone('Asia/Jakarta')
            ->format('d-m-Y H:i:s');

        $msg = "🚨 *DDoS ALERT* 🚨
Zone: {$zone}
Target: {$target}
Jenis: HTTP DDoS ({$type})
Severity: {$severity}
Traffic: {$rate} rps
🕒 {$timeId} WIB

📌 Detail:
- Rule: {$ruleDesc} (ID: {$ruleId})
- Mitigasi: {$mitigation}
- Plan: {$plan}
🔗 Dashboard: {$dashboard}
";

        // info($msg);
        (new WagoService)->sendMessageGroup($this->groupId, $msg);

        return response()->json(['status' => 'ok']);
    }

    public function originUptimeHandle(Request $request)
    {
        // 🔐 Verifikasi otentikasi
        $auth = $this->verifyCloudflareWebhook($request);
        if ($auth !== true) {
            return $auth; // balikan response unauthorized
        }

        // Kalau lolos verifikasi → proses payload
        $data = $request->all();

        $severity = strtoupper($data['data']['severity'] ?? 'INFO');
        $policy = $data['policy_name'] ?? '-';
        $account = $data['data']['account_tag'] ?? '-';
        $zones = $data['data']['unreachable_zones'] ?? [];
        $ts = $data['ts'] ?? now();

        $timeId = Carbon::createFromTimestamp($ts)
            ->timezone('Asia/Jakarta')
            ->format('d-m-Y H:i:s');

        // Ambil semua zone yang unreachable
        $zoneList = collect($zones)->pluck('zone_name')->implode(', ');

        $msg = "⚠️ *Origin Monitoring Alert* ⚠️
Zone: {$zoneList}
Severity: {$severity}
Status: ❌ Unreachable (≥ 5 menit)
🕒 {$timeId} WIB

📌 Detail:
- Policy: {$policy}
- Account: {$account}
";

        // info($msg);
        (new WagoService)->sendMessageGroup($this->groupId, $msg);

        return response()->json(['status' => 'ok']);
    }

    public function dexHandle(Request $request)
    {
        // 🔐 Verifikasi otentikasi
        $auth = $this->verifyCloudflareWebhook($request);
        if ($auth !== true) {
            return $auth; // balikan response unauthorized
        }

        $data = $request->all();
        $dex = $data['data']['dex_info'] ?? [];
        $policy = $data['policy_name'] ?? '-';
        $sev = strtoupper($data['data']['severity'] ?? 'INFO');
        $test = $data['data']['synthetic_test']['name'] ?? '-';
        $base = $dex['value_baseline'] ?? null;
        $curr = $dex['value_current'] ?? null;
        $link = $dex['dashboard_link'] ?? '-';
        $event = $dex['event_start_time'] ?? now();
        $account = $dex['account_name'] ?? '-';

        $timeId = Carbon::parse($event)
            ->timezone('Asia/Jakarta')
            ->format('d-m-Y H:i:s');

        $alertType = $data['alert_type'] ?? null;

        if ($alertType === 'synthetic_test_latency_alert') {
            $msg = "⚡ *DEX Latency Alert* ⚡
Test: {$test}
Severity: {$sev}
Latency: {$base}ms ➝ {$curr}ms
🕒 {$timeId} WIB

📌 Detail:
- Policy: {$policy}
- Account: {$account}
🔗 Dashboard: {$link}
";
        } elseif ($alertType === 'synthetic_test_low_availability_alert') {
            $msg = "🚨 *DEX Availability Alert* 🚨
Test: {$test}
Severity: {$sev}
Availability: {$base}% ➝ {$curr}%
🕒 {$timeId} WIB

📌 Detail:
- Policy: {$policy}
- Account: {$account}
🔗 Dashboard: {$link}
";
        } else {
            return response()->json(['status' => 'ignored']);
        }

        (new WagoService)->sendMessageGroup($this->groupId, $msg);

        return response()->json(['status' => 'ok']);
    }

    public function healthCheckHandle(Request $request)
    {
        // 🔒 Otentikasi global (bisa dipakai semua handler)
        $secret = config('services.cloudflare.webhook_secret', env('CLOUDFLARE_WEBHOOK_SECRET'));
        if ($request->header('cf-webhook-auth') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $request->all();
        $data = $payload['data'] ?? [];

        $time = isset($payload['ts'])
            ? Carbon::createFromTimestamp($payload['ts'])->timezone('Asia/Jakarta')->format('d-m-Y H:i:s')
            : '-';

        $severity = strtoupper($data['severity'] ?? 'INFO');
        $status = $data['status'] ?? '-';
        $reason = $data['reason'] ?? '-';
        $event = $data['state_event'] ?? '-';
        $name = $data['name'] ?? '-';
        $policy = $payload['policy_name'] ?? '-';

        // 📌 Format pesan ringkas & detail
        $msg = "🚨 [{$severity}] Health Check Alert

🔹 Name : {$name}
🔹 Status : {$status}
🔹 Reason : {$reason}
🔹 Time : {$time}

Detail:
- Event : {$event}
- Policy : {$policy}";

        (new WagoService)->sendMessageGroup($this->groupId, $msg);

        return response()->json(['status' => 'ok']);
    }


}
