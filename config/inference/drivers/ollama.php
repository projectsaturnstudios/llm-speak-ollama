<?php

return [
    'driver_class' => \LLMSpeak\Ollama\Drivers\Interaction\OllamaInferenceDriver::class,
    'config' => [
        'endpoint_uri' => env('OLLAMA_INFERENCE_URI', '/generate'),
        'api_key' => env('OLLAMA_API_KEY', null),
    ]
];
