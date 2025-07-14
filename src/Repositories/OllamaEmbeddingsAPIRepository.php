<?php

namespace LLMSpeak\Ollama\Repositories;

use LLMSpeak\Ollama\Actions\OllamaAPI\Embeddings\GenerateEmbeddingsEndpoint;
use LLMSpeak\Ollama\Support\Facades\Ollama;

class OllamaEmbeddingsAPIRepository extends OllamaAPIRepository
{
    protected ?string $model = null;
    protected string|array|null $input = null;

    public function withModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function withInput(string|array $conversation): GenerateEmbeddingsEndpoint
    {
        $this->input = $conversation;
        return new GenerateEmbeddingsEndpoint(
            url: Ollama::api_url(),
            model: $this->model,
            input: $this->input,
        );
    }
}
