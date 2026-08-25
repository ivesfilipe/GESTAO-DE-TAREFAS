<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('kanban livewire renderiza colunas e tarefas visiveis', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->emAndamento()->create(['created_by' => $gestor->id, 'title' => 'Tarefa no quadro']);

    $component = Livewire::actingAs($gestor)->test('kanban-board');

    $component->assertOk()
        ->assertSee('Quadro de Tarefas')
        ->assertSee('Sem responsável')
        ->assertSee('Em andamento')
        ->assertSee('Tarefa no quadro');
});

test('liderado ve apenas proprias tarefas no componente', function () {
    $gestor = User::factory()->gestor()->create();
    $ana = User::factory()->liderado()->create();
    $bruno = User::factory()->liderado()->create();

    Task::factory()->withAssignee($ana)->create(['created_by' => $gestor->id, 'title' => 'Da Ana']);
    Task::factory()->withAssignee($bruno)->create(['created_by' => $gestor->id, 'title' => 'Do Bruno']);

    $component = Livewire::actingAs($ana)->test('kanban-board');

    $component->assertSee('Da Ana')->assertDontSee('Do Bruno');
});

test('mover tarefa para status valido atualiza e limpa erro', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    Livewire::actingAs($liderado)->test('kanban-board')
        ->call('moveTask', (string) $task->id, 'recebida')
        ->assertSet('error', '');

    expect($task->fresh()->status)->toBe('recebida');
});

test('transicao invalida mostra mensagem amigavel sem quebrar', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    Livewire::actingAs($liderado)->test('kanban-board')
        ->call('moveTask', (string) $task->id, 'concluida')
        ->assertSet('error', 'Movimento não permitido para esta tarefa.');

    expect($task->fresh()->status)->toBe('nova');
});

test('nao responsavel nao move tarefa de outro', function () {
    $gestor = User::factory()->gestor()->create();
    $outro = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($outro)->create(['created_by' => $gestor->id]);

    Livewire::actingAs(User::factory()->liderado()->create())->test('kanban-board')
        ->call('moveTask', (string) $task->id, 'recebida');

    expect($task->fresh()->status)->toBe('nova');
});

test('filtro por responsavel restringe resultados do gestor', function () {
    $gestor = User::factory()->gestor()->create();
    $ana = User::factory()->liderado()->create();
    $bruno = User::factory()->liderado()->create();

    Task::factory()->withAssignee($ana)->create(['created_by' => $gestor->id, 'title' => 'Filtrada Ana']);
    Task::factory()->withAssignee($bruno)->create(['created_by' => $gestor->id, 'title' => 'Filtrada Bruno']);

    Livewire::actingAs($gestor)->test('kanban-board', ['assignedTo' => (string) $ana->id])
        ->assertSee('Filtrada Ana')
        ->assertDontSee('Filtrada Bruno');
});
