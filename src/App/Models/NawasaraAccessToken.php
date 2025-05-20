<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class NawasaraAccessToken extends PersonalAccessToken
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'plain_text_token',
        'abilities',
        'expires_at',
    ];

    // Relasi ke log hit token
    public function dailyHits()
    {
        return $this->hasMany(NawasaraTokenDailyHit::class, 'personal_access_token_id');
    }

    // Hit total
    public function totalHits()
    {
        return $this->dailyHits()->sum('hit_count');
    }

    // Hit hari ini
    public function todayHits()
    {
        return $this->dailyHits()
            ->whereDate('hit_date', today())
            ->value('hit_count') ?? 0;
    }

    public function tokenable()
    {
        return $this->morphTo();
    }

    // Contoh shortcut untuk ambil user langsung
    public function user()
    {
        return $this->tokenable(); // Alias
    }

    public function regenerate(): string
    {
        $plainText = Str::random(40);
        $hashed = hash('sha256', $plainText);

        $this->update([
            'token' => $hashed,
            'plain_text_token' => Crypt::encryptString($plainText),
        ]);

        return $plainText;
    }

    public static function createTokenWithPlainText(Model $tokenable, string $name, array $abilities = []): self
    {
        $plainText = Str::random(40);
        $hashedToken = hash('sha256', $plainText);

        $token = static::create([
            'tokenable_type' => get_class($tokenable),
            'tokenable_id' => $tokenable->getKey(),
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => $abilities,
            'plain_text_token' => Crypt::encryptString($plainText),
        ]);

        // Attach plain text for return (not saved this way)
        $token->accessToken = $plainText;

        return $token;
    }
}
