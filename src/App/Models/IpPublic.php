<?php

namespace Paparee\BaleNawasara\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IpPublic extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public function monitor(): HasOne
    {
        return $this->hasOne(KumaMonitor::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(PicContact::class, 'pic_contact_id');
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->diffForHumans(),
        );
    }
}
