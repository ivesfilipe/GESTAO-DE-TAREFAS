<?php

use App\Models\Task;
use App\Models\User;
use App\Services\AI\Safety\ZeroDataRetention;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['ai.zdr.required' => true]);
    config(['ai.zdr.confirmed' => false]);
});

test('isConfirmed retorna true quando zdr não é requerido', function () {
    config(['ai.zdr.required' => false]);

    expect((new ZeroDataRetention)->isConfirmed())->toBeTrue();
});

test('isConfirmed retorna false quando requerido e não confirmado', function () {
    expect((new ZeroDataRetention)->isConfirmed())->toBeFalse();
});

test('anonymize substitui entidades por tokens', function () {
    $zdr = new ZeroDataRetention;

    $result = $zdr->anonymize('João da Silva deve fazer isso', ['user_name_1' => 'João da Silva']);

    expect($result)->not->toContain('João da Silva')
        ->and($result)->toContain('[USER_NAME_1]');
});

test('allow bloqueia e-mails', function () {
    $zdr = new ZeroDataRetention;

    expect($zdr->allow('Contato: joao@empresa.com'))->toBeFalse();
});

test('allow permite texto sem dados sensíveis', function () {
    $zdr = new ZeroDataRetention;

    expect($zdr->allow('A tarefa está atrasada'))->toBeTrue();
});

test('entitiesFromUser extrai nome e e-mail', function () {
    $user = User::factory()->make(['name' => 'Maria', 'email' => 'maria@empresa.com']);
    $entities = (new ZeroDataRetention)->entitiesFromUser($user);

    expect($entities)->toHaveKey('user_name_'.$user->id, 'Maria')
        ->and($entities)->toHaveKey('user_email_'.$user->id, 'maria@empresa.com');
});

test('entitiesFromTask inclui assignee e creator', function () {
    $assignee = User::factory()->liderado()->create(['name' => 'Carlos', 'email' => 'carlos@empresa.com']);
    $creator = User::factory()->gestor()->create(['name' => 'Ana', 'email' => 'ana@empresa.com']);
    $task = Task::factory()->create([
        'title' => 'Tarefa secreta',
        'description' => 'Descrição secreta',
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
    ]);

    $entities = (new ZeroDataRetention)->entitiesFromTask($task);

    expect($entities)->toHaveKey('task_title_'.$task->id, 'Tarefa secreta')
        ->and($entities)->toHaveKey('user_name_'.$assignee->id, 'Carlos')
        ->and($entities)->toHaveKey('user_name_'.$creator->id, 'Ana');
});
