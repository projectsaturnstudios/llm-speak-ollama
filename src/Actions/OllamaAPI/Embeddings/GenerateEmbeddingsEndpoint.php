<?php

namespace LLMSpeak\Ollama\Actions\OllamaAPI\Embeddings;

use LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint\OllamaCompletionsEndpointNode;
use LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint\PrepareChatCompletionsRequestNode;
use LLMSpeak\Ollama\Actions\Sagas\ChatCompletions\ChatCompletionsEndpoint\PrepareChatCompletionsResultNode;
use LLMSpeak\Ollama\Actions\Sagas\Embeddings\GenerateEmbeddingsEndpoint\OllamaEmbeddingsEndpointNode;
use LLMSpeak\Ollama\Actions\Sagas\Embeddings\GenerateEmbeddingsEndpoint\PrepareEmbeddingsRequestNode;
use LLMSpeak\Ollama\Actions\Sagas\Embeddings\GenerateEmbeddingsEndpoint\PrepareEmbeddingsResultNode;
use LLMSpeak\Ollama\Builders\ConversationBuilder;
use LLMSpeak\Ollama\Builders\EmbeddingQueryBuilder;
use LLMSpeak\Ollama\Enums\OllamaRole;
use LLMSpeak\Ollama\OllamaCallResult;
use LLMSpeak\Ollama\OllamaEmbeddingResult;
use LLMSpeak\Ollama\Support\Facades\Ollama;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\Data;

class GenerateEmbeddingsEndpoint extends Data
{
    use AsAction;

    protected string $uri = 'embed';

    public function __construct(
        public readonly string $url,
        public readonly string $model,
        public readonly string|array $input,
    ) {}

    public function handle(): OllamaEmbeddingResult
    {
        $work_nodes = new PrepareEmbeddingsRequestNode;
        $work_nodes->next(new OllamaEmbeddingsEndpointNode("{$this->url}{$this->uri}"), 'call')
            ->next(new PrepareEmbeddingsResultNode, 'wrap-up');

        $shared = [
            'available_parameters' => $this->toArray()
        ];

        return flow($work_nodes, $shared);
    }

    public static function test(): OllamaEmbeddingResult
    {
        $convo = (new EmbeddingQueryBuilder())
            ->addQuery("What happens if I get pulled over for speeding?")
            ->render();

        return Ollama::embeddings()
            ->withModel('hf.co/mariadjadi/fine_tuned_mistral_legal_V2_merged-GGUF:latest')
            ->withInput($convo)
            ->handle();
    }

    public static function test2(): OllamaEmbeddingResult
    {
        $convo = (new EmbeddingQueryBuilder())
            ->addQuery("What happens if I get pulled over for speeding?")
            ->addQuery("If I go to jail do I get my one phone call?")
            ->render();

        return Ollama::embeddings()
            ->withModel('hf.co/mariadjadi/fine_tuned_mistral_legal_V2_merged-GGUF:latest')
            ->withInput($convo)
            ->handle();
    }
}
