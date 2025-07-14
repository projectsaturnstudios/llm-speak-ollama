<?php

namespace LLMSpeak\Ollama;

use Spatie\LaravelData\Data;

class OllamaEmbeddingResult extends Data
{
    public function __construct(
        public readonly string $model,
        public readonly array $embeddings,
        public readonly ?int $totalDuration = null,
        public readonly ?int $loadDuration = null,
        public readonly ?int $promptEvalCount = null,
    ) {}



}
