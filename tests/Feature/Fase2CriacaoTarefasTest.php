<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gestor cria tarefa com campos minimos', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Tarefa de teste',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', [
        'title' => 'Tarefa de teste',
        'status' => 'nao_atribuida',
        'priority' => 'normal',
    ]);
});

test('gestor cria tarefa com responsavel', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $response = $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Tarefa com responsavel',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'assigned_to' => $liderado->id,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', [
        'title' => 'Tarefa com responsavel',
        'assigned_to' => $liderado->id,
        'status' => 'nova',
    ]);
});

test('liderado nao consegue criar tarefa', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)
        ->get('/tarefas/nova')
        ->assertStatus(403);
});

test('tarefa sem titulo eh rejeitada', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => '',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ])
        ->assertSessionHasErrors('title');
});

test('criacao de tarefa gera evento de historico', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Tarefa historica',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

    $this->assertDatabaseHas('task_history_events', [
        'event_type' => 'created',
    ]);
});

test('tarefa guarda original_due_at na criacao', function () {
    $gestor = User::factory()->gestor()->create();
    $dueAt = now()->addDays(5);

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Tarefa com prazo',
            'priority' => 'normal',
            'due_at' => $dueAt->format('Y-m-d H:i:s'),
        ]);

    $task = Task::first();
    expect($task->original_due_at->format('Y-m-d'))->toBe($dueAt->format('Y-m-d'));
    expect($task->due_at->format('Y-m-d'))->toBe($dueAt->format('Y-m-d'));
});
