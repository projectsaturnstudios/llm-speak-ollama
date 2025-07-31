<?php

namespace LLMSpeak\Ollama\Drivers;

use LLMSpeak\Core\Drivers\LLMTranslationDriver;
use LLMSpeak\Core\Support\Requests\LLMSpeakChatRequest;
use LLMSpeak\Core\Support\Responses\LLMSpeakChatResponse;
use LLMSpeak\Ollama\OllamaChatRequest;
use LLMSpeak\Ollama\OllamaChatResponse;

class OllamaLLMSpeakTranslationDriver extends LLMTranslationDriver
{
    public function convertRequest(LLMSpeakChatRequest $communique): OllamaChatRequest
    {
        // Convert Conversation to Ollama messages format
        $messages = [];
        if ($communique->messages) {
            foreach ($communique->messages->getEntries() as $message) {
                $role = strtolower($message->role->value);
                
                // Convert MODEL role to 'assistant' for Ollama API
                if ($role === 'model') {
                    $role = 'assistant';
                }
                
                $messages[] = [
                    'role' => $role,
                    'content' => $message->content
                ];
            }
        }

        // Convert SystemInstructions to system parameter (Ollama has dedicated system param)
        $system = null;
        if ($communique->system_instructions) {
            $systemEntries = $communique->system_instructions->getEntries();
            if ($systemEntries->isNotEmpty()) {
                $system = $systemEntries->map(function ($instruction) {
                    return $instruction->content;
                })->implode("\n\n");
            }
        }

        // Convert ToolKit to Ollama tools format (OpenAI-compatible)
        $tools = null;
        if ($communique->tools) {
            $toolFunctions = [];
            foreach ($communique->tools->getTools() as $tool) {
                $toolFunctions[] = [
                    'type' => 'function',
                    'function' => [
                        'name' => $tool->tool,
                        'description' => $tool->description,
                        'parameters' => $tool->inputSchema
                    ]
                ];
            }
            $tools = $toolFunctions;
        }

        return new OllamaChatRequest(
            model: $communique->model,
            messages: $messages,
            max_tokens: $communique->max_tokens,
            temperature: $communique->temperature,
            tools: $tools,
            tool_choice: $communique->tool_choice,
            stream: $communique->stream,
            response_format: $communique->response_format ? (array) $communique->response_format : null,
            parallel_function_calling: $communique->parallel_function_calling,
            system: $system
        );
    }

    public function convertResponse(LLMSpeakChatResponse $communique): OllamaChatResponse
    {
        // Convert LLMSpeak universal format to Ollama's format
        // Extract message from first choice
        $message = [];
        if (!empty($communique->choices)) {
            $firstChoice = $communique->choices[0];
            $content = $firstChoice['message']['content'] ?? $firstChoice['content'] ?? '';

            $message = [
                'role' => 'assistant',
                'content' => $content
            ];
        }

        // Map finish reason to Ollama's done_reason
        $doneReason = $this->mapToOllamaDoneReason($communique->getFinishReason());

        return new OllamaChatResponse(
            status_code: 200,
            headers: [],
            model: $communique->model,
            message: $message,
            done: true,
            total_duration: 0, // Not available in universal format
            load_duration: 0,  // Not available in universal format
            prompt_eval_count: $communique->getPromptTokens() ?? 0,
            prompt_eval_duration: 0, // Not available in universal format
            eval_count: $communique->getCompletionTokens() ?? 0,
            eval_duration: 0, // Not available in universal format
            created_at: date('c', $communique->created) // Convert to ISO 8601
        );
    }

