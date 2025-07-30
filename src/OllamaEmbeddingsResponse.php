<?php

namespace LLMSpeak\Ollama;

use Spatie\LaravelData\Data;

/**
 * OllamaEmbeddingsResponse - Ollama Embeddings API Response Handler
 * 
 * Represents the complete response structure from the Ollama Embeddings API.
 * Built using the same pattern as ClaudeMessageResponse for consistency.
 * 
 * Usage Examples:
 * 
 * // Create from API response
 * $response = OllamaEmbeddingsResponse::fromApiResponse($apiData);
 * 
 * // Access embeddings data
 * $embeddings = $response->getEmbeddings();
 * $model = $response->model;
 * $duration = $response->getTotalDurationMs();
 * 
 * // Check for multiple embeddings
 * if ($response->hasMultipleEmbeddings()) {
 *     $count = $response->getEmbeddingCount();
 * }
 * 
 * // Get performance metrics
 * $loadTime = $response->getLoadDurationMs();
 * $tokensEvaluated = $response->prompt_eval_count;
 */
class OllamaEmbeddingsResponse extends Data
{
    public function __construct(
        public readonly string $model,
        public readonly array $embeddings,
        public readonly ?int $total_duration = null,
        public readonly ?int $load_duration = null,
        public readonly ?int $prompt_eval_count = null
    ) {}

    /**
     * Create instance from raw API response array
     */
    public static function fromApiResponse(array $response): self
    {
        return new self(
            model: $response['model'],
            embeddings: $response['embeddings'] ?? [],
            total_duration: $response['total_duration'] ?? null,
            load_duration: $response['load_duration'] ?? null,
            prompt_eval_count: $response['prompt_eval_count'] ?? null
        );
    }

    // Embedding Access Methods
    
    public function getEmbeddings(): array
    {
        return $this->embeddings;
    }

    public function getFirstEmbedding(): ?array
    {
        return $this->embeddings[0] ?? null;
    }

    public function getEmbeddingAt(int $index): ?array
    {
        return $this->embeddings[$index] ?? null;
    }

    public function getEmbeddingCount(): int
    {
        return count($this->embeddings);
    }

    public function hasEmbeddings(): bool
    {
        return !empty($this->embeddings);
    }

    public function hasMultipleEmbeddings(): bool
    {
        return count($this->embeddings) > 1;
    }

    // Performance Analysis Methods
    
    public function getTotalDurationMs(): ?float
    {
        return $this->total_duration ? $this->total_duration / 1_000_000 : null;
    }

    public function getLoadDurationMs(): ?float
    {
        return $this->load_duration ? $this->load_duration / 1_000_000 : null;
    }

    public function getTotalDurationSeconds(): ?float
    {
        return $this->total_duration ? $this->total_duration / 1_000_000_000 : null;
    }

    public function getLoadDurationSeconds(): ?float
    {
        return $this->load_duration ? $this->load_duration / 1_000_000_000 : null;
    }

    // Embedding Analysis Methods
    
    public function getEmbeddingDimensions(): ?int
    {
        $firstEmbedding = $this->getFirstEmbedding();
        return $firstEmbedding ? count($firstEmbedding) : null;
    }

    public function getAverageEmbeddingLength(): ?float
    {
        if (empty($this->embeddings)) {
            return null;
        }

        $totalLength = array_sum(array_map('count', $this->embeddings));
        return $totalLength / count($this->embeddings);
    }

    public function getAllEmbeddingDimensions(): array
    {
        return array_map('count', $this->embeddings);
    }

    // Model Performance Methods
    
    public function getTokensPerSecond(): ?float
    {
        if (!$this->prompt_eval_count || !$this->total_duration) {
            return null;
        }

        $seconds = $this->total_duration / 1_000_000_000;
        return $this->prompt_eval_count / $seconds;
    }

    public function getProcessingEfficiency(): ?float
    {
        if (!$this->total_duration || !$this->load_duration) {
            return null;
        }

        $processingTime = $this->total_duration - $this->load_duration;
        return $processingTime > 0 ? ($processingTime / $this->total_duration) * 100 : 0.0;
    }

    public function wasModelAlreadyLoaded(): bool
    {
        // If load duration is very small compared to total duration,
        // the model was likely already in memory
        if (!$this->load_duration || !$this->total_duration) {
            return false;
        }

        $loadPercentage = ($this->load_duration / $this->total_duration) * 100;
        return $loadPercentage < 5; // Less than 5% of total time spent loading
    }

    // Utility Methods
    
    public function toSummary(): array
    {
        return [
            'model' => $this->model,
            'embedding_count' => $this->getEmbeddingCount(),
            'dimensions' => $this->getEmbeddingDimensions(),
            'total_duration_ms' => $this->getTotalDurationMs(),
            'load_duration_ms' => $this->getLoadDurationMs(),
            'tokens_evaluated' => $this->prompt_eval_count,
            'tokens_per_second' => $this->getTokensPerSecond(),
            'model_was_cached' => $this->wasModelAlreadyLoaded(),
        ];
    }

    public function hasPerformanceData(): bool
    {
        return $this->total_duration !== null || 
               $this->load_duration !== null || 
               $this->prompt_eval_count !== null;
    }
}
