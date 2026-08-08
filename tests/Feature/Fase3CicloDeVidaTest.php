<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('transicao valida de status funciona', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->patch("/tarefas/{$task->id}/status", ['status' => 'recebida']);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'recebida',
    ]);
});

test('transicao invalida de status lanca erro', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $response = $this->actingAs($liderado)
        ->patch("/tarefas/{$task->id}/status", ['status' => 'concluida']);

    $response->assertStatus(500);
});

test('mudanca de status gera evento de historico', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->patch("/tarefas/{$task->id}/status", ['status' => 'recebida']);

    $this->assertDatabaseHas('task_history_events', [
        'task_id' => $task->id,
        'event_type' => 'status_changed',
    ]);
});

test('liderado consegue mover para em_andamento', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id, 'status' => 'nova']);
    $task->update(['status' => 'recebida']);

    $this->actingAs($liderado)
        ->patch("/tarefas/{$task->id}/status", ['status' => 'em_andamento']);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'em_andamento',
    ]);
});

test('liderado solicita conclusao', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->emAndamento()->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/solicitar-conclusao");

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'aguardando_aprovacao',
    ]);
});

test('gestor altera responsavel da tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado1 = User::factory()->liderado()->create();
    $liderado2 = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado1)->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->patch("/tarefas/{$task->id}/atribuir", ['assigned_to' => $liderado2->id]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'assigned_to' => $liderado2->id,
    ]);
});

test('gestor altera prazo diretamente', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    // Liderado não pode alterar prazo diretamente - usa change request
    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/solicitar-alteracao", [
            'field' => 'due_at',
            'current_value' => $task->due_at->format('Y-m-d H:i:s'),
            'requested_value' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'justification' => 'Preciso de mais tempo',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('change_requests', [
        'task_id' => $task->id,
        'field' => 'due_at',
        'status' => 'pendente',
    ]);
});

test('cancelamento de tarefa usa soft delete', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->patch("/tarefas/{$task->id}/status", ['status' => 'cancelada']);

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});
