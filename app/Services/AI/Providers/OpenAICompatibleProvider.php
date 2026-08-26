<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\AIProviderInterface;
use App\DTO\AI\AIRequest;
use App\DTO\AI\AIResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

abstract class OpenAICompatibleProvider implements AIProviderInterface
{
    protected string $baseUrl;

    protected ?string $apiKey;

    protected string $model;

    protected int $timeout;

    protected ?int $maxTokens;

    protected ?float $temperature;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey = $config['api_key'] ?? null;
        $this->model = $config['model'] ?? $this->defaultModel();
        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->maxTokens = isset($config['max_tokens']) ? (int) $config['max_tokens'] : null;
        $this->temperature = isset($config['temperature']) ? (float) $config['temperature'] : null;
    }

    abstract protected function defaultModel(): string;

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey) || $this->allowsAnonymousAccess();
    }

    protected function allowsAnonymousAccess(): bool
    {
        return false;
    }

    public function complete(AIRequest $request): AIResponse
    {
        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $request->system],
                ['role' => 'user', 'content' => $request->user],
            ],
        ];

        if ($request->maxTokens !== null) {
            $payload['max_tokens'] = $request->maxTokens;
        } elseif ($this->maxTokens !== null) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        if ($request->temperature !== null) {
            $payload['temperature'] = $request->temperature;
        } elseif ($this->temperature !== null) {
            $payload['temperature'] = $this->temperature;
        }

        if ($request->responseFormat !== null) {
            $payload['response_format'] = $request->responseFormat;
        }

        if ($request->tools !== []) {
            $payload['tools'] = $request->tools;
        }

        $http = Http::timeout($this->timeout)
            ->withHeaders(['Accept' => 'application/json']);

        if (! empty($this->apiKey)) {
            $http = $http->withToken($this->apiKey);
        }

        try {
            $response = $http->post("{$this->baseUrl}/chat/completions", $payload);
        } catch (\Throwable $e) {
            Log::warning('AI provider request failed', ['provider' => $this->name(), 'error' => $e->getMessage()]);
            throw new RuntimeException("Provider {$this->name()} indisponível: {$e->getMessage()}");
        }

        if ($response->failed()) {
            Log::warning('AI provider returned error', [
                'provider' => $this->name(),
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException("Provider {$this->name()} retornou erro HTTP {$response->status()}.");
        }

        $data = $response->json();
        $message = $data['choices'][0]['message'] ?? [];
        $content = trim((string) ($message['content'] ?? ''));
        $toolCalls = $this->normalizeToolCalls($message['tool_calls'] ?? []);

        if ($content === '' && $toolCalls === []) {
            throw new RuntimeException("Provider {$this->name()} retornou resposta vazia.");
        }

        return new AIResponse(
            content: $content,
            finishReason: $data['choices'][0]['finish_reason'] ?? null,
            promptTokens: $data['usage']['prompt_tokens'] ?? null,
            completionTokens: $data['usage']['completion_tokens'] ?? null,
            totalTokens: $data['usage']['total_tokens'] ?? null,
            raw: array_diff_key($data, array_flip(['choices'])),
            toolCalls: $toolCalls,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeToolCalls(mixed $toolCalls): array
    {
        if (! is_array($toolCalls)) {
            return [];
        }

        $normalized = [];

        foreach ($toolCalls as $call) {
            if (! is_array($call) || ($call['type'] ?? '') !== 'function') {
                continue;
            }

            $function = $call['function'] ?? [];
            $arguments = $function['arguments'] ?? '{}';

            if (is_string($arguments)) {
                $decoded = json_decode($arguments, true);
                $arguments = is_array($decoded) ? $decoded : [];
            }

            $normalized[] = [
                'id' => $call['id'] ?? '',
                'name' => $function['name'] ?? '',
                'arguments' => $arguments,
            ];
        }

        return $normalized;
    }
}
