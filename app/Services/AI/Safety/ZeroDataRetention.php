<?php

namespace App\Services\AI\Safety;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Str;

class ZeroDataRetention
{
    /**
     * Dicionário de tokens anonimizados para evitar vazamento de dados reais.
     *
     * @var array<string, string>
     */
    private array $tokens = [];

    public function isConfirmed(): bool
    {
        $required = config('ai.zdr.required', true);
        $confirmed = config('ai.zdr.confirmed', false);

        return ! $required || $confirmed;
    }

    /**
     * Anonimiza um texto substituindo nomes, e-mails e títulos por tokens.
     * Se ZDR não estiver confirmado, também remove possíveis telefones e CPFs.
     */
    public function anonymize(string $text, array $entities = []): string
    {
        if ($this->isConfirmed()) {
            return $text;
        }

        $result = $text;

        foreach ($entities as $key => $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            $token = $this->tokenFor($key);
            $result = $this->replaceInsensitive($result, $value, $token);
        }

        $result = $this->maskEmailAddresses($result);
        $result = $this->maskPhoneNumbers($result);
        $result = $this->maskDocumentNumbers($result);

        return $result;
    }

    /**
     * Bloqueia envio de dados sensíveis quando ZDR não está confirmado.
     * Retorna true se o conteúdo foi considerado seguro.
     */
    public function allow(string $text): bool
    {
        if ($this->isConfirmed()) {
            return true;
        }

        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/', $text)) {
            return false;
        }

        if (preg_match('/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/', $text)) {
            return false;
        }

        return true;
    }

    /**
     * Extrai entidades de um usuário para anonimização.
     */
    public function entitiesFromUser(User $user): array
    {
        return [
            'user_name_'.$user->id => $user->name,
            'user_email_'.$user->id => $user->email,
        ];
    }

    /**
     * Extrai entidades de uma tarefa para anonimização.
     */
    public function entitiesFromTask(Task $task): array
    {
        $entities = [
            'task_title_'.$task->id => $task->title,
            'task_description_'.$task->id => $task->description ?? '',
        ];

        if ($task->assignee) {
            $entities = array_merge($entities, $this->entitiesFromUser($task->assignee));
        }

        if ($task->creator) {
            $entities = array_merge($entities, $this->entitiesFromUser($task->creator));
        }

        return $entities;
    }

    private function tokenFor(string $key): string
    {
        return $this->tokens[$key] ??= '['.Str::upper(Str::slug($key, '_')).']';
    }

    private function replaceInsensitive(string $haystack, string $needle, string $replacement): string
    {
        if ($needle === '') {
            return $haystack;
        }

        return str_ireplace($needle, $replacement, $haystack);
    }

    private function maskEmailAddresses(string $text): string
    {
        return preg_replace(
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/',
            '[EMAIL]',
            $text
        ) ?? $text;
    }

    private function maskPhoneNumbers(string $text): string
    {
        return preg_replace(
            '/\b(?:\+?55\s?)?\(?\d{2}\)?\s?\d{4,5}-?\d{4}\b/',
            '[TELEFONE]',
            $text
        ) ?? $text;
    }

    private function maskDocumentNumbers(string $text): string
    {
        return preg_replace(
            '/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/',
            '[DOCUMENTO]',
            $text
        ) ?? $text;
    }
}
