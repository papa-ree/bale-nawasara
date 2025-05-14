<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class NawasaraNotification extends Model
{
    protected $table = 'notifications';

    protected $guarded = [];

    protected $primaryKey = 'id';

    protected static function booted()
    {
        static::creating(function ($notification) {
            $notification->id = Uuid::uuid7();
        });
    }
}
