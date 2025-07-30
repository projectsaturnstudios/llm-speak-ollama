<?php

namespace LLMSpeak\Ollama;

use Spatie\LaravelData\Data;
use LLMSpeak\Ollama\Repositories\API\ChatEndpoint;
use GuzzleHttp\Exception\GuzzleException;

/**
 * OllamaChatRequest - Ollama API Request Builder
 *
 * Usage Examples:
 *
 * // Traditional setters
 * $request = new OllamaChatRequest('llama3.2:latest', $messages)
 *     ->setTemperature(0.8)
 *     ->setMaxTokens(1000)
 *     ->setStream(true);
 *
 * // Generic set() method
 * $request = $request->set('temperature', 0.8)->set('max_tokens', 1000);
 *
 * // Batch setting
 * $request = $request->setMultiple([
 *     'temperature' => 0.8,
 *     'max_tokens' => 1000,
 *     'stream' => true,
 *     'keep_alive' => '5m'
 * ]);
 *
 * // Magic methods (camelCase gets converted to snake_case)
 * $request = $request->setTemperature(0.8)->setMaxTokens(1000)->setKeepAlive('5m');
 *
 * // Convert to ChatEndpoint parameters
 * $params = $request->toArray();
 * $response = (new ChatEndpoint())->handle(...$params);
 *
 * // Or make direct API call
 * $response = $request->post(); // Returns OllamaChatResponse
 */
