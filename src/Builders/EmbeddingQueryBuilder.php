<?php

namespace LLMSpeak\Ollama\Builders;

class EmbeddingQueryBuilder
{
    protected array $conversation = [];

    public function addQuery(string $content): static
    {
        $this->conversation[] = $content;

        return $this;
    }

    public function render(): array|string
    {
        return count($this->conversation) == 1 ? $this->conversation[0] : $this->conversation;
    }
}
