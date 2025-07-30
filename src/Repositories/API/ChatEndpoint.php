<?php

namespace LLMSpeak\Ollama\Repositories\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ChatEndpoint
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Handle Ollama Chat Completions API request
     *
     * @param string $url - Full API URL (e.g., "http://localhost:11434/api/chat")
     * @param string $model - Model identifier (e.g., "llama3.2:latest")
     * @param array $messages - Conversation messages array (required)
     * @param string|null $apiKey - Optional API key for authentication
     * @param int|null $maxTokens - Maximum number of tokens to generate (maps to options.num_predict)
     * @param float|null $temperature - Sampling temperature between 0 and 2
     * @param array|null $tools - List of tools the model may call (JSON Schema functions)
     * @param string|array|null $toolChoice - Controls which tools are called
     * @param bool|null $stream - Enable streaming response
     * @param bool|null $think - Enable thinking mode for reasoning models
     * @param array|null $responseFormat - Format of the response (supports JSON mode and structured outputs)
     * @param bool|null $parallelFunctionCalling - Enable/disable parallel function calling
     * @param string|null $keepAlive - Controls how long model stays loaded (e.g., "5m", "1h", 0 to unload)
     * @param array|null $options - Advanced model parameters (seed, top_k, top_p, etc.)
     * @param string|null $system - System prompt (alternative to system message in messages array)
     * @param bool|null $raw - Disable prompt templating (use raw prompt)
     * @param string|null $template - Custom prompt template (overrides model default)
     * @param array|null $context - Conversation context for memory (deprecated but supported)
     *
     * @return array - Complete response with status_code, headers, and body
     * @throws GuzzleException
     */
    public function handle(
        string $url,
        string $model,
        array $messages,
        ?string $apiKey = null,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?array $tools = null,
        string|array|null $toolChoice = null,
        ?bool $stream = null,
        ?bool $think = null,
        ?array $responseFormat = null,
        ?bool $parallelFunctionCalling = null,
        string|null $keepAlive = null,
        ?array $options = null,
        ?string $system = null,
        ?bool $raw = null,
        ?string $template = null,
        ?array $context = null
    ): array {
        // Build the payload with required parameters
        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        // Add core optional parameters
        if ($stream !== null) {
            $payload['stream'] = $stream;
        }

        if ($think !== null) {
            $payload['think'] = $think;
        }

        if ($tools !== null) {
            $payload['tools'] = $tools;
        }

        if ($toolChoice !== null) {
            $payload['tool_choice'] = $toolChoice;
        }

        if ($responseFormat !== null) {
            $payload['format'] = $responseFormat;
        }

        if ($parallelFunctionCalling !== null) {
            $payload['parallel_function_calling'] = $parallelFunctionCalling;
        }

        if ($keepAlive !== null) {
            $payload['keep_alive'] = $keepAlive;
        }

        if ($system !== null) {
            $payload['system'] = $system;
        }

        if ($raw !== null) {
            $payload['raw'] = $raw;
        }

        if ($template !== null) {
            $payload['template'] = $template;
        }

        if ($context !== null) {
            $payload['context'] = $context;
        }

        // Build options object for advanced model parameters
        $optionsPayload = [];

        // Map common parameters to options
        if ($maxTokens !== null) {
            $optionsPayload['num_predict'] = $maxTokens;
        }

        if ($temperature !== null) {
            $optionsPayload['temperature'] = $temperature;
        }

        // Add any additional options passed in
        if ($options !== null) {
            $optionsPayload = array_merge($optionsPayload, $options);
        }

        // Only add options if we have any
        if (!empty($optionsPayload)) {
            $payload['options'] = $optionsPayload;
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

        // Handle streaming vs non-streaming responses
        $decodedBody = $this->parseResponseBody($body, $stream ?? false);

        // Return complete response data
        return [
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $decodedBody,
        ];
    }

    /**
     * Parse response body handling both streaming and non-streaming formats
     *
     * @param string $body Raw response body
     * @param bool $isStreaming Whether this is a streaming response
     * @return array|null Parsed response data
     */
    private function parseResponseBody(string $body, bool $isStreaming): ?array
    {
        // For non-streaming, try to decode as single JSON object
        if (!$isStreaming) {
            $decodedBody = json_decode($body, true);
            if ($decodedBody !== null) {
                return $decodedBody;
            }
        }

        // For streaming or failed JSON decode, handle as streaming format
        $chunks = array_filter(array_map('trim', explode("\n", $body)));
        $parsedChunks = [];
        $lastValidChunk = null;

        foreach ($chunks as $chunk) {
            if (empty($chunk)) continue;
            
            $parsedChunk = json_decode($chunk, true);
            if ($parsedChunk !== null) {
                $parsedChunks[] = $parsedChunk;
                $lastValidChunk = $parsedChunk;
            }
        }

        // If streaming, return structured data
        if ($isStreaming) {
            return [
                'streaming' => true,
                'chunks' => $parsedChunks,
                'raw_body' => $body,
                'final_chunk' => $lastValidChunk
            ];
        }

        // If not streaming but we parsed chunks, return the last valid chunk
        return $lastValidChunk;
    }
}
