<?php

namespace LLMSpeak\Ollama\Drivers\Interaction;

use LLMSpeak\Core\DTO\Primitives\ToolCallObject;
use LLMSpeak\Core\DTO\Schema\Completions\ToolResultMessage;
use LLMSpeak\Core\Enums\ConversationRole;
use LLMSpeak\Core\DTO\Primitives\TextObject;
use LLMSpeak\Core\NeuralModels\EmbeddingsModel;
use LLMSpeak\Core\Collections\ChatConversation;
use LLMSpeak\Core\NeuralModels\CompletionsModel;
use LLMSpeak\Core\DTO\Schema\Completions\ContentMessage;
use LLMSpeak\Core\DTO\Schema\Completions\ToolCallsMessage;
use LLMSpeak\Core\Drivers\Interaction\ModelCompletionsDriver;
use LLMSpeak\Core\DTO\Schema\Completions\TextOnlyContentMessage;

class OllamaCompletionsDriver extends ModelCompletionsDriver
{
    protected string $driver_name = 'ollama';
    protected ?array $req = null;

    /**
     * @param array<string> $input
     * @param EmbeddingsModel $neural_model
     * @return array
     */
    protected function generateRequestBody(array|string $input, CompletionsModel $neural_model): array
    {
        return array_map(function(ChatConversation|array $conversation) use($neural_model) {
            $results = [
                'model' => $neural_model->modelId(),
                'messages' => array_map(function(ContentMessage|array $message) {
                    if(is_array($message))
                    {
                        $all_strings = true;
                        foreach($message as $msg)
                        {
                            if(!($msg instanceof TextOnlyContentMessage))
                            {
                                $all_strings = false;
                                break;
                            }
                        }

                        if($all_strings)
                        {
                            // if all messages are strings, this is a user message
                            $message = implode("\n", array_map(fn(TextOnlyContentMessage $msg) => $msg->content->toValue(), $message));
                            return  (new TextOnlyContentMessage(ConversationRole::USER, new TextObject($message)))->toArray();
                        }
                        else
                        {
                            dd("Support multi modal messages!", $message);
                        }

                    }
                    else
                    {
                        switch($message_class = $message::class)
                        {
                            case TextOnlyContentMessage::class:
                                /** @var TextOnlyContentMessage $message */
                                return $message->toArray();

                            case ToolCallsMessage::class:
                                /** @var ToolCallsMessage $message */
                                return [
                                    'role' => 'assistant',
                                    'tool_calls' => array_map(fn(ToolCallObject $message) => [
                                        'function' => [
                                            'name' => $message->name,
                                            'arguments' => empty($message->args) ? new \stdClass() : $message->args
                                        ]
                                    ], $message->tool_calls)
                                ];

                            case ToolResultMessage::class:
                                /** @var ToolResultMessage $message */
                                return[
                                    'role' => 'tool',
                                    'content' => $message->content,
                                ];
                                break;

                            default:
                                dd("Support this kind of message", $message, $message_class);
                        }
                    }
                }, $conversation->all()),
                'stream' => $neural_model->willStreamResponse(),


                'keep_alive' => '30m'
            ];

            if(!empty($neural_model->getSystemInstructions()))
            {
                $system_messages = array_map(fn(string $instruction) => [
                    'role' => ConversationRole::SYSTEM->value,
                    'content' => (new TextObject($instruction))->toValue()
                ], $neural_model->getSystemInstructions());
                $results['messages'] = array_merge($system_messages, $results['messages']);
            }
            if(!empty($neural_model->getTools()))
            {
                //dd($neural_model->getTools());
                $results['tools'] = array_map(fn(array $tool_definition) => [
                    'type' => 'function',
                    'function' => [
                        'name' => $tool_definition['name'],
                        'description' => $tool_definition['description'],
                        'parameters' => array_map(function(array $schema) use($tool_definition) {
                            if(empty($schema['properties'])) $schema['properties'] = new \stdClass();
                            return $schema;
                        }, [$tool_definition['inputSchema']])[0],
                    ]
                ], $neural_model->getTools());
            }

            $options = [];
            if($neural_model->maxTokens()) $options['num_ctx'] = $neural_model->maxTokens();
            if($neural_model->temperature()) $options['temperature'] = $neural_model->temperature();
            if($neural_model->topP()) $options['top_p'] = $neural_model->topP();
            if($neural_model->seed()) $options['seed'] = $neural_model->seed();

            $original = $neural_model->getOriginal();
            // @todo - apply non-standard options from original
            if(!empty($options)) $results['options'] = $options;

            $this->req = $results;
            return $results;
        }, $input);
    }

    protected function generateCompletion(array $output): array
    {
        $results = [
            'id'  => new_uuid4(),
            'usage' => [
                'total_duration' => $output['total_duration'],
                'load_duration' => $output['load_duration'],
                'prompt_eval_count' => $output['prompt_eval_count'],
                'prompt_eval_duration' => $output['prompt_eval_duration'],
                'eval_count' => $output['eval_count'],
                'eval_duration' => $output['eval_duration'],
            ],
            'created_at' => strtotime($output['created_at']),
            'metadata' => [
                'model' => $output['model'] ?? null
            ],
            'messages' => array_map(function(array $choice) use($output){
                if(isset($choice['tool_calls']))
                {
                    $metadata = [
                        'done' => $output['done'],
                        'done_reason' => $output['done_reason'] ?? null,
                    ];
                    return (new ToolCallsMessage(
                        array_map(fn(array $tool_call) => new ToolCallObject(
                            $tool_call['function']['name'],
                            $tool_call['function']['arguments'],
                            $tool_call['function']['name'],
                        ), $choice['tool_calls']),
                        $choice['content'] ?? null ? new TextObject($choice['content']) : null,
                        $metadata
                    ));
                }
                elseif(isset($choice['content']))
                {
                    $metadata = [
                        'done' => $output['done'],
                        'done_reason' => $output['done_reason'] ?? null,
                    ];
                    return (new TextOnlyContentMessage(
                        ConversationRole::ASSISTANT,
                        new TextObject($choice['content']),
                        $metadata
                    ));
                }
                else
                {
                    dd("Support this kind of response choice", $choice);
                }
            }, [$output['message']]),
        ];

        return array_values($results);
    }
}
