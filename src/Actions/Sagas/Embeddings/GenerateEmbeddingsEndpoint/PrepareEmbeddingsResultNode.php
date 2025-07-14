<?php

namespace LLMSpeak\Ollama\Actions\Sagas\Embeddings\GenerateEmbeddingsEndpoint;

use LLMSpeak\HuggingFace\HuggingFaceCallResult;
use LLMSpeak\Ollama\OllamaCallResult;
use LLMSpeak\Ollama\OllamaEmbeddingResult;
use ProjectSaturnStudios\PocketFlow\Node;

class PrepareEmbeddingsResultNode extends Node
{
    public function prep(mixed &$shared): mixed
    {
        return $shared['model_response'];
    }

    public function exec(mixed $prep_res): mixed
    {
        return OllamaEmbeddingResult::from($prep_res);
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared = $exec_res;
        return 'finished';
    }
}
