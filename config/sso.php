<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO API Configuration
    |--------------------------------------------------------------------------
    */

    'api_url' => env('SSO_API_URL'),
    'api_key' => env('SSO_API_KEY', ''),
    'cookie_name' => env('SSO_COOKIE_NAME'),
    'frontend_url' => env('SSO_FRONTEND_URL'),
];
