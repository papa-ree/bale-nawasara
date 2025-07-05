<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Paparee\BaleNawasara\App\Models\DnsRecord;
use Paparee\BaleNawasara\App\Models\NawasaraMonitor;
use Paparee\BaleNawasara\App\Models\NawasaraTokenDailyHit;
use Paparee\BaleNawasara\App\Models\PicContact;

class GenerateNawasaraSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(): void
    {
        $nawasara = new NawasaraMonitor();
        $pic = new PicContact();
        $dns = new DnsRecord();
        $monitor = new NawasaraMonitor();
        $dns_sync_timestamp = cache()->get('dns_sync_timestamp') ?? null;
        $wago_hit_today = new NawasaraTokenDailyHit();

        Cache::put('nawasara_summary', [
            'monitored_subdomains' => $nawasara->count(),
            'new_monitored_subdomains' => $nawasara->whereBetween('created_at', [now()->subDays(7), now()])->count(),
            'valid_ssl' => $nawasara->whereCertificateStatus('valid')->count(),
            'ssl_expiring' => $nawasara->whereNotNull('certificate_expiration_date')->whereBetween('certificate_expiration_date',[now(),now()->addDays(30)])->count(),
            'pic_contacts' => $pic->count(),
            'new_pic_contacts' => $pic->whereBetween('created_at', [now()->subDays(7), now()])->count(),
            'whatsapp_sent_today' => $wago_hit_today->whereDate('created_at', now()->toDateString())->count(),
            'dns_records' => $dns->count(),
            'last_sync_dns_record' => $dns_sync_timestamp,
            'uptime_monitor' => ['up' => $monitor->whereUptimeStatus('up')->count(), 'down' => $monitor->whereUptimeStatus('down')->count()],
        ], now()->addMinutes(10));
    }
}
