<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gestor adiciona comentario em tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/comentarios", ['body' => 'Comentario de teste'])
        ->assertRedirect();

    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'body' => 'Comentario de teste',
    ]);
});

test('liderado adiciona comentario na propria tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/comentarios", ['body' => 'Meu comentario'])
        ->assertRedirect();

    $this->assertDatabaseHas('comments', ['body' => 'Meu comentario']);
});

test('liderado nao ve tarefa de outro liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado1 = User::factory()->liderado()->create();
    $liderado2 = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado1)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado2)
        ->get("/tarefas/{$task->id}")
        ->assertStatus(403);
});

test('liderado nao acessa URL direta de tarefa de outro liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado1 = User::factory()->liderado()->create();
    $liderado2 = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado1)->create(['created_by' => $gestor->id]);

    $response = $this->actingAs($liderado2)->get("/tarefas/{$task->id}");
    expect($response->status())->toBe(403);
});
