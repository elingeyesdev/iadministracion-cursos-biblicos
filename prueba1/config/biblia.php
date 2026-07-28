<?php

return [
    /**
     * URL de la API de A Bíblia Digital
     * Reemplaza con tu URL correcta
     */
    'base_url' => env('BIBLIA_DIGITAL_API_URL', 'https://www.seudominio.com.br/api'),
    
    /**
     * URL del endpoint GraphQL
     */
    'graphql_url' => env('BIBLIA_DIGITAL_GRAPHQL_URL', 'https://www.seudominio.com.br/graphql'),

    /**
     * Tiempo de expiración del caché (en segundos)
     */
    'cache_expiration' => env('BIBLIA_CACHE_EXPIRATION', 86400), // 24 horas

    /**
     * Habilitar modo debug
     */
    'debug' => env('BIBLIA_DEBUG', false),

    /**
     * Usar datos mock para desarrollo
     */
    'use_mock_data' => env('BIBLIA_USE_MOCK_DATA', false),

    /**
     * Timeout para las peticiones HTTP (en segundos)
     */
    'timeout' => env('BIBLIA_TIMEOUT', 30),
    'connect_timeout' => env('BIBLIA_CONNECT_TIMEOUT', 10),
];
