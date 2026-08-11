<?php

namespace Tests\Unit;

use App\Services\Ai\Adapters\AnthropicAdapter;
use App\Services\Ai\Adapters\OpenAiAdapter;
use App\Services\Ai\Contracts\LlmProviderInterface;
use Tests\TestCase;

class LlmProviderTest extends TestCase
{
    public function test_binds_openai_adapter_by_default(): void
    {
        config(['ai.default_driver' => 'openai']);
        config(['ai.openai.key' => 'test-key']);

        $provider = app(LlmProviderInterface::class);

        $this->assertInstanceOf(OpenAiAdapter::class, $provider);
    }

    public function test_binds_anthropic_adapter_when_configured(): void
    {
        config(['ai.default_driver' => 'anthropic']);
        config(['ai.anthropic.key' => 'test-key']);

        $provider = app(LlmProviderInterface::class);

        $this->assertInstanceOf(AnthropicAdapter::class, $provider);
    }
}
