<?php

namespace App\Services\AI\Tools;

class AITools
{
    /**
     * Tenta extrair um objeto JSON de uma string, mesmo que envolto em markdown.
     *
     * @return array<string, mixed>|null
     */
    public static function extractJson(string $text): ?array
    {
        $text = trim($text);

        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $text = $matches[1];
        } elseif (preg_match('/```\s*(.*?)\s*```/s', $text, $matches)) {
            $text = $matches[1];
        }

        $text = trim($text);

        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Tenta extrair um array JSON de strings.
     *
     * @return list<string>|null
     */
    public static function extractStringArray(string $text): ?array
    {
        $decoded = self::extractJson($text);

        if (is_array($decoded)) {
            $values = array_values($decoded);
            if (count($values) > 0 && is_string($values[0])) {
                return array_values(array_filter($values, 'is_string'));
            }
        }

        return null;
    }

    /**
     * Normaliza uma lista de critérios/evidências, garantindo no máximo $limit itens.
     *
     * @param  list<mixed>  $items
     * @return list<string>
     */
    public static function normalizeItems(array $items, int $limit = 5): array
    {
        $filtered = array_values(array_filter($items, fn ($item) => is_string($item) && trim($item) !== ''));

        return array_slice(array_map('trim', $filtered), 0, $limit);
    }
}
