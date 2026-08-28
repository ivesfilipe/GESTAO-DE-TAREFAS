<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use App\Services\AI\Providers\GroqProvider;
use App\Services\AI\Providers\MockProvider;
use App\Services\AI\Providers\OllamaProvider;
use App\Services\AI\Providers\OpenAIProvider;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class AIProviderManager
{
    /**
     * @var array<string, class-string<AIProviderInterface>>
     */
    private array $drivers = [
        'groq' => GroqProvider::class,
        'openai' => OpenAIProvider::class,
        'ollama' => OllamaProvider::class,
        'mock' => MockProvider::class,
    ];

    public function default(): AIProviderInterface
    {
        return $this->create(config('ai.default', 'mock'));
    }

    public function create(string $name): AIProviderInterface
    {
        $config = config("ai.providers.{$name}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Provider de IA '{$name}' não configurado.");
        }

        $driver = $config['driver'] ?? $name;

        if (! isset($this->drivers[$driver])) {
            throw new InvalidArgumentException("Driver de IA '{$driver}' não suportado.");
        }

        $class = $this->drivers[$driver];

        return new $class($config);
    }

    /**
     * Resolve o provider ativo, caindo em mock quando o padrão não está disponível
     * e fallback está desabilitado.
     */
    public function resolve(): AIProviderInterface
    {
        $provider = $this->default();

        if ($provider->isAvailable()) {
            return $provider;
        }

        if (config('ai.fallback_enabled', false)) {
            return $this->tryFallback($provider);
        }

        Log::info('Provider padrão indisponível e fallback desabilitado. Usando mock.', ['provider' => $provider->name()]);

        return new MockProvider;
    }

    private function tryFallback(AIProviderInterface $original): AIProviderInterface
    {
        $chain = config('ai.fallback_chain', []);

        foreach ($chain as $name) {
            if ($name === 'openai') {
                Log::warning('Fallback pago bloqueado por política', ['provider' => $name]);

                continue;
            }

            try {
                $candidate = $this->create($name);
                if ($candidate->isAvailable()) {
                    Log::info('Fallback de IA ativado', [
                        'from' => $original->name(),
                        'to' => $candidate->name(),
                    ]);

                    return $candidate;
                }
            } catch (\Throwable $e) {
                Log::warning("Fallback para '{$name}' falhou", ['error' => $e->getMessage()]);
            }
        }

        return new MockProvider;
    }

    public function register(string $name, string $class): void
    {
        $this->drivers[$name] = $class;
    }
}
