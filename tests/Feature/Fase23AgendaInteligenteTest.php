<?php

use App\Models\Task;
use App\Models\User;
use App\Services\AutoSchedulingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->fridayMorning = CarbonImmutable::parse('2026-08-28 08:00');
});

test('aloca primeiro bloco no inicio do expediente', function () {
    $gestor = User::factory()->gestor()->create();
    Task::factory()->withAssignee(User::factory()->create())->create([
        'created_by' => $gestor->id,
        'priority' => 'normal',
        'due_at' => now()->addDays(5),
    ]);

    $blocks = (new AutoSchedulingService)->propose($gestor, $this->fridayMorning);

    expect(count($blocks))->toBe(1);
    expect($blocks[0]['start']->format('Y-m-d H:i'))->toBe('2026-08-28 09:00');
    expect($blocks[0]['end']->format('H:i'))->toBe('10:00');
});

test('tarefa maior que janela cai em janela posterior que caiba', function () {
    $gestor = User::factory()->gestor()->create();
    Task::factory()->withAssignee(User::factory()->create())->create([
        'created_by' => $gestor->id,
        'estimated_minutes' => 300,
        'due_at' => now()->addDays(5),
    ]);

    $blocks = (new AutoSchedulingService)->propose($gestor, $this->fridayMorning);

    expect($blocks[0]['start']->format('Y-m-d H:i'))->toBe('2026-08-28 13:00');
    expect($blocks[0]['end']->format('H:i'))->toBe('18:00');
});

test('pula fim de semana', function () {
    $gestor = User::factory()->gestor()->create();
    Task::factory()->withAssignee(User::factory()->create())->create([
        'created_by' => $gestor->id,
        'due_at' => now()->addDays(5),
    ]);

    $saturday = CarbonImmutable::parse('2026-08-29 10:00');

    $blocks = (new AutoSchedulingService)->propose($gestor, $saturday);

    expect($blocks[0]['start']->format('Y-m-d'))->toBe('2026-08-31');
});

test('prioriza criticas e atrasadas antes de normais', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->create([
        'created_by' => $gestor->id, 'priority' => 'normal',
        'title' => 'Tarefa normal', 'due_at' => now()->addDay(),
    ]);
    Task::factory()->withAssignee($liderado)->vencida()->critica()->create([
        'created_by' => $gestor->id, 'title' => 'Crítica atrasada',
    ]);

    $blocks = (new AutoSchedulingService)->propose($gestor, $this->fridayMorning);

    expect($blocks[0]['title'])->toBe('Crítica atrasada');
});

test('aplicar salva scheduled_start nas tarefas', function () {
    $gestor = User::factory()->gestor()->create();
    $task = Task::factory()->withAssignee(User::factory()->create())->create(['created_by' => $gestor->id]);

    $applied = (new AutoSchedulingService)->apply($gestor, [
        ['task_id' => $task->id, 'start' => '2026-08-28 09:00:00'],
    ]);

    expect($applied)->toBe(1);
    expect($task->fresh()->scheduled_start->format('Y-m-d H:i'))->toBe('2026-08-28 09:00');
});

test('tarefas ja agendadas nao entram na nova proposta', function () {
    $gestor = User::factory()->gestor()->create();
    Task::factory()->withAssignee(User::factory()->create())->create([
        'created_by' => $gestor->id,
        'scheduled_start' => '2026-08-28 09:00:00',
        'due_at' => now()->addDay(),
    ]);

    $blocks = (new AutoSchedulingService)->propose($gestor, $this->fridayMorning);

    expect($blocks)->toBeEmpty();
});

test('pagina agenda inteligente acessivel para gestor', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->get('/agenda-inteligente')
        ->assertOk()
        ->assertSee('Agenda Inteligente');
});

test('liderado bloqueado pela feature flag', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)->get('/agenda-inteligente')->assertForbidden();
});
