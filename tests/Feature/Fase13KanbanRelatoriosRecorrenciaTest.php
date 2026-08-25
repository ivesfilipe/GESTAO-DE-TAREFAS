<?php

use App\Actions\CreateTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// --- Kanban ---

test('gestor acessa o quadro kanban com todas as colunas', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->emAndamento()->create(['created_by' => $gestor->id]);
    Task::factory()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->get('/tarefas/quadro')
        ->assertOk()
        ->assertSee('Quadro de Tarefas')
        ->assertSee('Sem responsável')
        ->assertSee('Em andamento')
        ->assertSee('Aguardando aprovação');
});

test('liderado ve apenas proprias tarefas no quadro', function () {
    $gestor = User::factory()->gestor()->create();
    $ana = User::factory()->liderado()->create(['name' => 'Ana Kanban']);
    $bruno = User::factory()->liderado()->create(['name' => 'Bruno Kanban']);

    Task::factory()->withAssignee($ana)->emAndamento()->create(['created_by' => $gestor->id, 'title' => 'Tarefa da Ana']);
    Task::factory()->withAssignee($bruno)->emAndamento()->create(['created_by' => $gestor->id, 'title' => 'Tarefa do Bruno']);

    $this->actingAs($ana)
        ->get('/tarefas/quadro')
        ->assertOk()
        ->assertSee('Tarefa da Ana')
        ->assertDontSee('Tarefa do Bruno');
});

test('kanban move card via json respeitando maquina de estados', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->patchJson("/tarefas/{$task->id}/status", ['status' => 'recebida'])
        ->assertOk()
        ->assertJson(['ok' => true, 'status' => 'recebida']);
});

test('kanban retorna erro amigavel em json para transicao ilegal', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->patchJson("/tarefas/{$task->id}/status", ['status' => 'concluida'])
        ->assertStatus(422)
        ->assertJsonPath('ok', false);
});

test('gestor aprova tarefa via json no kanban', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->postJson("/tarefas/{$task->id}/aprovar")
        ->assertOk()
        ->assertJson(['ok' => true, 'status' => 'concluida']);

    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'concluida']);
});

// --- Relatórios ---

test('gestor acessa relatorios', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->get('/relatorios')
        ->assertOk()
        ->assertSee('Relatórios de Desempenho');
});

test('liderado nao acessa relatorios', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)
        ->get('/relatorios')
        ->assertForbidden();
});

test('relatorios calcula metricas do periodo', function () {
    $gestor = User::factory()->gestor()->create();
    $ana = User::factory()->liderado()->create(['name' => 'Ana Metricas']);

    Task::factory()->withAssignee($ana)->create([
        'created_by' => $gestor->id,
        'title' => 'Concluida no prazo',
        'due_at' => now()->subDays(4),
        'original_due_at' => now()->subDays(4),
        'completed_at' => now()->subDays(5),
        'approved_by' => $gestor->id,
    ]);
    Task::factory()->withAssignee($ana)->create([
        'created_by' => $gestor->id,
        'title' => 'Concluida atrasada',
        'due_at' => now()->subDays(6),
        'original_due_at' => now()->subDays(6),
        'completed_at' => now()->subDays(2),
        'approved_by' => $gestor->id,
    ]);
    Task::factory()->withAssignee($ana)->create([
        'created_by' => $gestor->id,
        'status' => 'reprovada',
        'rejection_category' => 'nao_atende',
        'rejection_note' => 'refazer',
    ]);

    $response = $this->actingAs($gestor)->get('/relatorios?periodo=30');

    $response->assertOk()
        ->assertSee('Ana Metricas')
        ->assertSee('Concluídas (aprovadas)')
        ->assertSee('Não atende aos requisitos');

    $response->assertSee('2'); // concluídas
    $response->assertSee('1'); // reprovadas
});

// --- Tarefas recorrentes ---

