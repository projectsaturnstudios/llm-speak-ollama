<?php

namespace LLMSpeak\Ollama;

use Spatie\LaravelData\Data;

/**
 * OllamaChatResponse - Ollama API Response Handler
 * 
 * Represents the complete response structure from the Ollama Chat API.
 * Built using the same pattern as ClaudeMessageResponse for consistency.
 * 
 * Usage Examples:
 * 
 * // Create from API response
 * $response = OllamaChatResponse::fromApiResponse($apiData);
 * 
 * // Access response data
 * $content = $response->getTextContent();
 * $tokens = $response->getTotalTokens();
 * $wasToolUsed = $response->usedTools();
 * 
 * // Check completion status
 * if ($response->completedNaturally()) {
 *     // Handle successful completion
 * }
 */
class OllamaChatResponse extends Data
{
    public function __construct(
        public readonly int $status_code,
        public readonly array $headers,
        public readonly string|null $model,
        public readonly array $message,
        public readonly bool $done,
        public readonly int|null $total_duration,
        public readonly int|null $load_duration,
        public readonly int|null $prompt_eval_count,
        public readonly int|null $prompt_eval_duration,
        public readonly int|null $eval_count,
        public readonly int|null $eval_duration,
        public readonly string|null $created_at,
        public readonly array|null $context = null,
        public readonly bool $is_streaming = false,
        public readonly array $streaming_chunks = [],
        public readonly string|null $raw_body = null,
        public readonly array|null $final_chunk = null
    ) {}

    /**
     * Create instance from raw API response array
     */
    public static function fromApiResponse(array $response): self
    {
        $body = $response['body'] ?? [];
        
        // Handle streaming responses
        if (isset($body['streaming']) && $body['streaming']) {
            $finalChunk = $body['final_chunk'] ?? [];
            return new self(
                status_code: $response['status_code'] ?? 200,
                headers: $response['headers'] ?? [],
                model: $finalChunk['model'] ?? null,
                message: $finalChunk['message'] ?? [],
                done: $finalChunk['done'] ?? true,
                total_duration: $finalChunk['total_duration'] ?? null,
                load_duration: $finalChunk['load_duration'] ?? null,
                prompt_eval_count: $finalChunk['prompt_eval_count'] ?? null,
                prompt_eval_duration: $finalChunk['prompt_eval_duration'] ?? null,
                eval_count: $finalChunk['eval_count'] ?? null,
                eval_duration: $finalChunk['eval_duration'] ?? null,
                created_at: $finalChunk['created_at'] ?? null,
                context: $finalChunk['context'] ?? null,
                is_streaming: true,
                streaming_chunks: $body['chunks'] ?? [],
                raw_body: $body['raw_body'] ?? null,
                final_chunk: $finalChunk
            );
        }

        // Handle non-streaming responses
        return new self(
            status_code: $response['status_code'] ?? 200,
            headers: $response['headers'] ?? [],
            model: $body['model'] ?? null,
            message: $body['message'] ?? [],
            done: $body['done'] ?? true,
            total_duration: $body['total_duration'] ?? null,
            load_duration: $body['load_duration'] ?? null,
            prompt_eval_count: $body['prompt_eval_count'] ?? null,
            prompt_eval_duration: $body['prompt_eval_duration'] ?? null,
            eval_count: $body['eval_count'] ?? null,
            eval_duration: $body['eval_duration'] ?? null,
            created_at: $body['created_at'] ?? null,
            context: $body['context'] ?? null,
            is_streaming: false,
            streaming_chunks: [],
            raw_body: null,
            final_chunk: null
        );
    }

    // Content Access Methods
    
    public function getTextContent(): string|null
    {
        return $this->message['content'] ?? null;
    }

    public function getAllTextContent(): array
    {
        $content = $this->getTextContent();
        return $content ? [$content] : [];
    }

    public function getToolCalls(): array
    {
        return $this->message['tool_calls'] ?? [];
    }

    public function hasToolCalls(): bool
    {
        return !empty($this->getToolCalls());
    }

    public function getThinkingContent(): string|null
    {
        return $this->message['thinking'] ?? null;
    }

    public function hasThinking(): bool
    {
        return !empty($this->getThinkingContent());
    }

    // Status Check Methods
    
    public function completedNaturally(): bool
    {
        return $this->done && !$this->hasToolCalls();
    }

    public function usedTools(): bool
    {
        return $this->hasToolCalls();
    }

    public function isComplete(): bool
    {
        return $this->done;
    }

    public function isStreaming(): bool
    {
        return $this->is_streaming;
    }

    public function isSuccessful(): bool
    {
        return $this->status_code >= 200 && $this->status_code < 300;
    }

    // Token Analysis Methods
    
    public function getTotalTokens(): int
    {
        return $this->getPromptTokens() + $this->getCompletionTokens();
    }

    public function getPromptTokens(): int
    {
        return $this->prompt_eval_count ?? 0;
    }

    public function getCompletionTokens(): int
    {
        return $this->eval_count ?? 0;
    }

    // Performance Analysis Methods
    
    public function getTotalDurationMs(): float
    {
        return $this->total_duration ? $this->total_duration / 1_000_000 : 0.0;
    }

    public function getLoadDurationMs(): float
    {
        return $this->load_duration ? $this->load_duration / 1_000_000 : 0.0;
    }

    public function getPromptEvalDurationMs(): float
    {
        return $this->prompt_eval_duration ? $this->prompt_eval_duration / 1_000_000 : 0.0;
    }

    public function getEvalDurationMs(): float
    {
        return $this->eval_duration ? $this->eval_duration / 1_000_000 : 0.0;
    }

    public function getTokensPerSecond(): float
    {
        $evalDurationSeconds = $this->eval_duration ? $this->eval_duration / 1_000_000_000 : 0;
        $completionTokens = $this->getCompletionTokens();
        
        return $evalDurationSeconds > 0 ? $completionTokens / $evalDurationSeconds : 0.0;
    }

    // Context Methods
    
    public function hasContext(): bool
    {
        return !empty($this->context);
    }

    public function getContext(): array|null
    {
        return $this->context;
    }

    // Streaming Methods
    
    public function getStreamingChunks(): array
    {
        return $this->streaming_chunks;
    }

    public function getChunkCount(): int
    {
        return count($this->streaming_chunks);
    }

    public function getFinalChunk(): array|null
    {
        return $this->final_chunk;
    }

    public function getRawStreamingBody(): string|null
    {
        return $this->raw_body;
    }

    // Model Information Methods
    
    public function getModel(): string|null
    {
        return $this->model;
    }

    public function getCreatedAt(): string|null
    {
        return $this->created_at;
    }

    public function getRole(): string
    {
        return $this->message['role'] ?? 'assistant';
    }

    // Debug Methods
    
    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function toDebugArray(): array
    {
        return [
            'status_code' => $this->status_code,
            'model' => $this->model,
            'done' => $this->done,
            'is_streaming' => $this->is_streaming,
            'has_content' => !empty($this->getTextContent()),
            'has_tool_calls' => $this->hasToolCalls(),
            'has_thinking' => $this->hasThinking(),
            'total_tokens' => $this->getTotalTokens(),
            'duration_ms' => $this->getTotalDurationMs(),
            'tokens_per_second' => $this->getTokensPerSecond(),
        ];
    }
}
