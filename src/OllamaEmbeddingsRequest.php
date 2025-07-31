<?php

namespace LLMSpeak\Ollama;

use Spatie\LaravelData\Data;
use LLMSpeak\Ollama\Repositories\API\EmbedEndpoint;
use GuzzleHttp\Exception\GuzzleException;

/**
 * OllamaEmbeddingsRequest - Ollama Embeddings API Request Builder
 *
 * Usage Examples:
 *
 * // Traditional setters
 * $request = new OllamaEmbeddingsRequest('nomic-embed-text', 'Why is the sky blue?')
 *     ->setTruncate(true)
 *     ->setOptions(['temperature' => 0.7])
 *     ->setKeepAlive('10m');
 *
 * // Generic set() method
 * $request = $request->set('truncate', false)->set('keepAlive', '5m');
 *
 * // Batch setting
 * $request = $request->setMultiple([
 *     'truncate' => true,
 *     'options' => ['temperature' => 0.8],
 *     'keepAlive' => '15m',
 *     'encodingFormat' => 'float'
 * ]);
 *
 * // Magic methods (camelCase gets converted to snake_case)
 * $request = $request->setTruncate(false)->setKeepAlive('10m')->setEncodingFormat('base64');
 *
 * // Convert to EmbedEndpoint parameters
 * $params = $request->toArray();
 * $response = (new EmbedEndpoint())->handle(...$params);
 *
 * // Or make direct API call
 * $response = $request->post(); // Returns OllamaEmbeddingsResponse
 */
class OllamaEmbeddingsRequest extends Data
{
    protected string $url;
    protected ?string $api_key;

    public function __construct(
        public readonly string $model,
        public readonly string|array $input,
        public readonly ?string $encodingFormat = null,
        public readonly ?bool $truncate = null,
        public readonly ?array $options = null,
        public readonly ?string $keepAlive = null
    )
    {
        $this->api_key = env('OLLAMA_API_KEY', null);
        $this->url = rtrim(config('llms.embeddings-providers.drivers.ollama.base_url'), '/') . '/embed';
    }

    /**
     * Generic method to set any property and return a new instance
     */
    public function set(string $property, mixed $value): self
    {
        $currentData = [
            'model' => $this->model,
            'input' => $this->input,
            'encodingFormat' => $this->encodingFormat,
            'truncate' => $this->truncate,
            'options' => $this->options,
            'keepAlive' => $this->keepAlive,
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
     * Usage: $request->setTruncate(false) or $request->setKeepAlive('10m')
     */
    public function __call(string $method, array $arguments): self
    {
        if (str_starts_with($method, 'set')) {
            $property = lcfirst(substr($method, 3));
            // Convert camelCase to snake_case for some properties
            if ($property === 'encodingFormat') {
                $property = 'encodingFormat';
            } elseif ($property === 'keepAlive') {
                $property = 'keepAlive';
            }
            return $this->set($property, $arguments[0] ?? null);
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }

    /**
     * Convenience methods using the generic set() method
     */
    public function setModel(string $model): self
    {
        return $this->set('model', $model);
    }

    public function setInput(string|array $input): self
    {
        return $this->set('input', $input);
    }

    public function setEncodingFormat(string $encodingFormat): self
    {
        return $this->set('encodingFormat', $encodingFormat);
    }

    public function setTruncate(bool $truncate): self
    {
        return $this->set('truncate', $truncate);
    }

    public function setOptions(array $options): self
    {
        return $this->set('options', $options);
    }

    public function setKeepAlive(string $keepAlive): self
    {
        return $this->set('keepAlive', $keepAlive);
    }

    public function addOption(string $key, mixed $value): self
    {
        $currentOptions = $this->options ?? [];
        $currentOptions[$key] = $value;
        return $this->set('options', $currentOptions);
    }

    public function setTemperature(float $temperature): self
    {
        return $this->addOption('temperature', $temperature);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getApiKey(): ?string
    {
        return $this->api_key;
    }

    /**
     * Convert to array in the exact shape expected by EmbedEndpoint->handle()
     * Parameters must match the handle function signature exactly:
     * handle(string $url, string $model, string|array $input, ?string $apiKey, ?string $encodingFormat, ?bool $truncate, ?array $options, ?string $keepAlive)
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'model' => $this->model,
            'input' => $this->input,
            'apiKey' => $this->api_key,
            'encodingFormat' => $this->encodingFormat,
            'truncate' => $this->truncate,
            'options' => $this->options,
            'keepAlive' => $this->keepAlive,
        ];
    }

    public function post(): OllamaEmbeddingsResponse
    {
        try {
            // Get the parameters for the EmbedEndpoint
            $params = $this->toArray();

            // Make the API call using EmbedEndpoint
            $endpoint = new EmbedEndpoint();
            $rawResponse = $endpoint->handle(...$params);

            // Validate the response structure
            if (!isset($rawResponse['status_code']) || $rawResponse['status_code'] !== 200) {
                throw new \Exception(
                    'API call failed with status: ' . ($rawResponse['status_code'] ?? 'unknown') .
                    '. Error: ' . ($rawResponse['error'] ?? 'No error details provided')
                );
            }

            // Use the response body directly (Ollama embeddings don't support streaming)
            if (!isset($rawResponse['body']) || !is_array($rawResponse['body'])) {
                throw new \Exception('Invalid API response: missing or invalid body');
            }

            return OllamaEmbeddingsResponse::fromApiResponse($rawResponse['body']);

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

    /**
     * Validation helpers
     */
    public function isValidEncodingFormat(): bool
    {
        if ($this->encodingFormat === null) {
            return true;
        }
        return in_array($this->encodingFormat, ['float', 'base64']);
    }

    public function hasMultipleInputs(): bool
    {
        return is_array($this->input);
    }

    public function getInputCount(): int
    {
        return is_array($this->input) ? count($this->input) : 1;
    }
}
