<?php

use App\Services\NaturalLanguageTaskParser;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('parser');

function fixedNow(): CarbonImmutable
{
    return CarbonImmutable::parse('2026-08-25 10:00', 'America/Sao_Paulo');
}

test('extrai titulo prazo e hora simples', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Reunião com o time amanhã às 15h', fixedNow());

    expect($parsed['title'])->toBe('Reunião com o time');
    expect($parsed['due_at']->format('Y-m-d H:i'))->toBe('2026-08-26 15:00');
    expect($parsed['priority'])->toBe('normal');
});

test('detecta prioridade urgente', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Enviar proposta urgente até amanhã 09:30', fixedNow());

    expect($parsed['priority'])->toBe('urgente');
    expect($parsed['due_at']->format('Y-m-d H:i'))->toBe('2026-08-26 09:30');
    expect($parsed['title'])->toContain('Enviar proposta');
});

test('detecta prioridade critica com acento', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Corrigir falha crítica hoje às 18h', fixedNow());

    expect($parsed['priority'])->toBe('critica');
});

test('amanha sem hora usa 17h padrao', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Ligar para o fornecedor amanhã', fixedNow());

    expect($parsed['due_at']->format('Y-m-d H:i'))->toBe('2026-08-26 17:00');
});

test('expressao em N dias', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Inspecionar equipamentos em 3 dias', fixedNow());

    expect($parsed['due_at']->format('Y-m-d'))->toBe('2026-08-28');
    expect($parsed['title'])->toBe('Inspecionar equipamentos');
});

test('proxima segunda-feira pula para semana seguinte', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Apresentar métricas próxima segunda-feira', fixedNow());

    expect($parsed['due_at']->dayOfWeek)->toBe(1);
    expect($parsed['due_at']->format('Y-m-d'))->toBe('2026-08-31');
});

test('sexta simples resolve para sexta da semana corrente', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Fechar apontamentos sexta', fixedNow());

    expect($parsed['due_at']->format('Y-m-d'))->toBe('2026-08-28');
});

test('data explicita dd/mm com ano automatico', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Auditoria interna 15/09', fixedNow());

    expect($parsed['due_at']->format('Y-m-d'))->toBe('2026-09-15');
});

test('recorrencia semanal detectada', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Backup dos servidores toda segunda 08h', fixedNow());

    expect($parsed['recurrence_frequency'])->toBeNull();

    $parsed2 = (new NaturalLanguageTaskParser)->parse('Relatório de status toda semana às 16h', fixedNow());
    expect($parsed2['recurrence_frequency'])->toBe('semanal');
    expect($parsed2['due_at']->hour)->toBe(16);
});

test('recorrencia diaria e mensal', function () {
    $p1 = (new NaturalLanguageTaskParser)->parse('Checklist de abertura todo dia 07h', fixedNow());
    expect($p1['recurrence_frequency'])->toBe('diaria');

    $p2 = (new NaturalLanguageTaskParser)->parse('Inventário do estoque todo mês dia 05/09', fixedNow());
    expect($p2['recurrence_frequency'])->toBe('mensal');
});

test('somente hora sem data assume hoje ou amanha', function () {
    $p1 = (new NaturalLanguageTaskParser)->parse('Revisar orçamento às 14h', fixedNow());
    expect($p1['due_at']->format('Y-m-d H:i'))->toBe('2026-08-25 14:00');

    $p2 = (new NaturalLanguageTaskParser)->parse('Revisar orçamento às 08h', fixedNow());
    expect($p2['due_at']->format('Y-m-d H:i'))->toBe('2026-08-26 08:00');
});

test('sem informacao de prazo usa amanha as 17h', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Organizar documentação do projeto', fixedNow());

    expect($parsed['due_at']->format('Y-m-d H:i'))->toBe('2026-08-26 17:00');
    expect($parsed['title'])->toBe('Organizar documentação do projeto');
});

test('preserva maiusculas e siglas do titulo original', function () {
    $parsed = (new NaturalLanguageTaskParser)->parse('Revisar filtros do HVAC amanhã às 15h urgente', fixedNow());

    expect($parsed['title'])->toBe('Revisar filtros do HVAC');
    expect($parsed['priority'])->toBe('urgente');
});

test('texto sem titulo lanca excecao', function () {
    new NaturalLanguageTaskParser;

    expect(fn () => (new NaturalLanguageTaskParser)->parse('urgente amanhã', fixedNow()))
        ->toThrow(InvalidArgumentException::class);
});
