<?php

namespace LLMSpeak\Ollama\Drivers\Interaction;

use LLMSpeak\Core\NeuralModels\EmbeddingsModel;
use LLMSpeak\Core\DTO\Primitives\VectorEmbedding;
use LLMSpeak\Core\Drivers\Interaction\ModelEmbeddingsDriver;

class OllamaEmbeddingsDriver extends ModelEmbeddingsDriver
{
    protected string $driver_name = 'ollama';

     /**
     * @param array<string> $input
     * @param EmbeddingsModel $neural_model
     * @return array
     */
    protected function generateRequestBody(array $input, EmbeddingsModel $neural_model): array
    {
        return array_map(fn(string $text) => [
            'input' => $text,
            'model' => $neural_model->modelId(),
        ], $input);
    }

    protected function generateEmbeddings(array $output): array
    {
        $results = [
            'usage' => [
                'total_duration' => $output['total_duration'],
                'load_duration' => $output['load_duration'],
                'prompt_eval_count' => $output['prompt_eval_count'],
            ],
            'vectorized' => array_map(fn(array $embedding) => new VectorEmbedding($embedding), $output['embeddings'])
        ];

        return array_values($results);
    }
}
