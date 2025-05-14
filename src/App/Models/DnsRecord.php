<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DnsRecord extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::remove('"', $value),
        );
    }

    public function monitor()
    {
        return $this->hasOne(NawasaraMonitor::class);
    }
}
