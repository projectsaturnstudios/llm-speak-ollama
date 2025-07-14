<?php

namespace LLMSpeak\Ollama\Support\Facades;

use Illuminate\Support\Facades\Facade;
use LLMSpeak\Ollama\Repositories\OllamaChatCompletionAPIRepository;
use LLMSpeak\Ollama\Repositories\OllamaEmbeddingsAPIRepository;

/**
 * @method static string api_url()
 * @method static string api_key()
 * @method static OllamaChatCompletionAPIRepository chat_completions()
 * @method static OllamaEmbeddingsAPIRepository embeddings()
 *
 * @see \LLMSpeak\Ollama\OllamaLocal
 */
class Ollama extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ollama';
    }
}
