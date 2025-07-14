<?php

namespace LLMSpeak\Ollama\Builders;

class SystemPromptBuilder
{
    protected array $conversation = [];

    public function addText(string $content): static
    {
        $this->conversation[] = [
            'type' => 'text',
            'text' => $content,
        ];

        return $this;
    }

    public function addContent(array $content): static
    {
        return $this;
    }

    public function render(): array
    {
        return $this->conversation;
    }
}
