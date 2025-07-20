<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Paparee\BaleNawasara\App\Models\NawasaraMonitor;

class SendUptimeCheckFailedToWago extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nawasara:uptime-failed-wago
                            {--phone= : send to specific phone number}
                            {--group= : send to specific group id}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Uptime Check Failed to Wago';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Mengecek monitor yang berstatus DOWN...');

        $downMonitors = NawasaraMonitor::where('uptime_status', 'DOWN')->get();

        if ($downMonitors->isEmpty()) {
            $this->info('✅ Tidak ada subdomain yang down.');

            return Command::SUCCESS;
        }

        $list = $downMonitors->map(function ($monitor, $i) {
            $domain = $monitor->url ?? $monitor->dnsRecord->subdomain ?? 'tidak diketahui';
            $reason = $monitor->uptime_check_failure_reason ?? 'tidak diketahui';

            return ($i + 1) . '. ' . $domain . "\nAlasan: " . $reason;
        })->implode("\n");

        $message = "**🚨 Subdomain DOWN terdeteksi!**\n\n" .
            'Jumlah Subdomain yang down: ' . $downMonitors->count() . "\n\n" .
            "Berikut adalah daftar subdomain yang saat ini tidak dapat diakses:\n\n" .
            $list . "\n\n" .
            "_Mohon segera dicek dan ditindaklanjuti._\n\n*Pesan ini dikirim melalui Nawasara*";

        try {

            if ($phone = $this->option('phone')) {
                $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post(env('WHATSAPP_GO_URL') . '/send/message', [
                        'phone' => $phone . '@s.whatsapp.net',
                        // 'phone' => '120363402962373513' . '@g.us', //CF cloudflare
                        'message' => $message,
                    ]);
            } elseif ($group = $this->option('group')) {
                $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post(env('WHATSAPP_GO_URL') . '/send/message', [
                        'phone' => $group . '@g.us', // CF cloudflare
                        'message' => $message,
                    ]);
            } else {
                $response = Http::withBasicAuth(env('WHATSAPP_GO_USER'), env('WHATSAPP_GO_PASSWORD'))
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post(env('WHATSAPP_GO_URL') . '/send/message', [
                        'phone' => '62895709700900' . '@s.whatsapp.net',
                        // 'phone' => '120363402962373513' . '@g.us', //CF cloudflare
                        'message' => $message,
                    ]);
            }

            if ($response->successful()) {
                $this->info('📤 Pesan berhasil dikirim ke admin via WhatsApp.');
            } else {
                $this->error('❌ Gagal mengirim pesan. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('❌ Terjadi error saat mengirim pesan: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
