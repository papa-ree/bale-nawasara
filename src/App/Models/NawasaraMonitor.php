<?php

namespace Paparee\BaleNawasara\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Spatie\UptimeMonitor\Models\Monitor;

class NawasaraMonitor extends Monitor
{
    protected $table = 'monitors';

    protected $guarded = ['id'];

    protected $primaryKey = 'id';

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::remove('.ponorogo.go.id', $value),
        );
    }

    protected function uptimeLastCheckDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->diffForHumans() : '-',
        );
    }

    protected function certificateExpirationDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->diffForHumans() : '-',
        );
    }

    public function dnsRecord()
    {
        return $this->belongsTo(DnsRecord::class);
    }
}
