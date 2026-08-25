<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gestor interpreta texto em linguagem natural', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->postJson('/tarefas/interpretar', [
        'text' => 'Reunião com o time amanhã às 15h urgente',
    ]);

    $response->assertOk();
    expect($response->json('ok'))->toBeTrue();
    expect($response->json('title'))->toBe('Reunião com o time');
    expect($response->json('priority'))->toBe('urgente');
    expect($response->json('due_at_label'))->toMatch('/\d{2}\/\d{2}\/\d{4} 15:00/');
});

test('texto sem titulo retorna 422 com mensagem', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->postJson('/tarefas/interpretar', ['text' => 'urgente amanhã'])
        ->assertStatus(422);
});

test('texto vazio falha validacao', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->postJson('/tarefas/interpretar', ['text' => ''])
        ->assertStatus(422);
});

test('liderado nao pode interpretar tarefas', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)->postJson('/tarefas/interpretar', [
        'text' => 'Tarefa qualquer amanhã',
    ])->assertForbidden();
});

test('fluxo completo interpretar e criar tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $tomorrow = now()->addDay()->format('Y-m-d');

    $parsed = $this->actingAs($gestor)->postJson('/tarefas/interpretar', [
        'text' => 'Manutenção preventiva amanhã às 09:00 importante',
    ])->json();

    $this->actingAs($gestor)->post('/tarefas', [
        'title' => $parsed['title'],
        'priority' => $parsed['priority'],
        'due_at' => str_replace('00:00', '09:00', $tomorrow).' 09:00',
    ])->assertRedirect();

    $this->assertDatabaseHas('tasks', ['title' => 'Manutenção preventiva']);
});
