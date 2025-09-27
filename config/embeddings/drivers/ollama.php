<?php

return [
    'driver_class' => \LLMSpeak\Ollama\Drivers\Interaction\OllamaEmbeddingsDriver::class,
    'config' => [
        'endpoint_uri' => env('OLLAMA_EMBED_URI', '/embed'),
        'api_key' => env('OLLAMA_API_KEY', null),
    ]
];
