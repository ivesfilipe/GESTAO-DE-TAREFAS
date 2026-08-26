<?php

use App\Services\AI\AIProviderManager;
use App\Services\AI\Providers\MockProvider;

beforeEach(function () {
    config(['ai.default' => 'groq']);
    config(['ai.providers.groq.api_key' => null]);
    config(['ai.fallback_enabled' => false]);
});

test('resolve retorna mock quando provider padrão indisponível e fallback desabilitado', function () {
    $manager = new AIProviderManager;
    $provider = $manager->resolve();

    expect($provider->name())->toBe('mock');
});

test('create retorna provider pelo nome', function () {
    config(['ai.providers.mock' => ['driver' => 'mock']]);

    $manager = new AIProviderManager;
    $provider = $manager->create('mock');

    expect($provider)->toBeInstanceOf(MockProvider::class);
});

test('create desconhecido lança exceção', function () {
    $manager = new AIProviderManager;

    expect(fn () => $manager->create('inexistente'))
        ->toThrow(InvalidArgumentException::class);
});
