<?php

namespace LLMSpeak\Ollama\Providers;

use Illuminate\Support\ServiceProvider;

class OllamaLLMSpeakServiceProvider extends ServiceProvider
{
    protected array $config = [
        'llms.providers.drivers.ollama' => __DIR__ .'/../../config/ollama.php',
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
            $this->config['llms.providers.drivers.ollama'] => config_path('llms/ollama.php'),
        ], ['llms', 'llms.ollama']);
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }

}
