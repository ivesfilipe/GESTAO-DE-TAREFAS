<?php

namespace App\Services\AI\Providers;

class OpenAIProvider extends OpenAICompatibleProvider
{
    public function __construct(array $config)
    {
        if (empty($config['api_key'])) {
            $config['api_key'] = config('services.openai.key');
        }

        parent::__construct($config);
    }

    public function name(): string
    {
        return 'openai';
    }

    protected function defaultModel(): string
    {
        return 'gpt-4o-mini';
    }
}
