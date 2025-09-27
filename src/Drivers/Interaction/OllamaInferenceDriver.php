<?php

namespace LLMSpeak\Ollama\Drivers\Interaction;

use LLMSpeak\Core\DTO\Primitives\TextObject;
use LLMSpeak\Core\NeuralModels\InferenceModel;
use LLMSpeak\Core\NeuralModels\EmbeddingsModel;
use LLMSpeak\Core\Drivers\Interaction\ModelInferenceDriver;

class OllamaInferenceDriver extends ModelInferenceDriver
{
    protected string $driver_name = 'ollama';

    /**
     * @param array<string> $input
     * @param EmbeddingsModel $neural_model
     * @return array
     */
    protected function generateRequestBody(array|string $input, InferenceModel $neural_model): array
    {
        return array_map(function(string|array $text) use($neural_model) {
            $results = [
                'model' => $neural_model->modelId(),
                'prompt' => is_array($text) ? implode("\n", $text) : $text,
                'stream' => $neural_model->willStreamResponse(),

                'keep_alive' => '30m'
            ];
            $options = [];
            if($neural_model->maxTokens()) $options['num_ctx'] = $neural_model->maxTokens();
            if($neural_model->temperature()) $options['temperature'] = $neural_model->temperature();
            if($neural_model->topP()) $options['top_p'] = $neural_model->topP();
            if($neural_model->seed()) $options['seed'] = $neural_model->seed();

            $original = $neural_model->getOriginal();
            // @todo - apply non-standard options from original
            if(!empty($options)) $results['options'] = $options;
            return $results;
        }, $input);
    }

    protected function generateInference(array $output): array
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
                'model' => $output['model'] ?? null,
                'context' => $output['context'] ?? null,
            ],
            'messages' => [
                new TextObject(...[
                    'text' => $output['response'],
                    'metadata' => [
                        'done' => $output['done'],
                        'done_reason' => $output['done_reason'] ?? null,
                    ]
                ])
            ]
        ];

        return array_values($results);
    }
}
