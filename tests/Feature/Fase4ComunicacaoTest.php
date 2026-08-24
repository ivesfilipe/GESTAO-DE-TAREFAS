<?php

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('gestor adiciona comentario em tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($gestor)
        ->post("/tarefas/{$task->id}/comentarios", ['body' => 'Comentario de teste'])
        ->assertRedirect();

    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'body' => 'Comentario de teste',
    ]);
});

test('liderado adiciona comentario na propria tarefa', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/comentarios", ['body' => 'Meu comentario'])
        ->assertRedirect();

    $this->assertDatabaseHas('comments', ['body' => 'Meu comentario']);
});

test('liderado nao ve tarefa de outro liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado1 = User::factory()->liderado()->create();
    $liderado2 = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado1)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado2)
        ->get("/tarefas/{$task->id}")
        ->assertStatus(403);
});

test('liderado nao acessa URL direta de tarefa de outro liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado1 = User::factory()->liderado()->create();
    $liderado2 = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado1)->create(['created_by' => $gestor->id]);

    $response = $this->actingAs($liderado2)->get("/tarefas/{$task->id}");
    expect($response->status())->toBe(403);
});

test('anexo de pdf e salvo na tarefa', function () {
    Storage::fake('anexos');

    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $file = UploadedFile::fake()->create('relatorio.pdf', 500, 'application/pdf');

    // Regressão: input da tela era name="attachment" mas a rota validava "file" -> upload nunca salvava
    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/anexos", ['file' => $file])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('attachments', [
        'task_id' => $task->id,
        'file_name' => 'relatorio.pdf',
        'file_type' => 'application/pdf',
    ]);

    Storage::disk('anexos')->assertExists(Attachment::first()->file_path);
});

test('comentario pode ser enviado junto com anexo', function () {
    Storage::fake('anexos');

    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $file = UploadedFile::fake()->create('documento.pdf', 500, 'application/pdf');

    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/comentarios", ['body' => 'Segue o documento', 'file' => $file])
        ->assertRedirect()
        ->assertSessionHas('success');

    $comment = Comment::where('task_id', $task->id)->first();
    expect($comment->body)->toBe('Segue o documento');

    $this->assertDatabaseHas('attachments', [
        'task_id' => $task->id,
        'comment_id' => $comment->id,
        'file_name' => 'documento.pdf',
    ]);
});

test('tela da tarefa exibe input de arquivo no formulario de comentario', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $task = Task::factory()->withAssignee($liderado)->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->get("/tarefas/{$task->id}")
        ->assertOk()
        ->assertSee('multipart/form-data', false)
        ->assertSee('name="file"', false);
});
