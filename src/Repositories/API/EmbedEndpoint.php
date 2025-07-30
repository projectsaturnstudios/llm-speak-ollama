<?php

namespace LLMSpeak\Ollama\Repositories\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EmbedEndpoint
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Handle Ollama Embeddings API request
     * 
     * @param string $url - Full API URL (e.g., "http://localhost:11434/api/embed")
     * @param string $model - Model identifier (e.g., "nomic-embed-text")
     * @param string|array $input - Input text to embed (required) - can be string or array of strings
     * @param string|null $apiKey - Optional API key for authentication
     * @param string|null $encodingFormat - Format to return embeddings ("float" or "base64")
     * @param bool|null $truncate - Truncates input to fit context length (default: true)
     * @param array|null $options - Additional model parameters (e.g., temperature)
     * @param string|null $keepAlive - How long to keep model loaded (default: "5m")
     * 
     * @return array - Complete response with status_code, headers, and body
     * @throws GuzzleException
     */
    public function handle(
        string $url,
        string $model,
        string|array $input,
        ?string $apiKey = null,
        ?string $encodingFormat = null,
        ?bool $truncate = null,
        ?array $options = null,
        ?string $keepAlive = null
    ): array {
        // Build the payload with required parameters
        $payload = [
            'model' => $model,
            'input' => $input,
        ];

        // Add optional parameters
        if ($encodingFormat !== null) {
            $payload['encoding_format'] = $encodingFormat;
        }

        if ($truncate !== null) {
            $payload['truncate'] = $truncate;
        }

        if ($options !== null && !empty($options)) {
            $payload['options'] = $options;
        }

        if ($keepAlive !== null) {
            $payload['keep_alive'] = $keepAlive;
        }

        // Build headers (authentication is optional for local Ollama)
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($apiKey !== null) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        // Make the request
        $response = $this->client->post($url, [
            'headers' => $headers,
            'json' => $payload,
        ]);

        // Get response body
        $body = $response->getBody()->getContents();

        // Return complete response data
        return [
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => json_decode($body, true),
        ];
    }
}
