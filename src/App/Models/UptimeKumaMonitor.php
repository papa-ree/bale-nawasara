<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UptimeKumaMonitor extends Model
{
    protected $connection = 'uptime';

    protected $table = 'monitor';

    protected $guarded = ['id'];

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function nawasaraMonitor(): HasOne
    {
        return $this->hasOne(KumaMonitor::class);
    }
}
