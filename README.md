```php
use LLMSpeak\Ollama\Support\Facades\Ollama;

Ollama::messages() <---OllamaMessagesAPIRepository Instance
    ->withModel($model) <---OllamaMessagesAPIRepository Instance
    ->withTools($temperature) <---OllamaMessagesAPIRepository Instance
    ->withMessages($messages) <--- MessagesEndpoint Instance
    ->handle();
```
