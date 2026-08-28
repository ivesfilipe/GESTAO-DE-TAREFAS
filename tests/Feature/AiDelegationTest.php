<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.openai.key' => null]);
    config(['ai.default' => 'groq']);
    config(['ai.providers.groq.api_key' => null]);
});

test('gestor gera rascunho de delegacao com ia', function () {
    $gestor = User::factory()->gestor()->create();
    User::factory()->liderado()->create();

    $response = $this->actingAs($gestor)->postJson('/tarefas/delegar-com-ia', [
        'input' => 'Revisar contrato do fornecedor até sexta às 17h, urgente',
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('draft.ai_mock', true)
        ->assertJsonPath('draft.title', fn ($title) => str_contains($title, 'Revisar contrato') || str_contains($title, 'demanda'));
});

test('delegar com ia exige input', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->postJson('/tarefas/delegar-com-ia', [])
        ->assertStatus(422);
});

test('liderado nao acessa delegar com ia', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)->postJson('/tarefas/delegar-com-ia', ['input' => 'X'])
        ->assertForbidden();
});

test('rascunho de ia respeita responsavel pre-selecionado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    User::factory()->liderado()->create();

    $response = $this->actingAs($gestor)->postJson('/tarefas/delegar-com-ia', [
        'input' => 'Tarefa para revisão',
        'assigned_to' => $liderado->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('draft.recommended_assignee_id', $liderado->id);
});

test('responsavel sugerido invalido e descartado no rascunho', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $response = $this->actingAs($gestor)->postJson('/tarefas/delegar-com-ia', [
        'input' => 'Tarefa genérica',
    ]);

    $draft = $response->json('draft');
    expect($draft['recommended_assignee_id'])->toBe($liderado->id);
});

test('gestor cria tarefa com novos campos de ia', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($gestor)->post('/tarefas', [
        'title' => 'Tarefa com critérios',
        'priority' => 'normal',
        'due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        'assigned_to' => $liderado->id,
        'task_type' => 'desenvolvimento',
        'acceptance_criteria' => 'Código revisado e testado',
        'expected_evidence' => 'Print dos testes passando',
    ])->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'title' => 'Tarefa com critérios',
        'task_type' => 'desenvolvimento',
        'acceptance_criteria' => json_encode(['Código revisado e testado']),
        'expected_evidence' => json_encode(['Print dos testes passando']),
    ]);
});

test('task_type padrao é demanda quando nao enviado', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->post('/tarefas', [
        'title' => 'Tarefa padrão',
        'priority' => 'normal',
        'due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
    ])->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'title' => 'Tarefa padrão',
        'task_type' => 'demanda',
    ]);
});
