<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('aplicacao opera no fuso de sao paulo', function () {
    expect(config('app.timezone'))->toBe('America/Sao_Paulo');
});

test('timestamps gravados em horario local brasileiro', function () {
    $gestor = User::factory()->gestor()->create();

    $task = Task::factory()->create(['created_by' => $gestor->id]);

    $stored = $task->fresh()->created_at->format('H:i');
    $localNow = now()->format('H:i');

    expect(abs(strtotime($stored) - strtotime($localNow)))->toBeLessThan(120);
    expect($stored)->not->toBe(now('UTC')->format('H:i'));
});

test('historico exibe horario local na view', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)->patch("/tarefas/{$task->id}/status", ['status' => 'recebida']);

    $localHour = now()->format('H:i');
    $utcHour = now('UTC')->format('H:i');

    if ($localHour === $utcHour) {
        $this->markTestSkipped('Fuso local igual ao UTC neste momento');
    }

    $this->actingAs($gestor)->get("/tarefas/{$task->id}")
        ->assertOk()
        ->assertSee($localHour, false);
});
