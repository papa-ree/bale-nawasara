<?php

namespace Paparee\BaleNawasara\App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Paparee\BaleNawasara\App\Models\NawasaraTokenDailyHit;

class TrackTokenHitDaily
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken) {
                NawasaraTokenDailyHit::updateOrCreate(
                    [
                        'personal_access_token_id' => $accessToken->id,
                        'hit_date' => today(),
                    ],
                    [
                        'hit_count' => DB::raw('hit_count + 1'),
                    ]
                );
            }
        }

        return $next($request);
    }
}
