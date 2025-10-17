<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Autentique API Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com Autentique API para assinatura
    | digital de documentos dos corretores AKAD.
    |
    */
    'autentique' => [
        'api_url' => env('AUTENTIQUE_API_URL', 'https://api.autentique.com.br/v2/graphql'),
        'token' => env('AUTENTIQUE_TOKEN'),
        'timeout' => env('AUTENTIQUE_TIMEOUT', 30),
        // 'webhook_secret' => env('AUTENTIQUE_WEBHOOK_SECRET'), // Opcional - comentado por enquanto
    ],

];
