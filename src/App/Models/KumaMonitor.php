<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KumaMonitor extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? json_decode($value) : null,
            set: fn($value) => $value ? json_encode($value) : null,
        );
    }

    protected function notificationIdList(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? json_decode($value, true) : null,
            set: fn($value) => $value ? json_encode($value) : null,
        );
    }

    public function ipPublic(): BelongsTo
    {
        return $this->belongsTo(IpPublic::class);
    }

    public function dnsRecord(): BelongsTo
    {
        return $this->belongsTo(DnsRecord::class);
    }

    public function uptime(): BelongsTo
    {
        return $this->belongsTo(UptimeKumaMonitor::class, 'kuma_id');
    }
}
