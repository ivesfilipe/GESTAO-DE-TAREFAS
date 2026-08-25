<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('calendario renderiza mes corrente com tarefas', function () {
    $gestor = User::factory()->gestor()->create();
    $task = Task::factory()->create([
        'title' => 'Tarefa do calendário',
        'created_by' => $gestor->id,
        'due_at' => now()->startOfMonth()->addDays(5)->setTime(14, 0),
    ]);

    $response = $this->actingAs($gestor)->get('/calendario');

    $response->assertOk()
        ->assertSee('Tarefa do calendário')
        ->assertSee(now()->month);
});

test('liderado ve apenas proprias tarefas no calendario', function () {
    $gestor = User::factory()->gestor()->create();
    $ana = User::factory()->liderado()->create();
    $bruno = User::factory()->liderado()->create();

    Task::factory()->withAssignee($ana)->create(['title' => 'Tarefa da Ana', 'due_at' => now()->setTime(10, 0)]);
    Task::factory()->withAssignee($bruno)->create(['title' => 'Tarefa do Bruno', 'due_at' => now()->setTime(11, 0)]);

    $this->actingAs($ana)->get('/calendario')
        ->assertSee('Tarefa da Ana')
        ->assertDontSee('Tarefa do Bruno');
});

test('navegacao entre meses funciona', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->get('/calendario?mes=2027-01')
        ->assertSee('Janeiro de 2027');
});

test('feed ical retorna eventos com token valido', function () {
    $user = User::factory()->create();
    $task = Task::factory()->withAssignee($user)->create([
        'title' => 'Evento no feed',
        'due_at' => now()->setTime(15, 0),
    ]);

    $response = $this->get("/calendario/{$user->calendar_token}.ics");

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/calendar');
    expect($response->getContent())->toContain('BEGIN:VCALENDAR');
    expect($response->getContent())->toContain('SUMMARY:Evento no feed');
    expect($response->getContent())->toContain('UID:task-'.$task->id);
});

test('token invalido retorna 404', function () {
    $this->get('/calendario/token-invalido.ics')->assertNotFound();
});

test('usuario novo recebe token automaticamente', function () {
    $user = User::factory()->create();

    expect($user->calendar_token)->not->toBeNull();
});
