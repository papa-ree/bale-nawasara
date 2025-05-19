<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Model;

class NawasaraTokenDailyHit extends Model
{
    protected $table = 'personal_access_token_hits';

    protected $fillable = ['personal_access_token_id', 'hit_date', 'hit_count'];

    public $timestamps = true;

    public function token()
    {
        return $this->belongsTo(NawasaraAccessToken::class, 'personal_access_token_id');
    }
}
