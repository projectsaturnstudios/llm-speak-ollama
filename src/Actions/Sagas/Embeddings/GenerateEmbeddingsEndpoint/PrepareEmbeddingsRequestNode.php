<?php

namespace LLMSpeak\Ollama\Actions\Sagas\Embeddings\GenerateEmbeddingsEndpoint;

use ProjectSaturnStudios\PocketFlow\Node;

class PrepareEmbeddingsRequestNode extends Node
{
    public function prep(mixed &$shared): mixed
    {
        return $shared['available_parameters'];
    }

    public function exec(mixed $prep_res): mixed
    {
        $results = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => [

            ],
        ];

        if(array_key_exists('model', $prep_res)) $results['body']['model'] = $prep_res['model'];
        else throw new \InvalidArgumentException('Model is required for the request.');

        if(array_key_exists('input', $prep_res)) $results['body']['input'] = $prep_res['input'];
        else throw new \InvalidArgumentException('Input is required for the request.');

        return $results;
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['prepared_request'] = $exec_res;
        return 'call';
    }
}
