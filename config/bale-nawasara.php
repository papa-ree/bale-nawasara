<?php

// config for Paparee/BaleNawasara
return [
    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'zone_id' => env('CLOUDFLARE_ZONE_ID'),
    ],

    'mikrotik' => [
        'cache_lifetime' => 60,
    ],

    'whatsapp' => [
        'secret' => env('WHATSAPP_WEBHOOK_SECRET'),
    ],

    'instansi_location' => [
        'api' => 'https://sadap.ponorogo.go.id/api/dataPeta',
    ],

    'cpanel' => [
        'url' => env('CPANEL_URL'),
        'token' => env('CPANEL_API_TOKEN'),
        'username' => env('CPANEL_USERNAME'),
        'domain' => env('CPANEL_DOMAIN'),
    ],

];
