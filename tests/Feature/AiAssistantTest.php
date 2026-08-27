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

test('pergunta inocente no mock nao vira rascunho de cobranca', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->postJson('/assistente/perguntar', [
        'question' => 'Qual o maior risco do time hoje?',
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonMissing(['answer' => 'Rascunho de cobrança']);

    expect($response->json('answer'))->not->toContain('Rascunho de cobrança');
});

test('pergunta por tarefas atrasadas devolve tasks com id', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->vencida()->create([
        'created_by' => $gestor->id,
        'title' => 'Orçamento de preventiva',
    ]);

    $response = $this->actingAs($gestor)->postJson('/assistente/perguntar', [
        'question' => 'Quais tarefas estão atrasadas?',
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true);

    $ids = collect($response->json('tasks'))->pluck('id');
    expect($ids)->toContain($task->id);
});

test('abrir tarefa unica preenche open_task_id', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create([
        'created_by' => $gestor->id,
        'title' => 'Posicao do orcamento XYZ',
    ]);

    $response = $this->actingAs($gestor)->postJson('/assistente/perguntar', [
        'question' => 'Abre a tarefa Posicao do orcamento XYZ',
    ]);

    $response->assertOk()
        ->assertJsonPath('open_task_id', $task->id);
});

test('gestor ve resumo da tarefa e liderado so a propria', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $outro = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->getJson('/tarefas/'.$task->id.'/resumo')
        ->assertOk()
        ->assertJsonPath('id', $task->id)
        ->assertJsonPath('title', $task->title);

    $this->actingAs($liderado)
        ->getJson('/tarefas/'.$task->id.'/resumo')
        ->assertOk();

    $this->actingAs($outro)
        ->getJson('/tarefas/'.$task->id.'/resumo')
        ->assertForbidden();
});

test('resumo de tarefa cancelada retorna 404', function () {
    $gestor = User::factory()->gestor()->create();
    $task = Task::factory()->create([
        'created_by' => $gestor->id,
        'status' => 'cancelada',
    ]);

    $this->actingAs($gestor)
        ->getJson('/tarefas/'.$task->id.'/resumo')
        ->assertNotFound();
});

test('painel exibe atalho do copiloto', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->get('/painel')
        ->assertOk()
        ->assertSee('Pergunte ao Copiloto')
        ->assertSee('Abrir chat');
});
