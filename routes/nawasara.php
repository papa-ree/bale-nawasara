<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Paparee\BaleNawasara\App\Controllers\DnsRecordController;
use Paparee\BaleNawasara\App\Controllers\WebhookController;

Route::get('/lang/{locale}', function ($locale) {
    if (! in_array($locale, ['en', 'id'])) {
        abort(400);
    }

    session()->put('locale', $locale);

    return redirect()->back();
});

// Whatsapp Webhook
Route::post('/webhook/handler', [WebhookController::class, 'handle']);

// landing with theme route
Route::localizedGroup(function () {
    Route::middleware([
        'set locale',
    ])->group(function () {

        Route::middleware([
            'auth:sanctum',
            config('jetstream.auth_session'),
            'verified',
        ])->group(function () {
            Route::group(['middleware' => ['role:developer', 'permission:dashboard']], function () {
                Route::name('dns.')->group(function () {
                    Volt::route('dns', 'nawasara/pages/dns/index')->name('index');
                });

                // sync dns record by alpine trigger
                Route::post('/dns-records/sync', [DnsRecordController::class, 'sync'])->name('dns.sync');
                Route::get('/dns-records/status', [DnsRecordController::class, 'status'])->name('dns.status');

                Volt::route('/network/ip-publics', 'nawasara/pages/ip/index');

                Route::name('tokens.')->group(function () {
                    Volt::route('tokens', 'nawasara/pages/token/index')->name('index');
                    Volt::route('tokens.create', 'nawasara/pages/token/token-cru')->name('create');
                });

            });

        });
    });
});
