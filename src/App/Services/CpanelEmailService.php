<?php

namespace Paparee\BaleNawasara\App\Services;

use Illuminate\Support\Facades\Http;

class CpanelEmailService
{
    protected $baseUrl;

    protected $token;

    protected $cpanelUser;

    protected $domain;

    public function __construct()
    {
        $this->baseUrl = config('bale-nawasara.cpanel.url'); // e.g. https://your-cpanel-host:2083
        $this->token = config('bale-nawasara.cpanel.token');
        $this->cpanelUser = config('bale-nawasara.cpanel.username');
        $this->domain = config('bale-nawasara.cpanel.domain');
    }

    public function getEmailAccounts()
    {
        $response = Http::withHeaders([
            'Authorization' => "cpanel {$this->cpanelUser}:{$this->token}",
        ])->get("{$this->baseUrl}/execute/Email/list_pops", [
            'domain' => $this->domain,
        ]);

        return $response->json('data') ?? [];
    }

    public function addEmailAccount(string $email, string $password, int $quotaMB = 250)
    {
        $user = explode('@ponorogo.go.id', $email)[0];

        $response = Http::withHeaders([
            'Authorization' => "cpanel {$this->cpanelUser}:{$this->token}",
        ])->post("{$this->baseUrl}/execute/Email/add_pop", [
            'email' => $user,
            'domain' => $this->domain,
            'password' => $password,
            'quota' => $quotaMB,
        ]);

        return $response->json();
    }
}
