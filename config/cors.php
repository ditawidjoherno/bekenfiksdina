<?php

return [

    /*
    |----------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |----------------------------------------------------------------------
    |
    | Configuration for cross-origin resource sharing. You may adjust these
    | settings as needed.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],  // Menambahkan paths yang relevan

    'allowed_methods' => ['*'],  // Mengizinkan semua metode HTTP

    'allowed_origins' => [
        'http://localhost:3000',  // Tambahkan URL frontend kamu di sini
        // Contoh: 'https://your-frontend-domain.com'
    ],

    'allowed_origins_patterns' => [],  // Bisa biarkan kosong jika tidak perlu

    'allowed_headers' => ['*'],  // Mengizinkan semua header

    'exposed_headers' => [],  // Biarkan kosong jika tidak perlu

    'max_age' => 0,  // Tidak perlu diubah

    'supports_credentials' => true,  // Pastikan ini true untuk mendukung cookies (atau kredensial lainnya)

];
