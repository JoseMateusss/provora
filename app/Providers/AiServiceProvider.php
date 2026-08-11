<?php

namespace App\Providers;

use App\Services\Ai\Adapters\AnthropicAdapter;
use App\Services\Ai\Adapters\OpenAiAdapter;
use App\Services\Ai\Contracts\LlmProviderInterface;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LlmProviderInterface::class, function () {
            $driver = config('ai.default_driver', 'openai');

            return match ($driver) {
                'anthropic' => new AnthropicAdapter(
                    apiKey: (string) config('ai.anthropic.key'),
                    model: (string) config('ai.anthropic.model')
                ),
                default => new OpenAiAdapter(
                    apiKey: (string) config('ai.openai.key'),
                    model: (string) config('ai.openai.model')
                ),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
