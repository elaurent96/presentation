<?php

return [
    'api' => [
        'title' => 'Presentation Studio API',
        'version' => '1.0.0',
        'prefix' => 'api',
        'domain' => env('L5_SWAGGER_DOMAIN', null),
        'middleware' => [
            'web',
            'api',
        ],
    ],
    'routes' => [
        'api' => '/api/documentation',
        'docs' => '/docs',
        'oauth2_callback' => '/api/oauth2-callback',
    ],
    'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
    'generate_yaml' => env('L5_SWAGGER_GENERATE_YAML', false),
    'yaml' => [
        'output' => resource_path('api-docs'),
        'output_file' => 'api-docs.yaml',
        'save' => true,
    ],
    'constants' => [
        'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost:8000'),
    ],
    'scan' => [
        'enabled' => true,
        'paths' => [
            base_path('app'),
        ],
        'exclude' => [
            base_path('app/Console'),
            base_path('app/Exceptions'),
            base_path('app/Http/Middleware'),
            base_path('app/Providers'),
        ],
    ],
    'security' => [
        'Authorization' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
            'description' => 'Enter your API token (94 characters)',
        ],
    ],
];
