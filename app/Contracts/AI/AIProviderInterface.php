<?php

namespace App\Contracts\AI;

use App\DTO\AI\AIRequest;
use App\DTO\AI\AIResponse;

interface AIProviderInterface
{
    /**
     * Nome legível do provider.
     */
    public function name(): string;

    /**
     * Verifica se o provider está disponível (chave configurada, serviço acessível, etc.).
     */
    public function isAvailable(): bool;

    /**
     * Envia uma requisição de chat completion e retorna a resposta.
     *
     * @throws \RuntimeException em caso de falha na comunicação ou resposta inválida.
     */
    public function complete(AIRequest $request): AIResponse;
}
