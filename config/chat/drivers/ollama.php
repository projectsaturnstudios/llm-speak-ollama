<?php

return [
    'driver_class' => \LLMSpeak\Ollama\Drivers\Interaction\OllamaCompletionsDriver::class,
    'config' => [
        'endpoint_uri' => env('OLLAMA_INFERENCE_URI', '/chat'),
        'api_key' => env('OLLAMA_API_KEY', null),
    ]
];