    public function translateRequest(mixed $communique): LLMSpeakChatRequest
    {
        if(!$communique instanceof OllamaChatRequest) throw new \InvalidArgumentException('Expected OllamaChatRequest instance.');
        
        // Convert Ollama messages back to Conversation
        $conversation = null;
        if (!empty($communique->messages)) {
            $chatMessages = [];
            
            foreach ($communique->messages as $message) {
                $role = $message['role'];
                $content = $message['content'];
                
                // Convert 'assistant' role back to 'model' for universal schema
                if ($role === 'assistant') {
                    $role = 'model';
                }
                
                $chatRole = \LLMSpeak\Core\Enums\ChatRole::from($role);
                $chatMessages[] = new \LLMSpeak\Core\Support\Schema\Conversation\ChatMessage($chatRole, $content);
            }
            
            if (!empty($chatMessages)) {
                $conversation = new \LLMSpeak\Core\Support\Schema\Conversation\Conversation($chatMessages);
            }
        }

        // Convert Ollama system back to SystemInstructions
        $systemInstructions = null;
        if ($communique->system) {
            $systemInstruction = new \LLMSpeak\Core\Support\Schema\SystemInstructions\SystemInstruction($communique->system);
            $systemInstructions = new \LLMSpeak\Core\Support\Schema\SystemInstructions\SystemInstructions([$systemInstruction]);
        }

        // Convert tools back to ToolKit
        $tools = null;
        if ($communique->tools) {
            $toolDefinitions = [];
            foreach ($communique->tools as $tool) {
                $toolDefinitions[] = new \LLMSpeak\Core\Support\Schema\Tools\ToolDefinition(
                    $tool['function']['name'] ?? $tool['name'] ?? '',
                    $tool['function']['description'] ?? $tool['description'] ?? '',
                    $tool['function']['parameters'] ?? $tool['input_schema'] ?? []
                );
            }
            $tools = new \LLMSpeak\Core\Support\Schema\Tools\ToolKit($toolDefinitions);
        }

        return new \LLMSpeak\Core\Support\Requests\LLMSpeakChatRequest(
            model: $communique->model,
            messages: $conversation,
            tools: $tools,
            system_instructions: $systemInstructions,
            max_tokens: $communique->max_tokens,
            temperature: $communique->temperature,
            tool_choice: $communique->tool_choice,
            response_format: is_array($communique->response_format) ? (object) $communique->response_format : $communique->response_format,
            stream: $communique->stream,
            parallel_function_calling: $communique->parallel_function_calling
        );
    }

    public function translateResponse(mixed $communique): LLMSpeakChatResponse
    {
        if(!$communique instanceof OllamaChatResponse) throw new \InvalidArgumentException('Expected OllamaChatResponse instance.');
        
        // Map Ollama response to universal format
        $choices = [];
        if ($communique->message) {
            $choices[] = [
                'index' => 0,
                'message' => [
                    'role' => $communique->message['role'] ?? 'assistant',
                    'content' => $communique->message['content'] ?? ''
                ],
                'finish_reason' => $communique->done ? 'stop' : null
            ];
        }

        // Map usage information - Ollama provides timing stats instead of token counts
        $usage = [
            'prompt_tokens' => 0, // Ollama doesn't provide token counts
            'completion_tokens' => 0,
            'total_tokens' => 0
        ];

        return new \LLMSpeak\Core\Support\Responses\LLMSpeakChatResponse(
            id: 'ollama_' . uniqid(), // Ollama doesn't provide an ID, so generate one
            model: $communique->model,
            created: time(), // Convert Ollama's created_at to timestamp
            choices: $choices,
            usage: $usage,
            finish_reason: $communique->done ? 'stop' : null,
            object: 'chat.completion',
            system_fingerprint: null,
            metadata: [
                'total_duration' => $communique->total_duration ?? null,
                'load_duration' => $communique->load_duration ?? null,
                'prompt_eval_duration' => $communique->prompt_eval_duration ?? null,
                'eval_duration' => $communique->eval_duration ?? null
            ]
        );
    }

    /**
     * Map universal finish_reason to Ollama's done_reason
     */
    private function mapToOllamaDoneReason(?string $finishReason): ?string
    {
        return match($finishReason) {
            'stop' => 'stop',
            'length' => 'length',
            'tool_calls' => 'tool_calls',
            default => 'stop'
        };
    }
}
