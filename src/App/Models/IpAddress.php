<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public function monitor()
    {
        // ip_addresses.address → kuma_monitors.hostname
        return $this->hasOne(KumaMonitor::class);
    }
}
