<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class NaturalLanguageTaskParser
{
    private const WEEKDAYS = [
        'domingo' => ['/\bdomingo\b/iu', 0],
        'segunda' => ['/\bsegunda(-feira)?\b/iu', 1],
        'terça' => ['/\bter[çc]a(-feira)?\b/iu', 2],
        'quarta' => ['/\bquarta(-feira)?\b/iu', 3],
        'quinta' => ['/\bquinta(-feira)?\b/iu', 4],
        'sexta' => ['/\bsexta(-feira)?\b/iu', 5],
        'sábado' => ['/\bs[áa]bado\b/iu', 6],
    ];

    public function parse(string $input, ?CarbonImmutable $now = null): array
    {
        $now = $now ?? CarbonImmutable::now();
        $original = trim($input);
        $text = mb_strtolower($original);
        $consumed = [];

        $priority = $this->extractPriority($text, $consumed);
        $recurrence = $this->extractRecurrence($text, $consumed);
        [$dueAt, $dateFragments] = $this->extractDueDate($text, $now, $consumed);
        $title = $this->buildTitle($original, array_merge($consumed, $dateFragments));

        if ($title === '') {
            throw new InvalidArgumentException('Não foi possível extrair o título da tarefa.');
        }

        return [
            'title' => mb_convert_case(mb_substr($title, 0, 1), MB_CASE_UPPER).''.mb_substr($title, 1),
            'due_at' => $dueAt,
            'priority' => $priority,
            'recurrence_frequency' => $recurrence,
        ];
    }

    private function extractPriority(string $text, array &$consumed): string
    {
        foreach ([
            '/\bcr[ií]tica\b/u' => 'critica',
            '/\burgente(?:mente)?\b/u' => 'urgente',
            '/\bimportante\b/u' => 'importante',
        ] as $pattern => $value) {
            if (preg_match($pattern, $text)) {
                $consumed[] = $pattern;

                return $value;
            }
        }

        return 'normal';
    }

    private function extractRecurrence(string $text, array &$consumed): ?string
    {
        $map = [
            '/\b(todo dia|todos os dias|diari[ao]|diári[ao])\b/u' => 'diaria',
            '/\b(toda semana|semanal(mente)?)\b/u' => 'semanal',
            '/\bquinzenal(mente)?\b/u' => 'quinzenal',
            '/\b(todo m[eê]s|mensal(mente)?)\b/u' => 'mensal',
        ];

        foreach ($map as $pattern => $value) {
            if (preg_match($pattern, $text)) {
                $consumed[] = $pattern;

                return $value;
            }
        }

        return null;
    }

    private function extractDueDate(string $text, CarbonImmutable $now, array &$consumed): array
    {
        $fragments = [];
        $date = null;
        $time = null;

        if (preg_match('/\b(\d{1,2})\/(\d{1,2})(?:\/(\d{4}))?\b/u', $text, $m)) {
            $year = isset($m[3]) ? (int) $m[3] : $this->resolveYear((int) $m[1], (int) $m[2], $now);
            $date = $now->setDate($year, (int) $m[2], (int) $m[1]);
            $fragments[] = '/\b'.$m[1].'\/'.$m[2].(isset($m[3]) ? '\/'.$m[3] : '').'\b/iu';
        } elseif (preg_match('/\bdepois de amanh[aã]\b/u', $text)) {
            $date = $now->addDays(2)->startOfDay();
            $fragments[] = '/\bdepois de amanh[aã](?:,)?\b/iu';
        } elseif (preg_match('/\bamanh[aã]\b/u', $text)) {
            $date = $now->addDay()->startOfDay();
            $fragments[] = '/\bamanh[aã](?:,)?\b/iu';
        } elseif (preg_match('/\bhoje\b/u', $text)) {
            $date = $now->startOfDay();
            $fragments[] = '/\bhoje\b/iu';
        } elseif (preg_match('/\bem (\d+) dias?\b/u', $text, $m)) {
            $date = $now->addDays((int) $m[1])->startOfDay();
            $fragments[] = '/\bem '.$m[1].' dias?(?!\w)/iu';
        } elseif (preg_match('/\bem (\d+) semanas?\b/u', $text, $m)) {
            $date = $now->addWeeks((int) $m[1])->startOfDay();
            $fragments[] = '/\bem '.$m[1].' semanas?\b/iu';
        } else {
            $weekdayMatch = $this->matchWeekday($text);
            if ($weekdayMatch !== null) {
                [$pattern, $dayNumber] = $weekdayMatch;
                $date = $this->nextWeekdayOccurrence($now, $dayNumber);
                $fragments[] = $pattern;
                if (preg_match('/\bproxim[oa]\b/u', $text)) {
                    $fragments[] = '/\bproxim[oa](?:,)?\b/iu';
                }
            }
        }

        if (preg_match('/(?:\b(?:à|a)s\s*)?\b(\d{1,2})[:h](\d{2})?(?:\s*h?)?\b/u', $text, $m)) {
            $hour = min((int) $m[1], 23);
            $minute = isset($m[2]) ? min((int) $m[2], 59) : 0;
            $time = [$hour, $minute];
            $fragments[] = '/(?:\b(?:à|a)s\s*)?\b'.$m[1].'[:h]'.($m[2] ?? '').'(?:\s*h?)?\b/iu';
        }

        if ($date === null && $time !== null) {
            $candidate = $now->setTime($time[0], $time[1]);
            $date = $candidate->lessThan($now) ? $candidate->addDay()->startOfDay() : $now->startOfDay();
        }

        if ($date === null) {
            $date = $now->addDay()->setTime(17, 0);

            return [$date, $fragments];
        }

        $time = $time ?? [17, 0];

        return [$date->setTime($time[0], $time[1]), $fragments];
    }

    private function matchWeekday(string $text): ?array
    {
        foreach (self::WEEKDAYS as [$pattern, $dayNumber]) {
            if (preg_match($pattern, $text)) {
                return [$pattern, $dayNumber];
            }
        }

        return null;
    }

    private function nextWeekdayOccurrence(CarbonImmutable $now, int $targetDayNumber): CarbonImmutable
    {
        $candidate = $now->startOfDay()->addDay();

        while ($candidate->dayOfWeek !== $targetDayNumber) {
            $candidate = $candidate->addDay();
        }

        return $candidate;
    }

    private function resolveYear(int $day, int $month, CarbonImmutable $now): int
    {
        $year = (int) $now->format('Y');

        if (! checkdate($month, $day, $year)) {
            return $year;
        }

        $candidate = $now->setDate($year, $month, $day)->startOfDay();

        return $candidate->lessThan($now->startOfDay()) ? $year + 1 : $year;
    }

    private function buildTitle(string $text, array $patterns): string
    {
        $title = $text;

        foreach ($patterns as $pattern) {
            $title = preg_replace($pattern, ' ', $title);
        }

        $title = preg_replace('/\b(até|ate|para|prazo|no dia|dia)\b/iu', ' ', $title);
        $title = preg_replace('/\s+([,.!?;:]?)/u', '$1 ', $title);
        $title = preg_replace('/\s+([,.!?;:])/u', '$1', $title);
        $title = trim((string) preg_replace('/[\s,;\-–]+\z/', '', (string) preg_replace('/\A[\s,\-–]+/', '', (string) $title)));
        $title = trim((string) preg_replace('/ {2,}/u', ' ', $title));

        return rtrim($title, ' ,;-–');
    }
}
