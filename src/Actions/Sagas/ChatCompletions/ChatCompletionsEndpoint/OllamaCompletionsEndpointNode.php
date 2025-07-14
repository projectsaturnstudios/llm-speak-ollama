<?php

namespace LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint;

use Illuminate\Support\Facades\Http;
use ProjectSaturnStudios\PocketFlow\Node;
use Symfony\Component\VarDumper\VarDumper;

class OllamaCompletionsEndpointNode extends Node
{
    public function __construct(protected string $url)
    {
        parent::__construct();
    }

    public function prep(mixed &$shared): mixed
    {
        return $shared['prepared_request'];
    }

    public function exec(mixed $prep_res): mixed
    {
        $response = Http::withHeaders($prep_res['headers'])->post($this->url, $prep_res['body']);
        VarDumper::dump(['Ollama Request - OllamaCompletionsEndpointNode', json_encode($prep_res['body'])]);
        VarDumper::dump(['Ollama Results - OllamaCompletionsEndpointNode', $response->json()]);
        return $response->json();
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['model_response'] = $exec_res;
        return 'wrap-up';
    }
}
