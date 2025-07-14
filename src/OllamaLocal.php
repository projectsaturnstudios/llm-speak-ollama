<?php

namespace LLMSpeak\Ollama;

use LLMSpeak\Ollama\Repositories\OllamaChatCompletionAPIRepository;
use LLMSpeak\Ollama\Repositories\OllamaEmbeddingsAPIRepository;

class OllamaLocal
{
    public function __construct(protected array $config)
    {

    }

    public function chat_completions(): OllamaChatCompletionAPIRepository
    {
        return new OllamaChatCompletionAPIRepository;
    }

    public function embeddings(): OllamaEmbeddingsAPIRepository
    {
        return new OllamaEmbeddingsAPIRepository;
    }

    /*
    public function models(): OllamaModelsAPIRepository
    {

    }



    */

    public function api_url(): string
    {
        return $this->config['api_url'] ?? 'http://localhost:11434/api/';
    }

    public function api_key(): string
    {
        return $this->config['api_key'];
    }

    public static function boot(): void
    {
        app()->singleton('ollama', function () {
            $results = new static(config('llms.services.ollama'));

            return $results;
        });
    }
}
