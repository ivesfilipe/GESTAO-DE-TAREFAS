<?php

use App\Contracts\AI\AIProviderInterface;
use App\DTO\AI\AIRequest;
use App\DTO\AI\AIResponse;
use App\Services\AI\AIService;
use App\Services\AI\Providers\MockProvider;
use App\Services\AI\Safety\ZeroDataRetention;

beforeEach(function () {
    config(['ai.logging.enabled' => false]);
    config(['ai.zdr.confirmed' => true]);
});

test('ask retorna resposta do provider', function () {
    $provider = new MockProvider;
    $service = new AIService($provider);

    $response = $service->ask('Você é um assistente.', 'divida esta tarefa em passos');

    expect($response)->toBeInstanceOf(AIResponse::class)
        ->and($response->content)->toBeString()
        ->and($response->finishReason)->toBe('stop');
});

test('isMock retorna true quando provider é mock', function () {
    $service = new AIService(new MockProvider);

    expect($service->isMock())->toBeTrue();
});

test('zdr não confirmado anonimiza dados', function () {
    config(['ai.zdr.confirmed' => false]);

    $provider = Mockery::mock(AIProviderInterface::class);
    $provider->shouldReceive('complete')->once()->andReturnUsing(function (AIRequest $request) {
        expect($request->user)->not->toContain('João da Silva')
            ->and($request->user)->toContain('[USER_NAME_1]');

        return new AIResponse(content: 'ok');
    });
    $provider->shouldReceive('name')->andReturn('mock');

    $zdr = new ZeroDataRetention;
    $service = new AIService($provider, $zdr);

    $service->ask(
        system: 'Analise.',
        user: 'Responsável: João da Silva',
        entities: ['user_name_1' => 'João da Silva'],
    );
});

test('zdr bloqueia dados sensíveis', function () {
    config(['ai.zdr.confirmed' => false]);

    $provider = Mockery::mock(AIProviderInterface::class);
    $provider->shouldReceive('name')->andReturn('mock');

    $service = new AIService($provider, new ZeroDataRetention);

    expect(fn () => $service->ask('Analise.', 'Envie para joao@empresa.com'))
        ->toThrow(RuntimeException::class, 'Zero Data Retention');
});
