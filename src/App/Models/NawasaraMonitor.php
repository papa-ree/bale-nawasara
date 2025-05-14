<?php

namespace Paparee\BaleNawasara\App\Models;

use Spatie\UptimeMonitor\Models\Monitor;

class NawasaraMonitor extends Monitor
{
    protected $table = 'monitors';

    public function dnsRecord()
    {
        return $this->belongsTo(DnsRecord::class);
    }
}
