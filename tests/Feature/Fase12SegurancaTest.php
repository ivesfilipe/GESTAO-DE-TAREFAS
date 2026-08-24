<?php

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('login bloqueado apos muitas tentativas falhas', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $i) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ])->assertSessionHasErrors();
    }

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

test('token de convite nao fica armazenado em texto puro', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)
        ->post('/equipe', [
            'name' => 'Liderado Seguro',
            'email' => 'seguro@exemplo.com',
            'role' => 'liderado',
        ]);

    $link = $response->getSession()->get('invite_link');
    $token = Str::after($link, '/convite/');

    expect(strlen($token))->toBe(64);

    $this->assertDatabaseMissing('password_reset_tokens', [
        'token' => $token,
    ]);

    $record = DB::table('password_reset_tokens')->where('email', 'seguro@exemplo.com')->first();

    expect($record->token)->not->toBe($token)
        ->and($record->token)->toBe(hash('sha256', $token));
});

test('link de convite continua funcionando com token hasheado', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)
        ->post('/equipe', [
            'name' => 'Liderado Fluxo',
            'email' => 'fluxo@exemplo.com',
            'role' => 'liderado',
        ]);

    $link = $response->getSession()->get('invite_link');
    $token = Str::after($link, '/convite/');

    $this->get("/convite/{$token}")
        ->assertOk()
        ->assertSee('fluxo@exemplo.com');

    $this->post("/convite/{$token}", [
        'password' => 'novaSenha123',
        'password_confirmation' => 'novaSenha123',
    ])
        ->assertRedirect('/login')
        ->assertSessionHas('success');

    $this->assertCredentials([
        'email' => 'fluxo@exemplo.com',
        'password' => 'novaSenha123',
    ]);
});

test('download de anexo exige visualizacao da tarefa', function () {
    Storage::fake('anexos');

    $gestor = User::factory()->gestor()->create();
    $responsavel = User::factory()->liderado()->create();
    $terceiro = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($responsavel)->emAndamento()->create(['created_by' => $gestor->id]);

    $attachment = Attachment::create([
        'task_id' => $task->id,
        'uploaded_by' => $responsavel->id,
        'file_path' => 'attachments/teste.pdf',
        'file_name' => 'documento.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 100,
        'created_at' => now(),
    ]);

    Storage::disk('anexos')->put('attachments/teste.pdf', 'conteudo-secreto');

    // Responsável consegue baixar
    $this->actingAs($responsavel)
        ->get("/tarefas/{$task->id}/anexos/{$attachment->id}")
        ->assertOk();

    // Gestor consegue baixar
    $this->actingAs($gestor)
        ->get("/tarefas/{$task->id}/anexos/{$attachment->id}")
        ->assertOk();

    // Terceiro sem vínculo é bloqueado
    $this->actingAs($terceiro)
        ->get("/tarefas/{$task->id}/anexos/{$attachment->id}")
        ->assertForbidden();
});

test('visitante é redirecionado ao login ao baixar anexo', function () {
    Storage::fake('anexos');

    $gestor = User::factory()->gestor()->create();
    $responsavel = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($responsavel)->emAndamento()->create(['created_by' => $gestor->id]);

    $attachment = Attachment::create([
        'task_id' => $task->id,
        'uploaded_by' => $responsavel->id,
        'file_path' => 'attachments/teste.pdf',
        'file_name' => 'documento.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 100,
        'created_at' => now(),
    ]);

    Storage::disk('anexos')->put('attachments/teste.pdf', 'conteudo-secreto');

    $this->get("/tarefas/{$task->id}/anexos/{$attachment->id}")
        ->assertRedirect(route('login'));
});

test('anexos nao ficam acessiveis no disco publico', function () {
    Storage::fake('anexos');
    Storage::fake('public');

    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $task = Task::factory()->withAssignee($liderado)->emAndamento()->create(['created_by' => $gestor->id]);

    $this->actingAs($liderado)
        ->post("/tarefas/{$task->id}/anexos", [
            'file' => UploadedFile::fake()->create('relatorio.pdf', 100, 'application/pdf'),
        ]);

    $attachment = $task->attachments()->first();

    expect($attachment)->not->toBeNull()
        ->and(Storage::disk('anexos')->exists($attachment->file_path))->toBeTrue()
        ->and(Storage::disk('public')->exists($attachment->file_path))->toBeFalse();

    $this->actingAs($gestor)
        ->get("/tarefas/{$task->id}/anexos/{$attachment->id}")
        ->assertDownload('relatorio.pdf');
});
