<?php

namespace LLMSpeak\Ollama;

use Spatie\LaravelData\Data;

class OllamaCallResult extends Data
{
    public function __construct(
        public readonly string $model,
        public readonly string $created_at,
        public readonly array $message,
        public readonly bool $done,
        public readonly ?int $totalDuration = null,
        public readonly ?int $loadDuration = null,
        public readonly ?int $promptEvalCount = null,
        public readonly ?int $promptEvalDuration = null,
        public readonly ?int $evalCount = null,
        public readonly ?int $evalDuration = null,
        public readonly ?string $doneReason = null
    ) {}



}
