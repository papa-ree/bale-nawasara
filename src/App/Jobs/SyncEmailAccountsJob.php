<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Paparee\BaleNawasara\App\Models\EmailAccount;
use Paparee\BaleNawasara\App\Services\CpanelEmailService;

class SyncEmailAccountsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected string $domain;

    public function __construct()
    {
        $this->domain = config('bale-nawasara.cpanel.username');
    }

    public function handle()
    {
        $cp = new CpanelEmailService();
        $accounts = $cp->getEmailAccounts();

        Cache::put('email_sync_timestamp', now());

        // Kumpulkan semua email yang masih aktif dari CPanel
        $cpanelEmails = [];

        foreach ($accounts as $acc) {
            $cpanelEmails[] = $acc['email'];
            EmailAccount::updateOrCreate([
                'email' => $acc['email'],
                'login' => $acc['login'],
            ], [
                'suspended_login' => $acc['suspended_login'],
                'suspended_incoming' => $acc['suspended_incoming'],
            ]);
        }

        // Hapus Email yang tidak ada di WHM;
        EmailAccount::whereNotIn('email', $cpanelEmails)->each(function ($email) {
            $email->delete();
        });

        cache()->forget('email_sync_status');
    }
}
