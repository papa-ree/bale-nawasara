<?php

namespace Paparee\BaleNawasara\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailAccount extends Model
{
    use HasUuids;

    protected $guarded = ['id'];
    
    public function contact(): BelongsTo
    {
        return $this->belongsTo(PicContact::class, 'pic_contact_id');
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::remove('@ponorogo.go.id', $value),
        );
    }
}
