<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('criacao de tarefa gera registro de historico', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Tarefa historica',
            'priority' => 'urgente',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

    $this->assertDatabaseHas('task_history_events', [
        'event_type' => 'created',
    ]);
});

test('atribuicao gera registro de historico', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->create(['created_by' => $gestor->id, 'status' => 'nao_atribuida']);

    $this->actingAs($gestor)
        ->patch("/tarefas/{$task->id}/atribuir", ['assigned_to' => $liderado->id]);

    $this->assertDatabaseHas('task_history_events', [
        'task_id' => $task->id,
        'event_type' => 'assigned',
    ]);
});

test('mudanca de status gera registro de historico', function () {
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

test('comentario gera registro de historico', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/comentarios", ['body' => 'Comentario']);

    $this->assertDatabaseHas('task_history_events', [
        'task_id' => $task->id,
        'event_type' => 'comment_added',
    ]);
});

test('historico eh imutavel - tabela nao permite update', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Outra tarefa',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

    $event = \App\Models\TaskHistoryEvent::first();
    expect($event->getAttributes())->toHaveKey('event_type');
    expect($event->event_type)->toBeString();
});
