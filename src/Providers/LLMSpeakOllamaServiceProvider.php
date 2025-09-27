<?php

namespace LLMSpeak\Ollama\Providers;

use LLMSpeak\Core\Support\Facades\AICompletions;
use LLMSpeak\Core\Support\Facades\AIEmbeddings;
use LLMSpeak\Core\Support\Facades\AIInference;
use LLMSpeak\Ollama\Drivers\Interaction\OllamaCompletionsDriver;
use LLMSpeak\Ollama\Drivers\Interaction\OllamaEmbeddingsDriver;
use LLMSpeak\Ollama\Drivers\Interaction\OllamaInferenceDriver;
use ProjectSaturnStudios\LaravelDesignPatterns\Providers\BaseServiceProvider;

class LLMSpeakOllamaServiceProvider extends BaseServiceProvider
{
    protected array $config = [
        'vector-embeddings.drivers.ollama' => __DIR__ . '/../../config/embeddings/drivers/ollama.php',
        'inferencing.drivers.ollama' => __DIR__ . '/../../config/inference/drivers/ollama.php',
        'chat-completions.drivers.ollama' => __DIR__ . '/../../config/chat/drivers/ollama.php',
    ];
    protected array $publishable_config = [
        [
            'key' => 'vector-embeddings.drivers.ollama',
            'file_path' => __DIR__ . '/../../config/embeddings/drivers/ollama.php',
            'groups' => ['llms', 'llms.ve', 'llms.ve.ollama']
        ],
        [
            'key' => 'inferencing.drivers.ollama',
            'file_path' => 'inferencing/drivers/ollama.php',
            'groups' => ['llms', 'llms.mi', 'llms.mi.ollama']
        ],
        [
            'key' => 'chat-completions.drivers.ollama',
            'file_path' => 'chat-completions/drivers/ollama.php',
            'groups' => ['llms', 'llms.cc', 'llms.cc.ollama']
        ]
    ];

    protected function mainBooted(): void
    {
        AIEmbeddings::extend('ollama', fn() => new OllamaEmbeddingsDriver());
        AIInference::extend('ollama', fn() => new OllamaInferenceDriver());
        AICompletions::extend('ollama', fn() => new OllamaCompletionsDriver());
    }

}
