<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.openai.key' => null]);
    config(['ai.default' => 'groq']);
    config(['ai.providers.groq.api_key' => null]);
    config(['ai.fallback_enabled' => false]);
});

test('gestor acessa copiloto com radar de risco', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->get('/assistente');

    $response->assertOk()
        ->assertSee('Copiloto do Gestor')
        ->assertSee('Radar prioritário')
        ->assertSee('Pergunte ao Copiloto');
});

test('gestor faz pergunta ao copiloto', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->postJson('/assistente/perguntar', [
        'question' => 'Qual o maior risco do time?',
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mock', true);
});

test('liderado nao acessa copiloto', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)->get('/assistente')->assertForbidden();
    $this->actingAs($liderado)->postJson('/assistente/perguntar', ['question' => 'X'])->assertForbidden();
});

test('copiloto mantem divisao de tarefas', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->get('/assistente?breakdown='.$task->id)
        ->assertOk()
        ->assertSee('Passos sugeridos');
});

test('copiloto exibe status da ia no topo', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->get('/assistente')
        ->assertOk()
        ->assertSee('Provider:')
        ->assertSee('ZDR:');
});

test('copiloto gera rascunho de cobranca sem enviar automaticamente', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id, 'title' => 'Tarefa atrasada']);

    $response = $this->actingAs($gestor)->postJson('/assistente/cobranca', [
        'task_id' => $task->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('draft', fn ($draft) => is_string($draft) && str_contains($draft, 'Tarefa atrasada'));
});

test('chat do copiloto responde no modo mock sem api externa', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->postJson('/assistente/perguntar', [
        'question' => 'Qual a carga do time?',
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mock', true);
});

test('copiloto carrega mesmo com tarefa sem responsavel e liderado ocioso', function () {
    $gestor = User::factory()->gestor()->create();
    User::factory()->liderado()->create();
    Task::factory()->create(['created_by' => $gestor->id, 'status' => 'nao_atribuida']);

    $this->actingAs($gestor)
        ->get('/assistente')
        ->assertOk()
        ->assertSee('Oportunidades de delegação');
});
