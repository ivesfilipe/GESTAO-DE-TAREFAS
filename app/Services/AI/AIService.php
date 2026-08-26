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
    public function ask(string $system, string $user, ?float $temperature = null, ?int $maxTokens = null, array $entities = [], ?array $responseFormat = null, array $tools = []): AIResponse
    {
        if (! $this->zdr->isConfirmed()) {
            $combined = $system.' '.$user;
            if (! $this->zdr->allow($combined)) {
                throw new RuntimeException('Zero Data Retention ativo: conteúdo contém dados sensíveis não autorizados para envio externo.');
            }

            $system = $this->zdr->anonymize($system, $entities);
            $user = $this->zdr->anonymize($user, $entities);
        }

        $request = new AIRequest(
            system: $system,
            user: $user,
            temperature: $temperature,
            maxTokens: $maxTokens,
            metadata: ['provider' => $this->provider->name()],
            responseFormat: $responseFormat,
            tools: $tools,
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
                'provider' => $request->metadata['provider'] ?? $this->provider->name(),
                'model' => config("ai.providers.{$this->provider->name()}.model"),
                'prompt' => mb_substr($request->system."\n\n".$request->user, 0, 4000),
                'response' => $response ? mb_substr($response->content, 0, 4000) : null,
                'prompt_tokens' => $response?->promptTokens,
                'completion_tokens' => $response?->completionTokens,
                'total_tokens' => $response?->totalTokens,
                'status' => $error ? 'error' : 'success',
                'error_message' => $error ? mb_substr($error->getMessage(), 0, 500) : null,
                'duration_ms' => (int) ($startedAt->diffInMilliseconds(now())),
                'metadata' => array_diff_key($request->metadata, ['provider' => true]),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar log de uso de IA', ['error' => $e->getMessage()]);
        }
    }
}
