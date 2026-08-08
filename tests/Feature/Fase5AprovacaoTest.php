<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gestor aprova tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/aprovar")
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'concluida',
        'approved_by' => $gestor->id,
    ]);
    expect(Task::find($task->id)->completed_at)->not->toBeNull();
});

test('liderado nao consegue aprovar tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/aprovar")
        ->assertStatus(403);
});

test('reprovacao exige categoria', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/reprovar", [
            'rejection_category' => '',
            'rejection_note' => 'Motivo',
        ])
        ->assertSessionHasErrors('rejection_category');
});

test('reprovacao categorizada funciona', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/reprovar", [
            'rejection_category' => 'nao_atende',
            'rejection_note' => 'Nao atendeu aos requisitos',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'reprovada',
        'rejection_category' => 'nao_atende',
    ]);
});

test('aprovacao gera evento de historico', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/aprovar");

    $this->assertDatabaseHas('task_history_events', [
        'task_id' => $task->id,
        'event_type' => 'approved',
    ]);
});
