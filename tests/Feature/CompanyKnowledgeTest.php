<?php

use App\Models\CompanyDocument;
use App\Models\CompanyKnowledgeChunk;
use App\Models\User;
use App\Services\AI\CompanyKnowledgeService;
use App\Services\AI\TeamKnowledgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    config(['ai.default' => 'groq']);
    config(['ai.providers.groq.api_key' => null]);
    config(['ai.fallback_enabled' => false]);
});

test('gestor anexa txt na base da empresa e retrieve acha o trecho', function () {
    $gestor = User::factory()->gestor()->create();
    $file = UploadedFile::fake()->createWithContent('procedimento.txt', 'Checklist de preventiva da caldeira industrial');

    $response = $this->actingAs($gestor)->post('/assistente/anexos', [
        'file' => $file,
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('status', 'pronto');

    $service = new CompanyKnowledgeService;
    $results = $service->retrieve('preventiva caldeira');

    expect($results)->not->toBeEmpty()
        ->and($results->first()->content)->toContain('caldeira');
});

test('anexo da sessao entra no contexto enviado ao provider', function () {
    $gestor = User::factory()->gestor()->create();
    $document = (new CompanyKnowledgeService)->storeDocument(
        $gestor,
        UploadedFile::fake()->createWithContent('procedimento.txt', 'Procedimento de calibracao do manometro'),
    );

    config([
        'ai.providers.groq.api_key' => 'fake-key',
        'ai.zdr.required' => false,
    ]);

    Http::fake(function (ClientRequest $request) {
        expect($request->data()['messages'][1]['content'])->toContain('Procedimento de calibracao do manometro');

        return Http::response([
            'choices' => [[
                'message' => ['content' => 'Recebi o procedimento.'],
                'finish_reason' => 'stop',
            ]],
        ]);
    });

    $this->actingAs($gestor)
        ->postJson('/assistente/perguntar', [
            'question' => 'Resuma o procedimento.',
            'document_ids' => [$document->id],
        ])
        ->assertOk()
        ->assertJsonPath('answer', 'Recebi o procedimento.')
        ->assertJsonPath('mock', false);
});

test('csv vira chunks na base da empresa', function () {
    $gestor = User::factory()->gestor()->create();
    $file = UploadedFile::fake()->createWithContent('estoque.csv', "item,qtd\nvalvula,12\nmanometro,4");

    $this->actingAs($gestor)->post('/assistente/anexos', ['file' => $file])->assertOk();

    $results = (new CompanyKnowledgeService)->retrieve('manometro');
    expect($results->first()->content)->toContain('manometro');
});

test('xlsx vira chunks na base da empresa', function () {
    $gestor = User::factory()->gestor()->create();
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->setTitle('Estoque');
    $spreadsheet->getActiveSheet()->fromArray([
        ['item', 'quantidade'],
        ['valvula de seguranca', 12],
    ]);
    $path = tempnam(sys_get_temp_dir(), 'xlsx');
    (new Xlsx($spreadsheet))->save($path);
    $spreadsheet->disconnectWorksheets();
    $file = new UploadedFile(
        $path,
        'estoque.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );

    $this->actingAs($gestor)->post('/assistente/anexos', ['file' => $file])->assertOk();

    $results = (new CompanyKnowledgeService)->retrieve('valvula seguranca');
    expect($results->first()->content)->toContain('valvula de seguranca');
});

test('knowledge da empresa nao mistura com perfil do liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $companyFile = UploadedFile::fake()->createWithContent('empresa.txt', 'Politica confidencial da empresa MedicalThermo');
    $this->actingAs($gestor)->post('/assistente/anexos', ['file' => $companyFile])->assertOk();

    $memberFile = UploadedFile::fake()->createWithContent('perfil.txt', 'Experiencia avancada em Laravel do liderado');
    (new TeamKnowledgeService)->storeDocument($liderado, $memberFile);

    $companyHits = (new CompanyKnowledgeService)->retrieve('Laravel liderado');
    $memberHits = (new TeamKnowledgeService)->retrieve($liderado, 'MedicalThermo empresa');

    expect($companyHits->contains(fn ($chunk) => str_contains($chunk->content, 'Laravel')))->toBeFalse();
    expect($memberHits->contains(fn ($chunk) => str_contains($chunk->content, 'MedicalThermo')))->toBeFalse();
});

test('imagem sem groq fica needs_ocr e nao quebra', function () {
    $gestor = User::factory()->gestor()->create();
    $file = UploadedFile::fake()->image('print.jpg', 20, 20);

    $response = $this->actingAs($gestor)->post('/assistente/anexos', ['file' => $file]);

    $response->assertOk()
        ->assertJsonPath('status', 'needs_ocr');

    expect(CompanyDocument::count())->toBe(1)
        ->and(CompanyKnowledgeChunk::count())->toBe(0);
});

test('mime rejeitado retorna 422', function () {
    $gestor = User::factory()->gestor()->create();
    $file = UploadedFile::fake()->create('malware.exe', 20, 'application/x-msdownload');

    $this->actingAs($gestor)
        ->postJson('/assistente/anexos', ['file' => $file])
        ->assertStatus(422);
});

test('liderado nao anexa na base da empresa', function () {
    $liderado = User::factory()->liderado()->create();
    $file = UploadedFile::fake()->createWithContent('x.txt', 'texto');

    $this->actingAs($liderado)
        ->post('/assistente/anexos', ['file' => $file])
        ->assertForbidden();
});
