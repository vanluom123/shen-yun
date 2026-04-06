<?php

// config for Wijourdil/NtfyNotificationChannel
return [

    'server' => env('NTFY_SERVER', 'https://ntfy.sh'),

    'topic' => env('NTFY_TOPIC', 'tiec_tra_shenyun_alerts'),

    'authentication' => [
        'enabled' => (bool) env('NTFY_AUTH_ENABLED', false),
        'username' => env('NTFY_AUTH_USERNAME', ''),
        'password' => env('NTFY_AUTH_PASSWORD', ''),
    ],

    'guzzle' => [
        'verify' => env('NTFY_SSL_VERIFY', false), // Disable SSL verify for Windows
    ],

];
