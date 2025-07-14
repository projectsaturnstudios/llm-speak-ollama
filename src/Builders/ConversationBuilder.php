<?php

namespace LLMSpeak\Ollama\Builders;


use LLMSpeak\Ollama\Enums\OllamaRole;

class ConversationBuilder
{
    protected array $conversation = [];

    public function addText(OllamaRole $role, string $content): static
    {
        $this->conversation[] = [
            'role' => $role->value,
            'content' => $content,
        ];

        return $this;
    }

    public function addToolRequest(string $id, string $name, array $input): static
    {
        $this->conversation[] = [
            'role' => 'assistant',
            'tool_calls' => [
                [
                    'id' => $id,
                    'type' => 'function',
                    'function' => [
                        'name' => $name,
                        'arguments' => json_encode($input)
                    ]
                ]
            ]
        ];
        return $this;
    }

    public function addToolResult(string $name, mixed $content): static
    {
        $this->conversation[] = [
            'role' => 'tool',
            'tool_name' => $name,
            'content' => json_encode($content)
        ];
        return $this;
    }

    public function render(): array
    {
        return $this->conversation;
    }
}
