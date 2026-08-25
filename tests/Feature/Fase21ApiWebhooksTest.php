<?php

use App\Models\Task;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function tokenFor(User $user): string
{
    return $user->createToken('test')->plainTextToken;
}

function authHeaders(User $user): array
{
    return ['Authorization' => 'Bearer '.tokenFor($user), 'Accept' => 'application/json'];
}

test('lista tarefas via api com token', function () {
    $gestor = User::factory()->gestor()->create();
    Task::factory()->count(3)->create(['created_by' => $gestor->id]);

    $response = $this->getJson('/api/v1/tasks', authHeaders($gestor));

    $response->assertOk();
    expect(count($response->json('data')))->toBe(3);
});

test('api exige token', function () {
    $this->getJson('/api/v1/tasks')->assertUnauthorized();
});

test('liderado ve apenas proprias tarefas na api', function () {
    $gestor = User::factory()->gestor()->create();
    $ana = User::factory()->liderado()->create();

    Task::factory()->withAssignee($ana)->create(['created_by' => $gestor->id]);
    Task::factory()->withAssignee(User::factory()->create())->create(['created_by' => $gestor->id]);

    $response = $this->getJson('/api/v1/tasks', authHeaders($ana));

    expect(count($response->json('data')))->toBe(1);
});

test('cria tarefa via api', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->postJson('/api/v1/tasks', [
        'title' => 'Tarefa via API',
        'priority' => 'urgente',
        'due_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
    ], authHeaders($gestor));

    $response->assertCreated();
    expect($response->json('data.title'))->toBe('Tarefa via API');
    $this->assertDatabaseHas('tasks', ['title' => 'Tarefa via API']);
});

test('transicao invalida via api retorna 422', function () {
    $gestor = User::factory()->gestor()->create();
    $task = Task::factory()->create(['created_by' => $gestor->id]);

    $this->patchJson("/api/v1/tasks/{$task->id}/status", ['status' => 'concluida'], authHeaders($gestor))
        ->assertStatus(422);
});

test('comentario via api dispara evento e grava historico', function () {
    Queue::fake();

    $gestor = User::factory()->gestor()->create();
    $task = Task::factory()->create(['created_by' => $gestor->id]);

    $this->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => 'Olá pela API'], authHeaders($gestor))
        ->assertCreated();

    $this->assertDatabaseHas('comments', ['body' => 'Olá pela API']);
    $this->assertDatabaseHas('task_history_events', ['event_type' => 'comment_added']);
});

test('webhook endpoint criado com secret', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->postJson('/api/v1/webhooks', [
        'url' => 'https://exemplo.com/hook',
    ], authHeaders($gestor));

    $response->assertCreated();
    expect($response->json('secret_note'))->toContain('secret');
    $this->assertDatabaseHas('webhook_endpoints', ['url' => 'https://exemplo.com/hook']);
});

test('evento de tarefa dispara webhook assinado', function () {
    Http::fake(['https://exemplo.com/hook' => Http::response(['ok' => true])]);

    $gestor = User::factory()->gestor()->create();
    WebhookEndpoint::create([
        'user_id' => $gestor->id,
        'url' => 'https://exemplo.com/hook',
        'secret' => 'segredo-teste',
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/tasks', [
        'title' => 'Dispara webhook',
        'priority' => 'normal',
        'due_at' => now()->addDay()->format('Y-m-d H:i:s'),
    ], authHeaders($gestor))->assertCreated();

    $this->artisan('queue:work --once --stop-when-empty')->assertExitCode(0);
    $endpoint = WebhookEndpoint::first();
    expect($endpoint->last_triggered_at)->not->toBeNull();
});
