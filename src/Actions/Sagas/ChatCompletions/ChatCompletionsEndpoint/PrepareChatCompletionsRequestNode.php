<?php

namespace LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint;

use ProjectSaturnStudios\PocketFlow\Node;

class PrepareChatCompletionsRequestNode extends Node
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
                'stream' => false,
            ],
        ];

        if(array_key_exists('model', $prep_res)) $results['body']['model'] = $prep_res['model'];
        else throw new \InvalidArgumentException('Model is required for the request.');

        if(array_key_exists('messages', $prep_res)) $results['body']['messages'] = $prep_res['messages'];
        else throw new \InvalidArgumentException('Messages are required for the request.');

        //if(array_key_exists('format', $prep_res)) $results['body']['format'] = $prep_res['format'];
        if(array_key_exists('tools', $prep_res) && (!empty($prep_res['tools']))) $results['body']['tools'] = $prep_res['tools'];
        //if(array_key_exists('think', $prep_res)) $results['body']['think'] = $prep_res['think'];
        if(array_key_exists('options', $prep_res) && (!empty($prep_res['options']))) {
            $results['body']['options'] = $prep_res['options'];
        } else {
            $results['body']['options'] = [
                'num_ctx' => 32768,
                'num_thread' => 6,
            ];
        }

        return $results;
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['prepared_request'] = $exec_res;
        return 'call';
    }
}
