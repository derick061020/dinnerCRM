<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL base del sitio WordPress / WooCommerce
    |--------------------------------------------------------------------------
    | Sin barra final. Ej: https://puntacanadinnerinthesky.com
    */
    'base_url' => rtrim(env('WOO_BASE_URL', 'https://puntacanadinnerinthesky.com'), '/'),

    /*
    |--------------------------------------------------------------------------
    | Credenciales de la REST API de WooCommerce
    |--------------------------------------------------------------------------
    | Generar en: WooCommerce → Ajustes → Avanzado → REST API → Añadir clave
    | (permisos Lectura/Escritura). Las del sitio dev NO sirven en producción.
    */
    'consumer_key' => env('WOO_CONSUMER_KEY'),
    'consumer_secret' => env('WOO_CONSUMER_SECRET'),

    /*
    | Encabezado Authorization ya armado (Basic auth) para usar en las requests.
    */
    'auth' => 'Basic ' . base64_encode(env('WOO_CONSUMER_KEY') . ':' . env('WOO_CONSUMER_SECRET')),

];
