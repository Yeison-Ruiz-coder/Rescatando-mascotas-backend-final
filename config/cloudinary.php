<?php
// config/cloudinary.php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Esta configuración se usa para conectar con Cloudinary
    |
    */
    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
        'api_key' => env('CLOUDINARY_API_KEY', ''),
        'api_secret' => env('CLOUDINARY_API_SECRET', ''),
    ],

    'url' => [
        'secure' => env('CLOUDINARY_SECURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de imágenes por defecto
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'quality' => 'auto:best',
        'format' => 'auto',
        'width' => 1600,
        'height' => 1600,
        'crop' => 'limit',
        'dpr' => 'auto',
        'flags' => 'lossy',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tamaños predefinidos para diferentes usos
    |--------------------------------------------------------------------------
    */
    'sizes' => [
        'thumbnail' => ['width' => 100, 'height' => 100, 'crop' => 'thumb'],
        'small' => ['width' => 300, 'height' => 200, 'crop' => 'fill'],
        'medium' => ['width' => 600, 'height' => 400, 'crop' => 'fill'],
        'large' => ['width' => 1200, 'height' => 800, 'crop' => 'limit'],
        'featured' => ['width' => 800, 'height' => 600, 'crop' => 'fill'],
        'full' => ['width' => 1600, 'height' => 1600, 'crop' => 'limit'],
    ]
];
