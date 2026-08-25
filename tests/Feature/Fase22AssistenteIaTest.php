<?php

use App\Models\Task;
use App\Models\User;
use App\Services\AiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('resumo diario conta tarefas corretamente', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->vencida()->count(2)->create(['created_by' => $gestor->id]);
    Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id, 'due_at' => now()->addDays(2)]);
    Task::factory()->withAssignee($liderado)->bloqueada()->create(['created_by' => $gestor->id]);

    $summary = (new AiAssistantService)->dailySummary($gestor);

    expect($summary['overdue'])->toBe(2);
    expect($summary['due_this_week'])->toBeGreaterThanOrEqual(1);
    expect($summary['blocked'])->toBe(1);
    expect($summary['narrative'])->toBeString();
});

test('sugestoes priorizam tarefas atrasadas criticas', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $criticaAtrasada = Task::factory()->withAssignee($liderado)->vencida()->create([
        'created_by' => $gestor->id, 'priority' => 'critica', 'title' => 'Falha crítica no sistema',
    ]);
    Task::factory()->withAssignee($liderado)->create([
        'created_by' => $gestor->id, 'priority' => 'normal',
        'due_at' => now()->addDays(20), 'title' => 'Rotina tranquila',
    ]);

    $suggestions = (new AiAssistantService)->prioritySuggestions($gestor);

    expect($suggestions)->not->toBeEmpty();
    expect($suggestions[0]['title'])->toBe('Falha crítica no sistema');
    expect($suggestions[0]['reasons'][0])->toContain('Atrasada');
});

test('modo heuristico divide tarefa generica em 4 passos', function () {
    $task = Task::factory()->make(['title' => 'Implantar novo processo']);

    $steps = (new AiAssistantService)->breakdownSuggestions($task);

    expect(count($steps))->toBeGreaterThanOrEqual(3);
});

test('servico sem api key usa modo heuristico', function () {
    $assistant = new AiAssistantService(null);

    expect($assistant->usesLlm())->toBeFalse();
});

test('gestor acessa pagina do assistente com feature ativa', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->get('/assistente')
        ->assertOk()
        ->assertSee('Assistente')
        ->assertSee('Foco sugerido agora');
});

test('liderado nao acessa assistente', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)->get('/assistente')->assertForbidden();
});
