```php
use LLMSpeak\Ollama\Support\Facades\Ollama;

Ollama::messages() <---OllamaMessagesAPIRepository Instance
    ->withApiKey($config['api_key']) <---OllamaMessagesAPIRepository Instance
    ->withModel($model) <---OllamaMessagesAPIRepository Instance
    ->withMaxTokens($max_tokens) <---OllamaMessagesAPIRepository Instance
    ->withSystemPrompt($prompt) <---OllamaMessagesAPIRepository Instance
    ->withTools($temperature) <---OllamaMessagesAPIRepository Instance
    ->withTemperature($temperature) <---OllamaMessagesAPIRepository Instance
    ->withMessages($messages) <--- MessagesEndpoint Instance
    ->handle();
```
