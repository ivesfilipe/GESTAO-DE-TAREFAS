<?php

use App\Models\Task;
use App\Models\User;
use App\Notifications\NovaTarefaNotification;
use App\Notifications\PrazoProximoNotification;
use App\Notifications\TarefaAprovadaNotification;
use App\Notifications\TarefaAtrasadaNotification;
use App\Notifications\TarefaReprovadaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('liderado nao responsavel nao altera status de tarefa alheia', function () {
    $gestor = User::factory()->gestor()->create();
    $responsavel = User::factory()->liderado()->create();
    $intruso = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($responsavel)->emAndamento()->create(['created_by' => $gestor->id]);

    // Regressão IDOR: qualquer autenticado conseguia mover/cancelar tarefa alheia
    $this->actingAs($intruso)
        ->from('/tarefas')
        ->patch("/tarefas/{$task->id}/status", ['status' => 'aguardando_aprovacao'])
        ->assertForbidden();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'em_andamento',
    ]);
});

test('liderado nao pode cancelar tarefa nem propria', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->from('/tarefas')
        ->patch("/tarefas/{$task->id}/status", ['status' => 'cancelada'])
        ->assertForbidden();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'nova',
        'deleted_at' => null,
    ]);
});

test('gestor continua podendo cancelar tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->patch("/tarefas/{$task->id}/status", ['status' => 'cancelada'])
        ->assertRedirect();

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});

test('liderado responsavel continua movendo proprio status', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->patch("/tarefas/{$task->id}/status", ['status' => 'recebida'])
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'recebida',
    ]);
});

test('botao desbloquear aponta para rota correta', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->bloqueada()->create(['created_by' => $gestor->id]);

    // Regressão: botão Desbloquear chamava tasks.approve e gerava 500
    $this->actingAs($gestor)
        ->get("/tarefas/{$task->id}")
        ->assertOk()
        ->assertSee('/tarefas/'.$task->id.'/desbloquear')
        ->assertDontSee('<form method="POST" action="'.route('tasks.approve', $task).'">', false);
});

test('modal de atribuir exibe liderados ativos', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create(['name' => 'Maria Souza']);

    $task = Task::factory()->create(['created_by' => $gestor->id, 'status' => 'nao_atribuida']);

    // Regressão: modal sempre vazio pois $liderados não era repassado à view
    $this->actingAs($gestor)
        ->get("/tarefas/{$task->id}")
        ->assertOk()
        ->assertSee('Maria Souza');
});

test('filtro de responsavel na listagem exibe membros da equipe', function () {
    $gestor = User::factory()->gestor()->create();
    User::factory()->liderado()->create(['name' => 'Joao Filtrado']);

    // Regressão: filtro sempre vazio pois $teamMembers não era repassado à view
    $this->actingAs($gestor)
        ->get('/tarefas')
        ->assertOk()
        ->assertSee('Joao Filtrado');
});

test('criacao de tarefa atribuida notifica responsavel', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Tarefa notificavel',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i'),
            'assigned_to' => $liderado->id,
        ])
        ->assertRedirect();

    $notification = $liderado->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->type)->toBe(NovaTarefaNotification::class)
        ->and($notification->data['task_id'])->toBeInt();
});

test('tarefa sem responsavel nao gera notificacao ate ser atribuida', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Sem dono',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i'),
        ])
        ->assertRedirect();

    expect($liderado->notifications()->count())->toBe(0);

    $task = Task::where('title', 'Sem dono')->first();

    $this->actingAs($gestor)
        ->patch("/tarefas/{$task->id}/atribuir", ['assigned_to' => $liderado->id])
        ->assertRedirect();

    $liderado->refresh();

    expect($liderado->notifications()->count())->toBe(1)
        ->and($liderado->notifications()->first()->type)->toBe(NovaTarefaNotification::class);
});

test('comentario notifica criador e responsavel mas nao o autor', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->emAndamento()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/comentarios", ['body' => 'Alguma novidade?'])
        ->assertRedirect();

    expect($liderado->notifications()->count())->toBe(1)
        ->and($gestor->notifications()->count())->toBe(0);

    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/comentarios", ['body' => 'Tudo certo!'])
        ->assertRedirect();

    expect($gestor->notifications()->count())->toBe(1)
        ->and($liderado->notifications()->count())->toBe(1);
});

test('aprovacao notifica responsavel', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/aprovar")
        ->assertRedirect();

    expect($liderado->notifications()->count())->toBe(1)
        ->and($liderado->notifications()->first()->type)->toBe(TarefaAprovadaNotification::class);
});

test('reprovacao notifica responsavel', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->aguardandoAprovacao()->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/reprovar", [
            'rejection_category' => 'nao_atende',
            'rejection_note' => 'Faltou o relatorio X',
        ])
        ->assertRedirect();

    expect($liderado->notifications()->count())->toBe(1)
        ->and($liderado->notifications()->first()->type)->toBe(TarefaReprovadaNotification::class);
});

test('usuario inativo nao recebe notificacoes de tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create(['is_active' => false]);

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Para inativo',
            'priority' => 'normal',
            'due_at' => now()->addDays(3)->format('Y-m-d H:i'),
            'assigned_to' => $liderado->id,
        ])
        ->assertRedirect();

    expect($liderado->notifications()->count())->toBe(0);
});

test('comando notifica prazos proximos apenas', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->emAndamento()->venceHoje()->create(['created_by' => $gestor->id]);
    Task::factory()->withAssignee($liderado)->emAndamento()->create([
        'created_by' => $gestor->id,
        'due_at' => now()->addDays(5),
        'original_due_at' => now()->addDays(5),
    ]);

    $this->artisan('tarefas:notificar-prazos-proximos')->assertSuccessful();

    expect($liderado->notifications()->count())->toBe(1)
        ->and($liderado->notifications()->first()->type)->toBe(PrazoProximoNotification::class);
});

test('comando notifica tarefas atrasadas exceto concluidas e bloqueadas', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->emAndamento()->vencida()->create(['created_by' => $gestor->id]);
    Task::factory()->withAssignee($liderado)->concluida()->vencida()->create(['created_by' => $gestor->id]);
    Task::factory()->withAssignee($liderado)->bloqueada()->vencida()->create(['created_by' => $gestor->id]);

    $this->artisan('tarefas:notificar-atrasadas')->assertSuccessful();

    expect($liderado->notifications()->count())->toBe(1)
        ->and($liderado->notifications()->first()->type)->toBe(TarefaAtrasadaNotification::class);
});

test('comando de atrasadas ignora usuario inativo', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create(['is_active' => false]);

    Task::factory()->withAssignee($liderado)->emAndamento()->vencida()->create(['created_by' => $gestor->id]);

    $this->artisan('tarefas:notificar-atrasadas')->assertSuccessful();

    expect($liderado->notifications()->count())->toBe(0);
});
