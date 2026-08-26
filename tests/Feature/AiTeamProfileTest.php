<?php

use App\Models\TeamMemberDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.openai.key' => null]);
    config(['ai.default' => 'groq']);
    config(['ai.providers.groq.api_key' => null]);
    Storage::fake('local');
});

test('gestor acessa perfil inteligente do liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $response = $this->actingAs($gestor)->get('/equipe/'.$liderado->id);

    $response->assertOk()
        ->assertSee($liderado->name)
        ->assertSee('Perfil inteligente');
});

test('liderado nao acessa perfil de outro liderado', function () {
    $liderado1 = User::factory()->liderado()->create();
    $liderado2 = User::factory()->liderado()->create();

    $this->actingAs($liderado1)->get('/equipe/'.$liderado2->id)->assertForbidden();
});

test('gestor gera resumo do perfil', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $response = $this->actingAs($gestor)->postJson('/equipe/'.$liderado->id.'/resumo');

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mock', true);
});

test('gestor anexa documento ao perfil', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $file = UploadedFile::fake()->createWithContent('notas.txt', 'Experiência em Laravel');

    $this->actingAs($gestor)
        ->post('/equipe/'.$liderado->id.'/documentos', [
            'document' => $file,
            'name' => 'Anotações',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('team_member_documents', [
        'user_id' => $liderado->id,
        'name' => 'Anotações',
    ]);
});

test('gestor remove documento do perfil', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $document = TeamMemberDocument::factory()->create(['user_id' => $liderado->id]);

    $this->actingAs($gestor)
        ->delete('/equipe/'.$liderado->id.'/documentos/'.$document->id)
        ->assertRedirect();

    $document->refresh();
    expect($document->deleted_at)->not->toBeNull();
});

test('gestor atualiza perfil profissional do liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($gestor)
        ->patch('/equipe/'.$liderado->id.'/perfil', [
            'role' => 'Analista',
            'department' => 'Manutenção',
            'function_summary' => 'Responsável por revisões técnicas.',
            'responsibilities' => ['Revisar contratos', 'Auditorias'],
            'recurring_responsibilities' => ['Relatório semanal'],
            'professional_objectives' => ['Especialização em normas'],
            'delegation_guidelines' => 'Delegar revisões técnicas.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('team_member_profiles', [
        'user_id' => $liderado->id,
        'role' => 'Analista',
        'department' => 'Manutenção',
    ]);
});

test('gestor gera sugestoes de tarefas para liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $liderado->teamProfile()->create([
        'role' => 'Analista',
        'recurring_responsibilities' => ['Revisão semanal'],
    ]);

    $response = $this->actingAs($gestor)->postJson('/equipe/'.$liderado->id.'/sugestoes');

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mock', true)
        ->assertJsonPath('suggestions', fn ($suggestions) => count($suggestions) > 0);
});

test('documento anexado registra status de processamento', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $file = UploadedFile::fake()->createWithContent('notas.txt', 'Experiência em Laravel');

    $this->actingAs($gestor)
        ->post('/equipe/'.$liderado->id.'/documentos', [
            'document' => $file,
            'name' => 'Anotações',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('team_member_documents', [
        'user_id' => $liderado->id,
        'name' => 'Anotações',
        'processing_status' => 'pronto',
    ]);
});

test('liderado nao atualiza perfil profissional', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)
        ->patch('/equipe/'.$liderado->id.'/perfil', ['role' => 'Hacker'])
        ->assertForbidden();
});
