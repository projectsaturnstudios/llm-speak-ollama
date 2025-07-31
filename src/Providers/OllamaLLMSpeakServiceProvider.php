<?php

namespace LLMSpeak\Ollama\Providers;

use Illuminate\Support\ServiceProvider;

class OllamaLLMSpeakServiceProvider extends ServiceProvider
{
    protected array $config = [
        'llms.chat-providers.drivers.ollama' => __DIR__ .'/../../config/ollama.php',
        'llms.embeddings-providers.drivers.ollama' => __DIR__ .'/../../config/ollama-embeddings.php',
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['llms.chat-providers.drivers.ollama'] => config_path('llms/chat-providers/drivers/ollama.php'),
            $this->config['llms.embeddings-providers.drivers.ollama'] => config_path('llms/embeddings-providers/drivers/ollama.php'),
        ], ['llms', 'llms.ollama']);
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }

}
