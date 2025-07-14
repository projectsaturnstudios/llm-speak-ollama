<?php

namespace LLMSpeak\Ollama\Repositories;

class OllamaEmbeddingsAPIRepository extends OllamaAPIRepository
{
    protected ?string $model = null;
    protected ?array $tools = null;
    protected ?array $system_prompt = null;
    protected ?float $temperature = null;
    protected ?int $max_tokens = null;

    public function withModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function withMaxTokens(int $tokens): static
    {
        $this->max_tokens = $tokens;
        return $this;
    }


    public function withSystemPrompt(array $prompt): static
    {
        $this->system_prompt = $prompt;
        return $this;
    }

    public function withTools(array $tools): static
    {
        $this->tools = $tools;
        return $this;
    }

    public function withTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }
}
