<?php

namespace LLMSpeak\Ollama\Drivers;

use LLMSpeak\Ollama\OllamaEmbeddingsRequest;
use LLMSpeak\Ollama\OllamaEmbeddingsResponse;
use LLMSpeak\Core\Drivers\LLMEmbeddingsDriver;
use LLMSpeak\Core\Support\Requests\LLMSpeakEmbeddingsRequest;
use LLMSpeak\Core\Support\Responses\LLMSpeakEmbeddingsResponse;

class OllamaLLMSpeakEmbeddingsDriver extends LLMEmbeddingsDriver
{
    public function convertRequest(LLMSpeakEmbeddingsRequest $communique): OllamaEmbeddingsRequest
    {
        // Build options array for any additional configuration
        $options = [];
        
        // Add dimensions to options if specified (some Ollama models may support this)
        if ($communique->dimensions !== null) {
            $options['dimensions'] = $communique->dimensions;
        }
        
        // Add task_type as an option if specified
        if ($communique->task_type !== null) {
            $options['task_type'] = $communique->task_type;
        }

        return new OllamaEmbeddingsRequest(
            model: $communique->model,
            input: $communique->input, // Direct mapping - both support string|array
            encodingFormat: $communique->encoding_format,
            truncate: null, // Not available in universal request
            options: !empty($options) ? $options : null,
            keepAlive: null // Not available in universal request
        );
    }

    public function convertResponse(LLMSpeakEmbeddingsResponse $communique): OllamaEmbeddingsResponse
    {
        // Convert universal format to Ollama's simple embedding array structure
        $embeddings = [];
        
        foreach ($communique->data as $item) {
            // Extract embedding vector from the item
            $embedding = $item['embedding'] ?? $item;
            $embeddings[] = $embedding;
        }

        return new OllamaEmbeddingsResponse(
            model: $communique->model,
            embeddings: $embeddings,
            total_duration: null, // Not available in universal format
            load_duration: null,  // Not available in universal format
            prompt_eval_count: $communique->getPromptTokens() // Map from usage info
        );
    }

    public function translateRequest(mixed $communique): LLMSpeakEmbeddingsRequest
    {
        if(!$communique instanceof OllamaEmbeddingsRequest) throw new \InvalidArgumentException('Expected OllamaEmbeddingsRequest instance.');
        
        // Extract dimensions and task_type from options array if present
        $dimensions = null;
        $taskType = null;
        
        if ($communique->options) {
            $dimensions = $communique->options['dimensions'] ?? null;
            $taskType = $communique->options['task_type'] ?? null;
        }

        return new LLMSpeakEmbeddingsRequest(
            model: $communique->model,
            input: $communique->input, // Direct mapping - both support string|array
            encoding_format: $communique->encodingFormat,
            dimensions: $dimensions,
            task_type: $taskType
        );
    }

    public function translateResponse(mixed $communique): LLMSpeakEmbeddingsResponse
    {
        if(!$communique instanceof OllamaEmbeddingsResponse) throw new \InvalidArgumentException('Expected OllamaEmbeddingsResponse instance.');
        
        // Convert Ollama embeddings array to universal data format
        $data = [];
        foreach ($communique->embeddings as $index => $embedding) {
            $data[] = [
                'object' => 'embedding',
                'embedding' => $embedding,
                'index' => $index
            ];
        }

        // Convert Ollama's performance metrics to usage format
        $usage = [
            'prompt_tokens' => $communique->prompt_eval_count ?? 0,
            'total_tokens' => $communique->prompt_eval_count ?? 0
        ];

        return new LLMSpeakEmbeddingsResponse(
            model: $communique->model,
            data: $data,
            usage: $usage,
            object: 'list',
            metadata: [
                'total_duration' => $communique->total_duration,
                'load_duration' => $communique->load_duration,
                'total_duration_ms' => $communique->getTotalDurationMs(),
                'load_duration_ms' => $communique->getLoadDurationMs()
            ]
        );
    }
}