test('gestor cria tarefa recorrente semanal', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Checklist semanal de equipamentos',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i'),
            'assigned_to' => $liderado->id,
            'recurrence_frequency' => 'semanal',
        ])
        ->assertRedirect();

    $task = Task::where('title', 'Checklist semanal de equipamentos')->first();

    expect($task->isRecurring())->toBeTrue()
        ->and($task->recurrence_frequency)->toBe('semanal')
        ->and($task->recurrence_series_id)->not->toBeNull()
        ->and(abs($task->recurrence_next_at->diffInDays($task->due_at)))->toBe(7.0);
});

test('gerador cria proxima instancia quando vence a cadencia', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    (new CreateTask)->execute($gestor, [
        'title' => 'Backup mensal do sistema',
        'priority' => 'importante',
        'due_at' => now()->subDays(32)->format('Y-m-d H:i:s'),
        'assigned_to' => $liderado->id,
        'recurrence_frequency' => 'mensal',
        'recurrence_next_at' => now()->subDay()->format('Y-m-d H:i:s'),
        'recurrence_series_id' => 'serie-xyz',
    ]);

    $this->artisan('tarefas:gerar-recorrentes')->assertSuccessful();

    $instancias = Task::where('title', 'Backup mensal do sistema')->orderBy('id')->get();

    expect($instancias)->toHaveCount(2);

    $nova = $instancias->last();

    expect($nova->status)->toBe('nova')
        ->and($nova->recurrence_series_id)->toBe('serie-xyz')
        ->and($nova->recurrence_next_at->isFuture())->toBeTrue();

    $original = $instancias->first();
    expect($original->fresh()->recurrence_next_at)->toBeNull();

    $this->assertDatabaseHas('task_history_events', [
        'task_id' => $nova->id,
        'event_type' => 'created',
    ]);
});

test('gerador nao duplica enquanto a proxima nao vence', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    (new CreateTask)->execute($gestor, [
        'title' => 'Inspeção quinzenal',
        'priority' => 'normal',
        'due_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
        'assigned_to' => $liderado->id,
        'recurrence_frequency' => 'quinzenal',
        'recurrence_next_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        'recurrence_series_id' => 'serie-abc',
    ]);

    $this->artisan('tarefas:gerar-recorrentes')->assertSuccessful();

    expect(Task::where('title', 'Inspeção quinzenal')->count())->toBe(1);
});

test('gerador atualiza cadencia atrasada sem criar instancias no passado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    (new CreateTask)->execute($gestor, [
        'title' => 'Rotina diaria de leituras',
        'priority' => 'normal',
        'due_at' => now()->subDays(10)->format('Y-m-d H:i:s'),
        'assigned_to' => $liderado->id,
        'recurrence_frequency' => 'diaria',
        'recurrence_next_at' => now()->subDays(9)->format('Y-m-d H:i:s'),
        'recurrence_series_id' => 'serie-diaria',
    ]);

    $this->artisan('tarefas:gerar-recorrentes')->assertSuccessful();

    $instancias = Task::where('title', 'Rotina diaria de leituras')->orderBy('id')->get();

    expect($instancias)->toHaveCount(2);

    $nova = $instancias->last();

    expect($nova->recurrence_next_at->isFuture())->toBeTrue()
        ->and($nova->recurrence_next_at->diffInDays(now()))->toBeLessThan(2.0);
});

test('tarefa recorrente exibe badge no detalhe e na listagem', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = (new CreateTask)->execute($gestor, [
        'title' => 'Manutencao recorrente visivel',
        'priority' => 'normal',
        'due_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        'assigned_to' => $liderado->id,
        'recurrence_frequency' => 'semanal',
        'recurrence_next_at' => now()->addDays(12)->format('Y-m-d H:i:s'),
        'recurrence_series_id' => 'serie-badge',
    ]);

    $this->actingAs($gestor)
        ->get("/tarefas/{$task->id}")
        ->assertOk()
        ->assertSee('Semanal');

    $this->actingAs($gestor)
        ->get('/tarefas')
        ->assertOk()
        ->assertSee('Manutencao recorrente visivel');
});
