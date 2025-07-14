<?php

namespace LLMSpeak\Ollama\Actions\OllamaAPI\ChatCompletions;

use LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint\OllamaCompletionsEndpointNode;
use LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint\PrepareChatCompletionsRequestNode;
use LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint\PrepareChatCompletionsResultNode;
use LLMSpeak\Ollama\Builders\ConversationBuilder;
use LLMSpeak\Ollama\Enums\OllamaRole;
use LLMSpeak\Ollama\OllamaCallResult;
use LLMSpeak\Ollama\Support\Facades\Ollama;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\Data;

class ChatCompletionsEndpoint extends Data
{
    use AsAction;

    protected string $uri = 'chat';

    public function __construct(
        public readonly string $url,
        public readonly string $model,
        public readonly array $messages,
        public readonly string $format = 'json',
        public readonly ?array $tools = null,
        public readonly ?bool $think = false,
        public readonly ?array $options = null,
    ) {}

    public function handle(): OllamaCallResult
    {
        $work_nodes = new PrepareChatCompletionsRequestNode;
        $work_nodes->next(new OllamaCompletionsEndpointNode("{$this->url}{$this->uri}"), 'call')
            ->next(new PrepareChatCompletionsResultNode, 'wrap-up');

        $shared = [
            'available_parameters' => $this->toArray()
        ];

        return flow($work_nodes, $shared);
    }

    public static function test(): OllamaCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(OllamaRole::SYSTEM, 'You are an astrophysicist. You don\'t have time for my small talk. Keep your answers to less than 20 words')
            ->addText(OllamaRole::USER, '')
            ->addText(OllamaRole::ASSISTANT, 'Yes?')
            ->addText(OllamaRole::USER, 'What is the sky blue?')
            ->render();

        return Ollama::chat_completions()
            ->withModel('llama3.2:latest')
            ->withMessages($convo)
            ->handle();
    }

    public static function test2(): OllamaCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(OllamaRole::SYSTEM, 'You love to use tools.')
            ->addText(OllamaRole::USER, '')
            ->addText(OllamaRole::ASSISTANT, 'Hi!?')
            ->addText(OllamaRole::USER, 'Say something funny with the echo tool.')
            ->render();

        $tools = [
            [
                'type' => 'function',
                'function' => [
                    "name" => "echo",
                    "description" => "Echoes back the request data for testing purposes",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "intended_output" => [
                                "type" => "string",
                                "description" => "The intended output of the echo.",
                            ],
                        ],
                        "required" => [
                            "intended_output",
                        ],
                    ]
                ],
            ]
        ];

        return Ollama::chat_completions()
            ->withModel('llama3.2:latest')
            ->withTools($tools)
            ->withMessages($convo)
            ->handle();
    }

    public static function test3(): OllamaCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(OllamaRole::SYSTEM, 'You love to use tools.')
            ->addText(OllamaRole::USER, '')
            ->addText(OllamaRole::ASSISTANT, 'Hi!?')
            ->addText(OllamaRole::USER, 'Say something funny with the echo tool.')
            ->addToolRequest('0', 'echo', ['intended_output' => "I told my wife she was drawing her eyebrows too high. She looked surprised."])
            ->addToolResult('echo',  ['output' => "I told my wife she was drawing her eyebrows too high. She looked surprised."])
            ->render();

        $tools = [
            [
                'type' => 'function',
                'function' => [
                    "name" => "echo",
                    "description" => "Echoes back the request data for testing purposes",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "intended_output" => [
                                "type" => "string",
                                "description" => "The intended output of the echo.",
                            ],
                        ],
                        "required" => [
                            "intended_output",
                        ],
                    ]
                ],
            ]
        ];

        return Ollama::chat_completions()
            ->withModel('llama3.2:latest')
            ->withTools($tools)
            ->withMessages($convo)
            ->handle();
    }
}
