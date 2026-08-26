<?php

namespace App\DTO\AI;

class AIRequest
{
    /**
     * @param  string  $system  Instrução de sistema (já sanitizada se necessário)
     * @param  string  $user  Mensagem do usuário (já sanitizada se necessário)
     * @param  array<string, mixed>  $metadata  Dados para auditoria (não enviados ao provider)
     */
    public function __construct(
        public string $system,
        public string $user,
        public ?float $temperature = null,
        public ?int $maxTokens = null,
        public array $metadata = [],
        public ?array $responseFormat = null,
        public array $tools = [],
    ) {}

    public function withMetadata(array $metadata): self
    {
        return new self(
            $this->system,
            $this->user,
            $this->temperature,
            $this->maxTokens,
            array_merge($this->metadata, $metadata),
            $this->responseFormat,
            $this->tools,
        );
    }
}
