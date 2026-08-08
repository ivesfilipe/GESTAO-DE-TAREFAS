<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('liderado acessa portal minhas tarefas', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)
        ->get('/minhas-tarefas')
        ->assertStatus(200);
});

test('gestor acessa minhas tarefas e ve suas proprias', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->get('/minhas-tarefas')
        ->assertStatus(200);
});

test('liderado ve apenas as proprias tarefas no portal', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado1 = User::factory()->liderado()->create();
    $liderado2 = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado1)->create(['created_by' => $gestor->id, 'title' => 'Tarefa do Liderado 1']);

    $response = $this->actingAs($liderado2)->get('/minhas-tarefas');
    $response->assertDontSee('Tarefa do Liderado 1');
});
