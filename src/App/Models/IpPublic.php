<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IpPublic extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public function monitor(): HasOne
    {
        return $this->hasOne(KumaMonitor::class, 'hostname');
    }
}
