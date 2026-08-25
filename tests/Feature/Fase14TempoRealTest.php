<?php

use App\Broadcasting\TaskChannel;
use App\Events\StatusAlterado;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\Support\RecordingBroadcaster;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->singleton(RecordingBroadcaster::class);
    Broadcast::extend('recording', fn () => app(RecordingBroadcaster::class));
    config(['broadcasting.default' => 'recording', 'broadcasting.connections.recording' => ['driver' => 'recording']]);
    $this->recorder = app(RecordingBroadcaster::class);
});

test('eventos de dominio implementam broadcast', function () {
    $gestor = User::factory()->gestor()->create();

    $event = new StatusAlterado(Task::factory()->make(['created_by' => $gestor->id]), $gestor, 'nova', 'recebida');

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
});

test('criar tarefa transmite nos canais task e user', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($gestor)->post('/tarefas', [
        'title' => 'Tarefa em tempo real',
        'priority' => 'normal',
        'due_at' => now()->addDays(2)->format('Y-m-d H:i'),
        'assigned_to' => $liderado->id,
    ]);

    expect($this->recorder->pushedTo('private-task.'))->toBeTrue();
    expect($this->recorder->pushedTo('private-user.'.$liderado->id))->toBeTrue();
    expect($this->recorder->pushedTo('private-user.'.$gestor->id))->toBeTrue();

    $broadcastEvent = collect($this->recorder->broadcasts)->firstWhere('event', 'task.tarefa_criada');
    expect($broadcastEvent)->not->toBeNull();
});

test('alterar status transmite evento com nome padronizado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->patch("/tarefas/{$task->id}/status", ['status' => 'recebida']);

    $names = collect($this->recorder->broadcasts)->pluck('event');

    expect($names)->toContain('task.status_alterado');
});

test('payload de broadcast contém dados essenciais', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $event = new StatusAlterado($task, $liderado, 'nova', 'em_andamento');
    $payload = $event->broadcastWith();

    expect($payload['id'])->toBe($task->id);
    expect($payload['title'])->toBe($task->title);
    expect($payload['actor_name'])->toBe($liderado->name);
    expect($event->broadcastAs())->toBe('task.status_alterado');
});

test('tarefa sem responsavel nao cria canal user do assignee', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->post('/tarefas', [
        'title' => 'Sem responsável',
        'priority' => 'normal',
        'due_at' => now()->addDay()->format('Y-m-d H:i'),
    ]);

    $channels = collect($this->recorder->broadcasts)
        ->filter(fn ($b) => str_starts_with($b['channel'], 'private-task.'))
        ->count();

    expect($channels)->toBeGreaterThan(0);
});

test('autorizacao de canal permite apenas envolvidos e gestor', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $outsider = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $channel = new TaskChannel;

    expect($channel->join($liderado, $task->id))->toBeTrue();
    expect($channel->join($gestor, $task->id))->toBeTrue();
    expect($channel->join($outsider, $task->id))->toBeFalse();
    expect($channel->join($liderado, 999999))->toBeFalse();
});
