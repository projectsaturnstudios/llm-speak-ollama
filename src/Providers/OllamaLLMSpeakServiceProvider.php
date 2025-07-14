<?php

namespace LLMSpeak\Ollama\Providers;

use Illuminate\Support\ServiceProvider;
use LLMSpeak\Ollama\OllamaLocal;

class OllamaLLMSpeakServiceProvider extends ServiceProvider
{
    protected array $config = [
        'llms.services.ollama' => __DIR__ .'/../../config/llms/ollama.php',
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
        OllamaLocal::boot();
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['llms.services.ollama'] => config_path('llms/ollama.php'),
        ], ['llms', 'llms.ollama']);
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }

}
