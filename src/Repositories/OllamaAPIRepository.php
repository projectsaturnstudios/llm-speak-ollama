<?php

namespace LLMSpeak\Ollama\Repositories;

abstract class OllamaAPIRepository
{
    protected ?string $api_key = null;

    public function withApikey(string $api_key): static
    {
        $this->api_key = $api_key;

        return $this;
    }
}
