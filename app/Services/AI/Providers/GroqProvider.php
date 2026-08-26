<?php

namespace App\Services\AI\Providers;

class GroqProvider extends OpenAICompatibleProvider
{
    public function name(): string
    {
        return 'groq';
    }

    protected function defaultModel(): string
    {
        return 'openai/gpt-oss-120b';
    }
}
