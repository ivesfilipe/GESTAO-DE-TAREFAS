<?php

use App\Contracts\AI\AIProviderInterface;
use App\Models\Task;
use App\Models\TeamMemberDocument;
use App\Models\TeamMemberKnowledgeChunk;
use App\Models\User;
use App\Services\AI\AIProviderManager;
use App\Services\AI\AIService;
use App\Services\AI\ImageUnderstandingService;
use App\Services\AI\Providers\MockProvider;
use App\Services\AI\Safety\ZeroDataRetention;
use App\Services\AI\TeamPerformanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('zdr sem confirmacao bloqueia provider externo antes de enviar contexto', function () {
    config([
        'ai.zdr.required' => true,
        'ai.zdr.confirmed' => false,
        'ai.logging.enabled' => false,
    ]);

    $provider = Mockery::mock(AIProviderInterface::class);
    $provider->shouldReceive('name')->andReturn('groq');
    $provider->shouldNotReceive('complete');

    $service = new AIService($provider, new ZeroDataRetention);

    expect(fn () => $service->ask('Analise.', 'Tarefa interna: revisão de contrato.'))
        ->toThrow(RuntimeException::class, 'ZDR exige confirmação administrativa');
});

test('fallback nunca troca groq por openai pago', function () {
    config([
        'ai.default' => 'groq',
        'ai.providers.groq.api_key' => null,
        'ai.providers.openai.api_key' => 'configured-but-forbidden-fallback',
        'ai.fallback_enabled' => true,
        'ai.fallback_chain' => ['openai', 'mock'],
    ]);

    $provider = (new AIProviderManager)->resolve();

    expect($provider)->toBeInstanceOf(MockProvider::class);
});

test('liderado nao cria tarefa pela api', function () {
    $liderado = User::factory()->liderado()->create();
    $token = $liderado->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/tasks', [
            'title' => 'Tentativa indevida',
            'priority' => 'normal',
            'due_at' => now()->addDay()->toIso8601String(),
        ])
        ->assertForbidden();
});

test('perfil de liderado rejeita arquivo fora da allowlist', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $file = UploadedFile::fake()->create('arquivo.exe', 10, 'application/x-msdownload');

    $this->actingAs($gestor)
        ->post('/equipe/'.$liderado->id.'/documentos', ['document' => $file])
        ->assertSessionHasErrors('document');
});

test('gestor nao acessa perfil de liderado de outra equipe', function () {
    $gestorDaEquipe = User::factory()->gestor()->create();
    $outroGestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create(['manager_id' => $gestorDaEquipe->id]);

    $this->actingAs($outroGestor)
        ->get('/equipe/'.$liderado->id)
        ->assertForbidden();
});

test('api do gestor nao lista tarefas de outra equipe', function () {
    $gestorDaEquipe = User::factory()->gestor()->create();
    $outroGestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create(['manager_id' => $gestorDaEquipe->id]);
    $task = Task::factory()->create([
        'title' => 'Tarefa confidencial',
        'created_by' => $gestorDaEquipe->id,
        'assigned_to' => $liderado->id,
    ]);

    $token = $outroGestor->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/tasks')
        ->assertOk()
        ->assertJsonMissing(['title' => $task->title]);
});

test('metricas contam como reprovacao de desempenho apenas nao atendimento', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    Task::factory()->create([
        'assigned_to' => $liderado->id,
        'created_by' => $gestor->id,
        'status' => 'concluida',
        'created_at' => now()->subHours(48),
        'completed_at' => now(),
    ]);
    Task::factory()->create([
        'assigned_to' => $liderado->id,
        'created_by' => $gestor->id,
        'status' => 'reprovada',
        'rejection_category' => 'info_incompleta',
    ]);
    Task::factory()->create([
        'assigned_to' => $liderado->id,
        'created_by' => $gestor->id,
        'status' => 'reprovada',
        'rejection_category' => 'nao_atende',
    ]);

    $metrics = (new TeamPerformanceService)->memberMetrics($liderado);

    expect($metrics['avg_cycle_hours'])->toBe(48.0)
        ->and($metrics['rejected_tasks'])->toBe(1)
        ->and($metrics['rejection_rate'])->toBe(50.0);
});

test('analise de imagem registra somente metadados pela camada central de ia', function () {
    Storage::fake('local');
    Storage::disk('local')->putFileAs(
        'company-knowledge',
        UploadedFile::fake()->image('diagrama.png'),
        'diagrama.png',
    );
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Diagrama de fluxo.']]],
        ]),
    ]);
    config([
        'ai.default' => 'groq',
        'ai.providers.groq.api_key' => 'test-key',
        'ai.zdr.required' => true,
        'ai.zdr.confirmed' => true,
        'ai.logging.enabled' => true,
    ]);

    $result = (new ImageUnderstandingService)->understand('company-knowledge/diagrama.png');

    expect($result)->toMatchArray(['text' => 'Diagrama de fluxo.', 'status' => 'pronto']);
    $this->assertDatabaseHas('ai_usage_logs', [
        'provider' => 'groq',
        'prompt' => null,
        'response' => null,
    ]);
});

test('resumo de perfil persiste os documentos usados como fonte', function () {
    config(['ai.default' => 'mock', 'ai.logging.enabled' => false]);
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();
    $document = TeamMemberDocument::factory()->create([
        'user_id' => $liderado->id,
        'name' => 'Responsabilidades.pdf',
    ]);
    TeamMemberKnowledgeChunk::create([
        'user_id' => $liderado->id,
        'document_id' => $document->id,
        'content' => 'Responsabilidades e competências técnicas do liderado.',
        'order' => 0,
    ]);

    $this->actingAs($gestor)
        ->postJson('/equipe/'.$liderado->id.'/resumo')
        ->assertOk()
        ->assertJsonPath('sources.0', 'Responsabilidades.pdf');

    $this->assertDatabaseHas('team_member_profiles', [
        'user_id' => $liderado->id,
        'summary_invalidated_at' => null,
    ]);
    expect($liderado->fresh()->teamProfile->ai_summary_sources)->toBe(['Responsabilidades.pdf']);
});

test('criacao web normaliza criterios e evidencias como listas', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)
        ->post('/tarefas', [
            'title' => 'Revisar procedimento',
            'priority' => 'normal',
            'due_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'task_type' => 'responsabilidade',
            'acceptance_criteria' => "1. Documento revisado\n2. Pendências registradas",
            'expected_evidence' => "Checklist assinado\nRegistro no sistema",
        ])
        ->assertRedirect('/tarefas');

    $task = Task::query()->where('title', 'Revisar procedimento')->firstOrFail();

    expect($task->task_type)->toBe('responsabilidade')
        ->and($task->acceptance_criteria)->toBe(['Documento revisado', 'Pendências registradas'])
        ->and($task->expected_evidence)->toBe(['Checklist assinado', 'Registro no sistema']);
});
