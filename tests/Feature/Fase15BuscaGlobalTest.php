<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gestor busca tarefas por titulo', function () {
    $gestor = User::factory()->gestor()->create();
    Task::factory()->create(['title' => 'Manutenção do chiller', 'created_by' => $gestor->id]);
    Task::factory()->create(['title' => 'Relatório mensal', 'created_by' => $gestor->id]);

    $response = $this->actingAs($gestor)->getJson('/busca?q=chiller');

    $response->assertOk();
    $results = $response->json('results');
    expect($results)->toHaveCount(1);
    expect($results[0]['title'])->toBe('Manutenção do chiller');
    expect($results[0]['url'])->toBe('/tarefas/1');
});

test('liderado so ve tarefas visiveis na busca', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $outro = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->create(['title' => 'Inspeção elétrica']);
    Task::factory()->withAssignee($outro)->create(['title' => 'Inspeção hidráulica']);

    $response = $this->actingAs($liderado)->getJson('/busca?q=inspe%C3%A7%C3%A3o');

    $response->assertOk();
    $titles = collect($response->json('results'))->pluck('title');
    expect($titles)->toContain('Inspeção elétrica');
    expect($titles)->not->toContain('Inspeção hidráulica');
});

test('busca por descricao encontra tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    Task::factory()->create([
        'title' => 'Ordem 1234',
        'description' => 'Verificar compressores do setor B',
        'created_by' => $gestor->id,
    ]);

    $response = $this->actingAs($gestor)->getJson('/busca?q=compressores');

    $response->assertOk();
    expect($response->json('results'))->toHaveCount(1);
});

test('termo curto retorna erro de validacao', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->getJson('/busca?q=a')->assertStatus(422);
});

test('visitante nao acessa busca', function () {
    $this->getJson('/busca?q=qualquer')->assertUnauthorized();
});
