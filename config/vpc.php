<?php

/** Configuración propia de Voy por Colombia. */
return [
    'upload_mb' => [
        'image' => (int) env('UPLOAD_MAX_IMAGE_MB', 12),
        'video' => (int) env('UPLOAD_MAX_VIDEO_MB', 200),
        'audio' => (int) env('UPLOAD_MAX_AUDIO_MB', 40),
        'document' => (int) env('UPLOAD_MAX_DOC_MB', 20),
    ],
    'image_quality' => (int) env('IMAGE_QUALITY', 80),

    // Capa opcional de calles o relieve bajo el mapa ilustrado (vacío = sin servicios externos).
    'map_tile_url' => env('MAP_TILE_URL', ''),
    'map_tile_attribution' => env('MAP_TILE_ATTRIBUTION', ''),

    // Token para crear la primera cuenta desde un hosting sin consola (en localhost no hace falta).
    'setup_token' => env('SETUP_TOKEN', ''),
];
