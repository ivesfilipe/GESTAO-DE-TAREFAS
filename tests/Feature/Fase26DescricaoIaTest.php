<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('gera descricao heuristica sem api key citando o titulo', function () {
    config(['services.openai.key' => null]);
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->postJson('/tarefas/descricao', [
        'title' => 'Auditoria do compressor central',
        'priority' => 'urgente',
    ]);

    $response->assertOk();
    $description = $response->json('description');

    expect($description)->toContain('Auditoria do compressor central');
    expect($description)->toContain('urgência');
    expect(str_word_count($description))->toBeGreaterThan(30);
});

test('gera descricao via llm quando api key presente', function () {
    config(['ai.default' => 'openai']);
    config(['services.openai.key' => 'chave-teste']);
    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [['message' => ['content' => "Briefing do diretor:\nObjetivo claro e cena montada.\n- Entrega A\n- Entrega B"]]],
        ]),
    ]);

    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->postJson('/tarefas/descricao', [
        'title' => 'Campanha de manutenção preventiva',
    ]);

    $response->assertOk();
    expect($response->json('description'))->toContain('Briefing do diretor');
    Http::assertSent(fn ($request) => str_contains($request->url(), 'api.openai.com'));
});

test('liderado nao gera descricao', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)->postJson('/tarefas/descricao', ['title' => 'X'])
        ->assertForbidden();
});

test('titulo obrigatorio', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->postJson('/tarefas/descricao', ['title' => ''])
        ->assertStatus(422);
});

test('fallback heuristico quando llm falha', function () {
    config(['ai.default' => 'openai']);
    config(['services.openai.key' => 'chave-teste']);
    Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'rate limited']], 429)]);

    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->postJson('/tarefas/descricao', [
        'title' => 'Tarefa com LLM fora do ar',
    ]);

    $response->assertOk();
    expect($response->json('description'))->toContain('Tarefa com LLM fora do ar');
});
