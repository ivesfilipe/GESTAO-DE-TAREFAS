<?php

use App\DTO\AI\AIRequest;
use App\Services\AI\Providers\GroqProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

function groqProvider(?string $apiKey = 'test-key'): GroqProvider
{
    return new GroqProvider([
        'base_url' => 'https://api.groq.com/openai/v1',
        'api_key' => $apiKey,
        'model' => 'test-model',
        'timeout' => 5,
    ]);
}

test('groq provider interpreta resposta http 200', function () {
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Resposta válida.']]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
        ]),
    ]);

    $response = groqProvider()->complete(new AIRequest('Sistema', 'Pergunta'));

    expect($response->content)->toBe('Resposta válida.')
        ->and($response->totalTokens)->toBe(15);
});

test('groq provider rejeita erros http do provider', function (int $status) {
    Http::fake(['https://api.groq.com/openai/v1/chat/completions' => Http::response(['error' => 'falha'], $status)]);

    expect(fn () => groqProvider()->complete(new AIRequest('Sistema', 'Pergunta')))
        ->toThrow(RuntimeException::class, "HTTP {$status}");
})->with([401, 403, 429, 500]);

test('groq provider rejeita resposta json invalida', function () {
    Http::fake(['https://api.groq.com/openai/v1/chat/completions' => Http::response('not-json', 200)]);

    expect(fn () => groqProvider()->complete(new AIRequest('Sistema', 'Pergunta')))
        ->toThrow(RuntimeException::class, 'JSON inválido');
});

test('groq provider trata timeout como indisponibilidade', function () {
    Http::fake(fn () => throw new ConnectionException('Timeout'));

    expect(fn () => groqProvider()->complete(new AIRequest('Sistema', 'Pergunta')))
        ->toThrow(RuntimeException::class, 'indisponível');
});

test('groq provider sem chave nao fica disponivel', function () {
    expect(groqProvider(null)->isAvailable())->toBeFalse();
});
