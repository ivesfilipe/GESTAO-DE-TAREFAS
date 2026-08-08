<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gestor acessa dashboard', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->get('/painel')
        ->assertStatus(200);
});

test('liderado nao acessa dashboard', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)
        ->get('/painel')
        ->assertStatus(403);
});

test('dashboard mostra tarefas atrasadas', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->vencida()->create(['created_by' => $gestor->id]);

    $response = $this->actingAs($gestor)->get('/painel');
    $response->assertSee('Atrasadas');
});

test('dashboard mostra tarefas aguardando aprovacao', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $response = $this->actingAs($gestor)->get('/painel');
    $response->assertSee('Aguardando');
});
