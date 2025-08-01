<?php
// config/cors.php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Paths
    |--------------------------------------------------------------------------
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Sesuaikan path API Anda

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    */
    'allowed_methods' => ['*'], // Izinkan semua method (GET, POST, PUT, dll.)

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    | **INI YANG PALING PENTING.** '*' mengizinkan semua (TIDAK AMAN!).
    | Gunakan URL frontend spesifik, ambil dari .env.
    */
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:5174',
        'https://lestariputro-frontend-production.up.railway.app'
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    */
    'max_age' => 0, // Cache preflight request (detik)

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    | Set true jika frontend perlu kirim/terima cookies/session/token Auth.
    | Jika true, allowed_origins TIDAK BOLEH '*'.
    */
    'supports_credentials' => true, // Set true jika pakai Sanctum SPA, dll.

];