class OllamaChatRequest extends Data
{
    protected string $url;
    protected ?string $api_key;

    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly ?int $max_tokens = null,
        public readonly ?float $temperature = null,
        public readonly ?array $tools = null,
        public readonly string|array|null $tool_choice = null,
        public readonly ?bool $stream = null,
        public readonly ?bool $think = null,
        public readonly ?array $response_format = null,
        public readonly ?bool $parallel_function_calling = null,
        public readonly string|null $keep_alive = null,
        public readonly ?array $options = null,
        public readonly ?string $system = null,
        public readonly ?bool $raw = null,
        public readonly ?string $template = null,
        public readonly ?array $context = null
    )
    {
        $this->api_key = env('OLLAMA_API_KEY', null);
        $this->url = rtrim(config('llms.providers.drivers.ollama.base_url'), '/') . '/chat';
    }

    /**
     * Generic method to set any property and return a new instance
     */
    public function set(string $property, mixed $value): self
    {
        $currentData = [
            'model' => $this->model,
            'messages' => $this->messages,
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature,
            'tools' => $this->tools,
            'tool_choice' => $this->tool_choice,
            'stream' => $this->stream,
            'think' => $this->think,
            'response_format' => $this->response_format,
            'parallel_function_calling' => $this->parallel_function_calling,
            'keep_alive' => $this->keep_alive,
            'options' => $this->options,
            'system' => $this->system,
            'raw' => $this->raw,
            'template' => $this->template,
            'context' => $this->context,
        ];

        $currentData[$property] = $value;

        return new self(...$currentData);
    }

    /**
     * Batch setter - set multiple properties at once
     */
    public function setMultiple(array $properties): self
    {
        $instance = $this;
        foreach ($properties as $property => $value) {
            $instance = $instance->set($property, $value);
        }
        return $instance;
    }

    /**
     * Magic method approach - could replace all setter methods
     * Usage: $request->setTemperature(0.8) or $request->setMaxTokens(100)
     */
    public function __call(string $method, array $arguments): self
    {
        if (str_starts_with($method, 'set')) {
            $property = strtolower(preg_replace('/([A-Z])/', '_$1', substr($method, 4)));
            return $this->set($property, $arguments[0] ?? null);
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }

    /**
     * Convenience methods using the generic set() method
     */
    public function setMaxTokens(int $max_tokens): self
    {
        return $this->set('max_tokens', $max_tokens);
    }

    public function setTemperature(float $temperature): self
    {
        return $this->set('temperature', $temperature);
    }

    public function setTools(array $tools): self
    {
        return $this->set('tools', $tools);
    }

    public function setToolChoice(string|array $tool_choice): self
    {
        return $this->set('tool_choice', $tool_choice);
    }

    public function setAutoToolChoice(): self
    {
        return $this->set('tool_choice', 'auto');
    }

    public function setRequiredToolChoice(): self
    {
        return $this->set('tool_choice', 'required');
    }

    public function setNoneToolChoice(): self
    {
        return $this->set('tool_choice', 'none');
    }

    public function setStream(bool $stream): self
    {
        return $this->set('stream', $stream);
    }

    public function setThink(bool $think): self
    {
        return $this->set('think', $think);
    }

    public function setResponseFormat(array $response_format): self
    {
        return $this->set('response_format', $response_format);
    }

    public function setJsonMode(): self
    {
        return $this->set('response_format', ['type' => 'json_object']);
    }

    public function setParallelFunctionCalling(bool $parallel_function_calling): self
    {
        return $this->set('parallel_function_calling', $parallel_function_calling);
    }

    public function setKeepAlive(string $keep_alive): self
    {
        return $this->set('keep_alive', $keep_alive);
    }

    public function setOptions(array $options): self
    {
        return $this->set('options', $options);
    }

    public function setAdvancedOptions(
        ?int $seed = null,
        ?int $top_k = null,
        ?float $top_p = null,
        ?float $repeat_penalty = null,
        ?int $repeat_last_n = null,
        ?float $tfs_z = null,
        ?float $typical_p = null,
        ?int $mirostat = null,
        ?float $mirostat_tau = null,
        ?float $mirostat_eta = null
    ): self {
        $options = array_filter([
            'seed' => $seed,
            'top_k' => $top_k,
            'top_p' => $top_p,
            'repeat_penalty' => $repeat_penalty,
            'repeat_last_n' => $repeat_last_n,
            'tfs_z' => $tfs_z,
            'typical_p' => $typical_p,
            'mirostat' => $mirostat,
            'mirostat_tau' => $mirostat_tau,
            'mirostat_eta' => $mirostat_eta,
        ], fn($value) => $value !== null);

        return $this->set('options', $options);
    }

    public function setSystemPrompt(string $system): self
    {
        return $this->set('system', $system);
    }

    public function setRaw(bool $raw): self
    {
        return $this->set('raw', $raw);
    }

    public function setTemplate(string $template): self
    {
        return $this->set('template', $template);
    }

    public function setContext(array $context): self
    {
        return $this->set('context', $context);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'model' => $this->model,
            'messages' => $this->messages,
            'apiKey' => $this->api_key,
            'maxTokens' => $this->max_tokens,
            'temperature' => $this->temperature,
            'tools' => $this->tools,
            'toolChoice' => $this->tool_choice,
            'stream' => $this->stream,
            'think' => $this->think,
            'responseFormat' => $this->response_format,
            'parallelFunctionCalling' => $this->parallel_function_calling,
            'keepAlive' => $this->keep_alive,
            'options' => $this->options,
            'system' => $this->system,
            'raw' => $this->raw,
            'template' => $this->template,
            'context' => $this->context,
        ];
    }

    public function post(): OllamaChatResponse
    {
        try {
            // Get the parameters for the ChatEndpoint
            $params = $this->toArray();

            // Make the API call using ChatEndpoint
            $endpoint = new ChatEndpoint();
            $rawResponse = $endpoint->handle(...$params);

            // Validate the response structure
            if (!isset($rawResponse['status_code']) || $rawResponse['status_code'] !== 200) {
                throw new \Exception(
                    'API call failed with status: ' . ($rawResponse['status_code'] ?? 'unknown') .
                    '. Error: ' . ($rawResponse['error'] ?? 'No error details provided')
                );
            }

            // Create and return OllamaChatResponse
            return OllamaChatResponse::fromApiResponse($rawResponse);

        } catch (GuzzleException $e) {
            throw new \Exception('HTTP request failed: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            // Re-throw our own exceptions, wrap any others
            if (str_starts_with($e->getMessage(), 'API call failed') ||
                str_starts_with($e->getMessage(), 'Invalid API response')) {
                throw $e;
            }
            throw new \Exception('Unexpected error during API call: ' . $e->getMessage(), 0, $e);
        }
    }
}
