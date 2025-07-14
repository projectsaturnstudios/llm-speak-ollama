<?php


return [
    /*
    |--------------------------------------------------------------------------
    | Anthropic API URL
    |--------------------------------------------------------------------------
    |
    | Here you may specify the base URL for the Anthropic API. The default
    | is the official Anthropic API endpoint, but you can change it if needed.
    |
    |
    */

    'api_url' => env('OLLAMA_URL', 'http://localhost:11434/api/'),
];
