<?php

namespace App\DTO\AI;

class AIResponse
{
    /**
     * @param  string  $content  Texto de resposta do modelo
     * @param  array<string, mixed>  $raw  Resposta bruta do provider (truncada/limitada)
     */
    public function __construct(
        public string $content,
        public ?string $finishReason = null,
        public ?int $promptTokens = null,
        public ?int $completionTokens = null,
        public ?int $totalTokens = null,
        public array $raw = [],
        public array $toolCalls = [],
    ) {}

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'finish_reason' => $this->finishReason,
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens' => $this->totalTokens,
        ];
    }
}
