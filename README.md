[![Latest Version on Packagist](https://img.shields.io/packagist/v/llm-speak/ollama.svg?style=flat-square)](https://packagist.org/packages/llm-speak/ollama)
[![Total Downloads](https://img.shields.io/packagist/dt/llm-speak/ollama.svg?style=flat-square)](https://packagist.org/packages/llm-speak/ollama)

```php
use LLMSpeak\Ollama\Support\Facades\Ollama;

Ollama::messages() <---OllamaMessagesAPIRepository Instance
    ->withModel($model) <---OllamaMessagesAPIRepository Instance
    ->withTools($temperature) <---OllamaMessagesAPIRepository Instance
    ->withMessages($messages) <--- MessagesEndpoint Instance
    ->handle();

Ollama::embeddings()
    ->withModel($model)
    ->withInput($convo)
    ->handle();
```
