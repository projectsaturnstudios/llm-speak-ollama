<?php

namespace LLMSpeak\Ollama\Repositories;

use LLMSpeak\Ollama\Actions\OllamaAPI\ChatCompletions\ChatCompletionsEndpoint;
use LLMSpeak\Ollama\Support\Facades\Ollama;

class OllamaChatCompletionAPIRepository extends OllamaAPIRepository
{
    protected ?string $model = null;
    protected ?array $tools = null;
    protected ?array $messages = null;
    protected ?array $options = null;

    public function withModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function withMessages(array $conversation): ChatCompletionsEndpoint
    {
        $this->messages = $conversation;
        return new ChatCompletionsEndpoint(
            url: Ollama::api_url(),
            model: $this->model,
            messages: $this->messages,
            format: 'json',
            tools: $this->tools,
            think: false,
            options: $this->options
        );
    }


    public function withTools(array $tools): static
    {
        $this->tools = $tools;
        return $this;
    }

    public function withOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }
}
