<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Paparee\BaleNawasara\App\Controllers\DnsRecordController;
use Paparee\BaleNawasara\App\Models\DnsRecord;
use Paparee\BaleNawasara\App\Services\CloudflareService;

Route::get('/lang/{locale}', function ($locale) {
    if (! in_array($locale, ['en', 'id'])) {
        abort(400);
    }

    session()->put('locale', $locale);

    return redirect()->back();
});

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

                Route::get('/dns-records', function (Request $request) {
                    $cf = new CloudflareService();
                    $allRecords = collect($cf->getDnsRecords()); // Tambahkan ['result'] karena response Cloudflare menyimpan data di `result`

                    foreach ($allRecords as $record) {
                        DnsRecord::updateOrCreate(
                            ['id' => $record['id']], // kondisi pencarian
                            [
                                'name' => $record['name'] ?? null,
                                'type' => $record['type'] ?? null,
                                'content' => json_encode($record['content']),
                                'proxiable' => $record['proxiable'] ?? null,
                                'proxied' => $record['proxied'] ?? null,
                                'ttl' => $record['ttl'] ?? null,
                                'settings' => isset($record['settings']) ? json_encode($record['settings']) : null,
                                'meta' => isset($record['meta']) ? json_encode($record['meta']) : null,
                                'comment' => $record['comment'] ?? null,
                                'tags' => isset($record['tags']) ? json_encode($record['tags']) : null,
                                'created_on' => $record['created_on'] ?? null,
                                'modified_on' => $record['modified_on'] ?? null,
                                'comment_modified_on' => $record['comment_modified_on'] ?? null,
                                'tags_modified_on' => $record['tags_modified_on'] ?? null,
                            ]
                        );
                    }
                    
                    // dd($allRecords);
                    // SEARCH
                    // if ($request->filled('search')) {
                    //     $search = strtolower($request->search);
                    //     $allRecords = $allRecords->filter(fn($record) =>
                    //         str_contains(strtolower($record['name']), $search) ||
                    //         str_contains(strtolower($record['type']), $search)
                    //     );
                    // }
                
                    // // SORT
                    // if ($request->filled('sortBy')) {
                    //     $allRecords = $allRecords->sortBy($request->sortBy, SORT_REGULAR, $request->sortDirection === 'desc');
                    // }
                
                    // // PAGINATION
                    // $page = (int) $request->input('page', 1);
                    // $perPage = (int) $request->input('perPage', 10);
                    // $paginated = $allRecords->forPage($page, $perPage)->values();
                
                    // return response()->json([
                    //     'data' => $paginated,
                    //     'total' => $allRecords->count(),
                    //     'page' => $page,
                    //     'perPage' => $perPage,
                    //     'lastPage' => ceil($allRecords->count() / $perPage),
                    // ]);
                });

                // sync dns record by alpine trigger
                Route::post('/dns-records/sync', [DnsRecordController::class, 'sync'])->name('dns.sync');
                Route::get('/dns-records/status', [DnsRecordController::class, 'status'])->name('dns.status');

            });
            

            
        });
    });
});
