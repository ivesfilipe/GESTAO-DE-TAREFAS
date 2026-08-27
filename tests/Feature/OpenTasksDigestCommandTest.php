<?php

use App\Mail\OpenTasksDigest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('envia resumo de tarefas abertas apenas para liderados ativos com tarefas', function () {
    Mail::fake();

    $gestor = User::factory()->gestor()->create();
    $lideradoComTarefas = User::factory()->liderado()->create(['email' => 'liderado1@example.com']);
    $lideradoSemTarefas = User::factory()->liderado()->create(['email' => 'liderado2@example.com']);
    $lideradoInativo = User::factory()->liderado()->create(['email' => 'liderado3@example.com', 'is_active' => false]);

    Task::factory()->withAssignee($lideradoComTarefas)->create([
        'created_by' => $gestor->id,
        'title' => 'Tarefa em aberto',
        'status' => 'em_andamento',
    ]);

    Task::factory()->withAssignee($lideradoInativo)->create([
        'created_by' => $gestor->id,
        'title' => 'Tarefa do inativo',
        'status' => 'nova',
    ]);

    $this->artisan('tarefas:enviar-resumo-tarefas-abertas')->assertSuccessful();

    Mail::assertQueued(OpenTasksDigest::class, function (OpenTasksDigest $mail) use ($lideradoComTarefas) {
        return $mail->hasTo($lideradoComTarefas->email)
            && $mail->user->id === $lideradoComTarefas->id
            && $mail->tasks->contains('title', 'Tarefa em aberto');
    });

    Mail::assertNotQueued(OpenTasksDigest::class, function (OpenTasksDigest $mail) use ($lideradoSemTarefas) {
        return $mail->hasTo($lideradoSemTarefas->email);
    });

    Mail::assertNotQueued(OpenTasksDigest::class, function (OpenTasksDigest $mail) use ($lideradoInativo) {
        return $mail->hasTo($lideradoInativo->email);
    });
});

test('nao envia resumo quando nao ha tarefas abertas', function () {
    Mail::fake();

    User::factory()->liderado()->create(['email' => 'liderado@example.com']);

    $this->artisan('tarefas:enviar-resumo-tarefas-abertas')->assertSuccessful();

    Mail::assertNothingSent();
});

test('tarefas concluidas ou canceladas nao entram no resumo', function () {
    Mail::fake();

    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id, 'status' => 'concluida', 'title' => 'Concluida']);
    Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id, 'status' => 'cancelada', 'title' => 'Cancelada']);

    $this->artisan('tarefas:enviar-resumo-tarefas-abertas')->assertSuccessful();

    Mail::assertNothingSent();
});
