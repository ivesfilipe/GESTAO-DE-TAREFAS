<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('liderado bloqueia propria tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->emAndamento()->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/bloquear", [
            'block_reason' => 'Aguardando API externa',
            'blocked_on' => 'Equipe de TI',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'bloqueada',
        'block_reason' => 'Aguardando API externa',
        'blocked_on' => 'Equipe de TI',
    ]);
});

test('tarefa bloqueada nao conta como atrasada', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->bloqueada()->vencida()->create(['created_by' => $gestor->id]);

    expect($task->isOverdue())->toBeFalse();
});

test('gestor desbloqueia tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->bloqueada()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/desbloquear")
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'em_andamento',
    ]);
});
