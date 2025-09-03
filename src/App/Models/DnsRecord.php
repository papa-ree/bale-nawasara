<?php

namespace Paparee\BaleNawasara\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class DnsRecord extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected static array $errorPatterns = [
        'SSL Handshake Error (525)' => ['code 525', 'error code: 525', 'resulted in a 525'],
        'SSL Handshake Error (526)' => ['526'],
        'DNS Resolution Error (curl 6)' => ['curl error 6'],
        'Failed to Connect (curl 7)' => ['curl error 7'],
        'SSL Certificate Error (curl 60)' => ['curl error 60', 'ssl certificate problem'],
        '404 Not Found' => ['404 not found', 'response: 404', 'status 404'],
        'Internal Server Error' => ['500 internal server error', 'response: 500', 'status 500', 'server error'],
    ];

    public function getUptimeFailureReasonAttribute(): string
    {
        $reason = strtolower($this->uptime_check_failure_reason ?? '');

        foreach (self::$errorPatterns as $label => $patterns) {
            foreach ($patterns as $pattern) {
                if (Str::contains($reason, strtolower($pattern))) {
                    return $label;
                }
            }
        }

        return 'Unknown';
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::remove('.ponorogo.go.id', $value),
        );
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::remove('"', Str::limit($value, 20, '')),
        );
    }

    protected function createdOn(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    public function monitor(): HasOne
    {
        return $this->hasOne(KumaMonitor::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(PicContact::class, 'pic_contact_id');
    }
}
