# LLMSpeak Ollama

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/releases/)
[![Laravel](https://img.shields.io/badge/Laravel-10.x%7C11.x%7C12.x-red.svg)](https://laravel.com)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/llm-speak/ollama.svg?style=flat-square)](https://packagist.org/packages/llm-speak/ollama)
[![Total Downloads](https://img.shields.io/packagist/dt/llm-speak/ollama.svg?style=flat-square)](https://packagist.org/packages/llm-speak/ollama)

**LLMSpeak Ollama** is a Laravel package that provides a fluent, Laravel-native interface for integrating with Ollama, the popular local AI model runner. Built as part of the LLMSpeak ecosystem, it offers seamless access to locally hosted models including Llama, Mistral, CodeLlama, Gemma, and hundreds of other open-source models running on your own hardware.

> **Note:** This package is part of the larger [LLMSpeak ecosystem](https://github.com/projectsaturnstudios/llm-speak). For universal provider switching and standardized interfaces, check out the [LLMSpeak Core](https://github.com/projectsaturnstudios/llm-speak-core) package.

## Table of Contents
- [Features](#features)
- [Get Started](#get-started)
- [Usage](#usage)
  - [Chat Completions](#chat-completions)
  - [Model Management](#model-management)
  - [Embeddings](#embeddings)
  - [Fluent Request Building](#fluent-request-building)
  - [Tool Calling](#tool-calling)
  - [Think Mode](#think-mode)
  - [Advanced Options](#advanced-options)
  - [Raw Mode & Templates](#raw-mode--templates)
  - [Streaming Responses](#streaming-responses)
  - [Performance Monitoring](#performance-monitoring)
- [Response Handling](#response-handling)
- [Testing](#testing)
- [Credits](#credits)
- [License](#license)

## Features

- **🏠 Local AI**: Run models locally on your own hardware with complete privacy
- **🚀 Laravel Native**: Full Laravel integration with automatic service discovery
- **🔧 Fluent Interface**: Expressive request builders with method chaining
- **📊 Laravel Data**: Powered by Spatie Laravel Data for robust data validation
- **🛠️ Tool Support**: Complete function calling capabilities
- **🧠 Think Mode**: Enhanced reasoning capabilities for supported models
- **⚡ Model Lifecycle**: Control model loading and unloading for optimal resource usage
- **📝 Embeddings**: Local embedding generation with truncation support
- **🎛️ Advanced Control**: Fine-grained model parameter control with options
- **🔧 Raw Mode**: Disable prompt templating for maximum control
- **📈 Performance Metrics**: Detailed timing and token usage analytics
- **💨 Streaming**: Real-time streaming responses
- **🎯 Type Safety**: Full PHP 8.2+ type declarations and IDE support
- **🔐 Privacy**: No data leaves your machine - complete privacy and control

## Get Started

> **Requires [PHP 8.2+](https://php.net/releases/), Laravel 10.x/11.x/12.x, and [Ollama](https://ollama.ai/) installed**

### Install Ollama

First, install Ollama on your system:

```bash
# macOS
brew install ollama

# Linux
curl -fsSL https://ollama.ai/install.sh | sh

# Windows - Download from https://ollama.ai/download
```

Start Ollama and pull a model:

```bash
# Start Ollama (runs on http://localhost:11434)
ollama serve

# Pull a model (in another terminal)
ollama pull llama3.2:latest
```

### Install the Package

Install the package via [Composer](https://getcomposer.org/):

```bash
composer require llm-speak/ollama
```

The package will automatically register itself via Laravel's package discovery.

### Environment Configuration

Optionally add configuration to your `.env` file:

```env
# Optional - Ollama runs locally by default
OLLAMA_API_KEY=your_optional_api_key_here

# Optional - Change default Ollama URL
OLLAMA_BASE_URL=http://localhost:11434/api/
```

> **Note:** API key is optional since Ollama typically runs locally without authentication.

## Usage

### Chat Completions

The simplest way to chat with locally hosted models:

```php
use LLMSpeak\Ollama\OllamaChatRequest;

$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: [
        ['role' => 'user', 'content' => 'Explain quantum computing in simple terms']
    ]
);

$response = $request->post();

echo $response->getTextContent(); // "Quantum computing is..."
```

### Model Management

Control how long models stay loaded in memory:

```php
// Keep model loaded for 5 minutes after last use
$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: $messages
)->setKeepAlive('5m');

// Keep model loaded for 1 hour
$request = $request->setKeepAlive('1h');

// Keep model loaded indefinitely
$request = $request->setKeepAlive('-1');

// Unload model immediately after use
$request = $request->setKeepAlive('0');

$response = $request->post();

// Check model loading performance
$loadTime = $response->load_duration;
$evalTime = $response->eval_duration;
echo "Model loaded in: " . ($loadTime / 1000000) . "ms";
```

### Available Models

Use any model available in Ollama:

```php
// Large reasoning models
$request = new OllamaChatRequest(model: 'llama3.2:70b', messages: $messages);

// Fast, efficient models
$request = new OllamaChatRequest(model: 'llama3.2:3b', messages: $messages);

// Code-specialized models
$request = new OllamaChatRequest(model: 'codellama:latest', messages: $messages);

// Multilingual models
$request = new OllamaChatRequest(model: 'mistral:latest', messages: $messages);

// Vision models
$request = new OllamaChatRequest(model: 'llava:latest', messages: $messages);

// Embedding models
$embeddingRequest = new OllamaEmbeddingsRequest(model: 'nomic-embed-text', input: $text);
```

### Embeddings

Generate embeddings locally with advanced control:

```php
use LLMSpeak\Ollama\OllamaEmbeddingsRequest;

// Simple text embedding
$request = new OllamaEmbeddingsRequest(
    model: 'nomic-embed-text',
    input: 'Generate embeddings for this text'
);

$response = $request->post();

$embeddings = $response->getEmbeddings();
$dimensions = $response->getDimensions();
```

### Advanced Embedding Configuration

Control truncation and encoding:

```php
// High-precision embeddings with truncation
$request = new OllamaEmbeddingsRequest(
    model: 'nomic-embed-text',
    input: 'Long text that might exceed model context'
)
->setTruncate(true)           // Automatically truncate if too long
->setEncodingFormat('float')   // High precision
->setKeepAlive('10m')         // Keep embedding model loaded
->setOptions([
    'temperature' => 0.0,      // Deterministic embeddings
]);

// Base64 encoded embeddings
$request = new OllamaEmbeddingsRequest(
    model: 'nomic-embed-text',
    input: ['Document 1', 'Document 2', 'Document 3']
)
->setEncodingFormat('base64')  // Compact format
->setTruncate(false);          // Fail if input too long

$response = $request->post();

$embeddings = $response->getEmbeddings();
$tokenCount = $response->getPromptTokens();
```

### Universal LLMSpeak Interface

For **provider-agnostic embeddings** that work across Ollama, Gemini, Mistral, and other providers, use the universal LLMSpeak interface:

```php
use LLMSpeak\Core\Support\Facades\LLMSpeak;
use LLMSpeak\Core\Support\Requests\LLMSpeakEmbeddingsRequest;

// Universal request works with ANY provider
$request = new LLMSpeakEmbeddingsRequest(
    model: 'nomic-embed-text',
    input: 'Generate embeddings for this text',
    encoding_format: 'float',    // 'float' or 'base64'
    dimensions: null,            // Use model default
    task_type: null              // Not applicable for Ollama
);

// Execute with Ollama - same code works with other providers!
$response = LLMSpeak::embeddingsFrom('ollama', $request);

// Universal response methods
$embeddings = $response->getAllEmbeddings();
$firstVector = $response->getFirstEmbedding();
$dimensions = $response->getDimensions();
$tokenUsage = $response->getTotalTokens();
$performanceData = $response->metadata; // Ollama-specific performance metrics
```

### Local Model Flexibility

The universal interface works seamlessly with any local Ollama model:

```php
// Different embedding models with same interface
$models = [
    'nomic-embed-text',           // General-purpose embeddings
    'mxbai-embed-large',          // Large model embeddings
    'snowflake-arctic-embed',     // Specialized embeddings
    'paraphrase-multilingual'     // Multilingual embeddings
];

foreach ($models as $model) {
    $request = new LLMSpeakEmbeddingsRequest(
        model: $model,
        input: 'Test embedding generation',
        encoding_format: 'float',
        dimensions: null,
        task_type: null
    );
    
    try {
        $response = LLMSpeak::embeddingsFrom('ollama', $request);
        echo "Model {$model}: {$response->getDimensions()} dimensions\n";
        
        // Access Ollama-specific performance data
        if (isset($response->metadata['total_duration_ms'])) {
            echo "Generation time: {$response->metadata['total_duration_ms']}ms\n";
        }
    } catch (\Exception $e) {
        echo "Model {$model} not available: {$e->getMessage()}\n";
    }
}
```

### Universal Batch Processing

Process multiple texts efficiently with the universal interface:

```php
// Batch embedding generation
$batchRequest = new LLMSpeakEmbeddingsRequest(
    model: 'nomic-embed-text',
    input: [
        'Artificial intelligence is transforming industries',
        'Machine learning enables automated decision making',
        'Natural language processing understands human text',
        'Computer vision interprets visual information',
        'Deep learning uses neural networks for complex patterns'
    ],
    encoding_format: 'float',
    dimensions: null,
    task_type: null
);

$batchResponse = LLMSpeak::embeddingsFrom('ollama', $batchRequest);

echo "Generated {$batchResponse->getEmbeddingCount()} embeddings";
echo "Vector dimensions: {$batchResponse->getDimensions()}";

// Access performance metrics for the entire batch
if (isset($batchResponse->metadata['total_duration_ms'])) {
    $totalTime = $batchResponse->metadata['total_duration_ms'];
    $avgTimePerEmbedding = $totalTime / $batchResponse->getEmbeddingCount();
    echo "Average time per embedding: {$avgTimePerEmbedding}ms";
}
```

### Advanced Universal Features

Leverage local processing benefits through the universal interface:

```php
// High-precision embeddings for similarity search
$precisionRequest = new LLMSpeakEmbeddingsRequest(
    model: 'nomic-embed-text',
    input: 'Document for semantic similarity search',
    encoding_format: 'float',    // High precision for accurate similarity
    dimensions: null,
    task_type: null
);

$precisionResponse = LLMSpeak::embeddingsFrom('ollama', $precisionRequest);

// Compact embeddings for storage efficiency
$compactRequest = new LLMSpeakEmbeddingsRequest(
    model: 'nomic-embed-text',
    input: 'Document for efficient storage',
    encoding_format: 'base64',   // More compact format
    dimensions: null,
    task_type: null
);

$compactResponse = LLMSpeak::embeddingsFrom('ollama', $compactRequest);

// Compare embedding formats
echo "Float embeddings: " . count($precisionResponse->getFirstEmbedding()) . " dimensions\n";
echo "Base64 embeddings: " . count($compactResponse->getFirstEmbedding()) . " dimensions\n";
```

### Privacy & Performance Benefits

Ollama's local processing combined with universal interface provides unique advantages:

```php
// Process sensitive data locally with universal interface
$sensitiveRequest = new LLMSpeakEmbeddingsRequest(
    model: 'nomic-embed-text',
    input: 'Confidential business document content',
    encoding_format: 'float',
    dimensions: null,
    task_type: null
);

// Data never leaves your machine!
$sensitiveResponse = LLMSpeak::embeddingsFrom('ollama', $sensitiveRequest);

// Monitor local performance
$perfData = $sensitiveResponse->metadata;
echo "Local processing time: {$perfData['total_duration_ms']}ms\n";
echo "Model load time: {$perfData['load_duration_ms']}ms\n";
echo "Tokens processed: {$sensitiveResponse->getPromptTokens()}\n";
```

### Why Use Universal Interface?

**✅ Provider Independence:** Switch between Ollama, Gemini, Mistral with zero code changes  
**✅ Local Privacy:** Keep sensitive data on your hardware while using universal API  
**✅ Future Proof:** New providers automatically supported  
**✅ Consistent API:** Same methods across cloud and local providers  
**✅ Performance Monitoring:** Access to Ollama's detailed performance metrics  
**✅ Model Flexibility:** Use any local model with the same interface  

```php
// Same request works with local and cloud providers!
$request = new LLMSpeakEmbeddingsRequest(
    model: 'embedding-model',
    input: 'Universal text input',
    encoding_format: 'float',
    dimensions: null,
    task_type: null
);

$ollamaResponse = LLMSpeak::embeddingsFrom('ollama', $request);   // Local processing
$geminiResponse = LLMSpeak::embeddingsFrom('gemini', $request);   // Google AI  
$mistralResponse = LLMSpeak::embeddingsFrom('mistral', $request); // Mistral AI
```

### Fluent Request Building

Build complex requests using the fluent interface:

```php
use LLMSpeak\Ollama\OllamaChatRequest;

$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: [
        ['role' => 'user', 'content' => 'Write a creative story about AI']
    ]
)
->setTemperature(0.8)
->setMaxTokens(2000)
->setKeepAlive('15m')
->setSystem('You are a creative storyteller');

$response = $request->post();

// Access response properties and performance metrics
echo $response->model;                    // llama3.2:latest
echo $response->getTextContent();         // Generated story
echo $response->eval_count;               // Tokens generated
echo $response->total_duration;           // Total time in nanoseconds
```

### Tool Calling

Enable local models to use external functions:

```php
$tools = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'calculate_mortgage',
            'description' => 'Calculate monthly mortgage payment',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'principal' => [
                        'type' => 'number',
                        'description' => 'Loan amount in dollars'
                    ],
                    'rate' => [
                        'type' => 'number',
                        'description' => 'Annual interest rate (e.g., 0.045 for 4.5%)'
                    ],
                    'years' => [
                        'type' => 'integer',
                        'description' => 'Loan term in years'
                    ]
                ],
                'required' => ['principal', 'rate', 'years']
            ]
        ]
    ]
];

$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: [
        ['role' => 'user', 'content' => 'Calculate monthly payment for a $300,000 mortgage at 4.5% for 30 years']
    ]
)
->setTools($tools)
->setToolChoice('auto')
->setParallelFunctionCalling(true);

$response = $request->post();

// Check for tool usage
if ($response->usedTools()) {
    $toolCalls = $response->getToolCalls();
    foreach ($toolCalls as $call) {
        echo "Function: {$call['function']['name']}\n";
        echo "Arguments: " . json_encode($call['function']['arguments']) . "\n";
    }
}
```

### Think Mode

Enable enhanced reasoning for compatible models:

```php
$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: [
        [
            'role' => 'user', 
            'content' => 'Solve this step-by-step: If a train travels 120 miles in 2 hours, and another train travels 180 miles in 3 hours, which train is faster and by how much?'
        ]
    ]
)
->setThink(true)              // Enable thinking mode
->setKeepAlive('10m');

$response = $request->post();

$reasoning = $response->getThinkingContent();
$finalAnswer = $response->getTextContent();

echo "Reasoning process:\n" . $reasoning . "\n\n";
echo "Final answer:\n" . $finalAnswer;
```

### Advanced Options

Fine-tune model behavior with detailed parameters:

```php
$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: $messages
)
->setAdvancedOptions(
    seed: 42,                    // Deterministic output
    topK: 40,                    // Top-K sampling
    topP: 0.9,                   // Nucleus sampling
    temperature: 0.8,            // Creativity
    repeatPenalty: 1.1,          // Avoid repetition
    contextLength: 4096,         // Context window
    numPredict: 1000,            // Max tokens to generate
    numGpu: 1                    // GPU layers
);

// Or set options manually
$request = $request->setOptions([
    'seed' => 42,
    'top_k' => 40,
    'top_p' => 0.9,
    'temperature' => 0.8,
    'repeat_penalty' => 1.1,
    'num_ctx' => 4096,
    'num_predict' => 1000,
    'num_gpu' => 1
]);

$response = $request->post();
```

### Raw Mode & Templates

Disable prompt templating for maximum control:

```php
// Use raw prompt without template
$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: [
        ['role' => 'user', 'content' => 'Complete this: The meaning of life is']
    ]
)
->setRaw(true)                   // Disable prompt templating
->setKeepAlive('5m');

// Or use custom template
$customTemplate = """
### System:
You are a helpful assistant.

### User:
{{ .Prompt }}

### Assistant:
""";

$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: $messages
)
->setTemplate($customTemplate)   // Custom prompt template
->setKeepAlive('5m');

$response = $request->post();
```

### Streaming Responses

Enable real-time streaming for long responses:

```php
$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: [
        ['role' => 'user', 'content' => 'Write a detailed technical article about quantum computing']
    ]
)
->setStream(true)
->setMaxTokens(4000)
->setKeepAlive('15m');

$response = $request->post();

// Stream handling will be processed by the ChatEndpoint
// Response contains streaming data format
$streamingChunks = $response->streaming_chunks;
$finalChunk = $response->final_chunk;
```

### Performance Monitoring

Access detailed performance and timing metrics:

```php
$request = new OllamaChatRequest(
    model: 'llama3.2:latest',
    messages: $messages
)->setKeepAlive('10m');

$response = $request->post();

// Performance metrics (all in nanoseconds)
$totalTime = $response->total_duration;
$loadTime = $response->load_duration;
$promptEvalTime = $response->prompt_eval_duration;
$evalTime = $response->eval_duration;

// Token counts
$promptTokens = $response->prompt_eval_count;
$generatedTokens = $response->eval_count;

// Calculate speeds
$promptSpeed = $promptTokens / ($promptEvalTime / 1e9); // tokens/second
$generationSpeed = $generatedTokens / ($evalTime / 1e9); // tokens/second

echo "Prompt processing: {$promptSpeed} tokens/sec\n";
echo "Text generation: {$generationSpeed} tokens/sec\n";
echo "Total time: " . ($totalTime / 1e6) . "ms\n";

// Model state
$modelStillLoaded = $response->done;
$contextVector = $response->context; // For conversation continuity
```

## Response Handling

Access comprehensive response data and performance metrics:

```php
$response = $request->post();

// Basic response info
$model = $response->model;
$completed = $response->done;
$timestamp = $response->created_at;

// Content access
$textContent = $response->getTextContent();
$message = $response->message;

// Performance metrics (nanoseconds)
$totalDuration = $response->total_duration;
$loadDuration = $response->load_duration;
$promptEvalDuration = $response->prompt_eval_duration;
$evalDuration = $response->eval_duration;

// Token counts
$promptTokens = $response->prompt_eval_count;
$generatedTokens = $response->eval_count;
$totalTokens = $response->getTotalTokens();

// Completion analysis
$completedNaturally = $response->completedNaturally();
$hitTokenLimit = $response->reachedTokenLimit();

// Tool usage
$usedTools = $response->usedTools();
$toolCalls = $response->getToolCalls();

// Performance calculations
$promptSpeed = $response->getPromptSpeed();      // tokens/second
$generationSpeed = $response->getGenerationSpeed(); // tokens/second
$efficiency = $response->getEfficiencyScore();   // overall efficiency

// Streaming data
$isStreaming = $response->is_streaming;
$streamingChunks = $response->streaming_chunks;
$finalChunk = $response->final_chunk;

// Context for conversation continuity
$conversationContext = $response->context;

// Convert to array for storage
$responseArray = $response->toArray();

// Embeddings Response Handling
$embeddingResponse = $embeddingRequest->post();

$embeddings = $embeddingResponse->getEmbeddings();
$firstVector = $embeddingResponse->getFirstEmbedding();
$dimensions = $embeddingResponse->getDimensions();
$promptTokens = $embeddingResponse->getPromptTokens();
```

## Testing

The package provides testing utilities for mocking Ollama responses:

```php
use LLMSpeak\Ollama\OllamaChatRequest;
use LLMSpeak\Ollama\OllamaChatResponse;
use LLMSpeak\Ollama\OllamaEmbeddingsResponse;

// Create a mock chat response
$mockResponse = new OllamaChatResponse(
    status_code: 200,
    headers: [],
    model: 'llama3.2:latest',
    message: [
        'role' => 'assistant',
        'content' => 'Mock response content'
    ],
    done: true,
    total_duration: 1500000000,    // 1.5 seconds in nanoseconds
    load_duration: 100000000,      // 100ms
    prompt_eval_count: 15,
    prompt_eval_duration: 200000000, // 200ms
    eval_count: 25,
    eval_duration: 1200000000,     // 1.2 seconds
    created_at: now()->toISOString()
);

// Test your application logic
$this->assertEquals('Mock response content', $mockResponse->getTextContent());
$this->assertEquals(40, $mockResponse->getTotalTokens());
$this->assertTrue($mockResponse->completedNaturally());
$this->assertGreaterThan(0, $mockResponse->getGenerationSpeed());

// Create a mock embeddings response
$mockEmbeddingResponse = new OllamaEmbeddingsResponse(
    status_code: 200,
    headers: [],
    model: 'nomic-embed-text',
    embeddings: [array_fill(0, 768, 0.1)],
    prompt_eval_count: 5
);

// Test embedding functionality
$this->assertEquals(768, $mockEmbeddingResponse->getDimensions());
$this->assertEquals(1, count($mockEmbeddingResponse->getEmbeddings()));
```

## Credits

- [Project Saturn Studios](https://github.com/projectsaturnstudios)
- [Ollama](https://ollama.ai) for providing the local AI model runner
- The open-source AI community for creating amazing models

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

**Part of the LLMSpeak Ecosystem** - Built with ❤️ by [Project Saturn Studios](https://projectsaturnstudios.com)