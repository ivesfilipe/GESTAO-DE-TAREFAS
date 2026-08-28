<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use App\DTO\AI\AIRequest;
use App\DTO\AI\AIResponse;
use App\Models\AIUsageLog;
use App\Services\AI\Safety\ZeroDataRetention;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIService
{
    private AIProviderInterface $provider;

    private ZeroDataRetention $zdr;

    public function __construct(?AIProviderInterface $provider = null, ?ZeroDataRetention $zdr = null)
    {
        $this->provider = $provider ?? app(AIProviderManager::class)->resolve();
        $this->zdr = $zdr ?? new ZeroDataRetention;
    }

    public function provider(): AIProviderInterface
    {
        return $this->provider;
    }

    public function isMock(): bool
    {
        return $this->provider->name() === 'mock';
    }

    /**
     * Envia uma requisição para o provider ativo, aplicando ZDR quando necessário.
     *
     * @param  array<string, mixed>  $entities  Entidades para anonimização (User, Task, etc.)
     */
    /**
     * @param  list<array<string, mixed>>  $messages
     */
    public function ask(string $system, string $user, ?float $temperature = null, ?int $maxTokens = null, array $entities = [], ?array $responseFormat = null, array $tools = [], array $messages = [], ?string $model = null): AIResponse
    {
        if ($this->isExternalProvider() && ! $this->zdr->isConfirmed()) {
            throw new RuntimeException('ZDR exige confirmação administrativa antes de enviar qualquer contexto a um provider externo.');
        }

        if (! $this->zdr->isConfirmed()) {
            $combined = $system.' '.$user;
            if (! $this->zdr->allow($combined)) {
                throw new RuntimeException('Zero Data Retention ativo: conteúdo contém dados sensíveis não autorizados para envio externo.');
            }

            $system = $this->zdr->anonymize($system, $entities);
            $user = $this->zdr->anonymize($user, $entities);
            $messages = array_map(function (array $message) use ($entities) {
                if (isset($message['content']) && is_string($message['content'])) {
                    $message['content'] = $this->zdr->anonymize($message['content'], $entities);
                }

                return $message;
            }, $messages);
        }

        $request = new AIRequest(
            system: $system,
            user: $user,
            temperature: $temperature,
            maxTokens: $maxTokens,
            metadata: ['provider' => $this->provider->name()],
            responseFormat: $responseFormat,
            tools: $tools,
            messages: $messages,
            model: $model,
        );

        $startedAt = now();

        try {
            $response = $this->provider->complete($request);
            $this->logUsage($request, $response, $startedAt, null);

            return $response;
        } catch (\Throwable $e) {
            $this->logUsage($request, null, $startedAt, $e);
            Log::warning('AIService ask failed', ['provider' => $this->provider->name(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Pergunta simples sem entidades específicas para anonimização.
     */
    public function chat(string $system, string $user, ?float $temperature = null, ?int $maxTokens = null): AIResponse
    {
        return $this->ask($system, $user, $temperature, $maxTokens);
    }

    private function logUsage(AIRequest $request, ?AIResponse $response, \DateTimeInterface $startedAt, ?\Throwable $error = null): void
    {
        if (! config('ai.logging.enabled', true)) {
            return;
        }

        try {
            AIUsageLog::create([
                'user_id' => auth()->id(),
                'provider' => $request->metadata['provider'] ?? $this->provider->name(),
                'model' => $request->model ?? config("ai.providers.{$this->provider->name()}.model"),
                'prompt_tokens' => $response?->promptTokens,
                'completion_tokens' => $response?->completionTokens,
                'total_tokens' => $response?->totalTokens,
                'status' => $error ? 'error' : 'success',
                'error_message' => $error ? $error::class : null,
                'duration_ms' => (int) ($startedAt->diffInMilliseconds(now())),
                'metadata' => array_diff_key($request->metadata, ['provider' => true]),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar log de uso de IA', ['error' => $e->getMessage()]);
        }
    }

    private function isExternalProvider(): bool
    {
        return ! in_array($this->provider->name(), ['mock', 'ollama'], true);
    }
}
