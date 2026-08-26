<?php

namespace App\Services\AI\Providers;

class OllamaProvider extends OpenAICompatibleProvider
{
    public function name(): string
    {
        return 'ollama';
    }

    protected function defaultModel(): string
    {
        return 'llama3.1';
    }

    protected function allowsAnonymousAccess(): bool
    {
        return true;
    }
}
