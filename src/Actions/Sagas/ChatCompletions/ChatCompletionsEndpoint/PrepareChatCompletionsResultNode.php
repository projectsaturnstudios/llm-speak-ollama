<?php

namespace LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint;

use LLMSpeak\HuggingFace\HuggingFaceCallResult;
use LLMSpeak\Ollama\OllamaCallResult;
use ProjectSaturnStudios\PocketFlow\Node;

class PrepareChatCompletionsResultNode extends Node
{
    public function prep(mixed &$shared): mixed
    {
        return $shared['model_response'];
    }

    public function exec(mixed $prep_res): mixed
    {
        return OllamaCallResult::from($prep_res);
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared = $exec_res;
        return 'finished';
    }
}
