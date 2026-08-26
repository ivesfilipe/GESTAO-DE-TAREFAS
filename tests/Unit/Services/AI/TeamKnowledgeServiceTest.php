<?php

use App\Models\TeamMemberDocument;
use App\Models\TeamMemberKnowledgeChunk;
use App\Models\User;
use App\Services\AI\TeamKnowledgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

test('storeDocument salva documento e cria chunks', function () {
    $member = User::factory()->liderado()->create();
    $file = UploadedFile::fake()->createWithContent('curriculo.txt', 'PHP Laravel gestão de equipes liderança');

    $service = new TeamKnowledgeService;
    $document = $service->storeDocument($member, $file);

    expect($document)->toBeInstanceOf(TeamMemberDocument::class)
        ->and($document->user_id)->toBe($member->id)
        ->and($document->chunks()->count())->toBeGreaterThan(0);
});

test('retrieve encontra chunks por termo', function () {
    $member = User::factory()->liderado()->create();
    TeamMemberKnowledgeChunk::create([
        'user_id' => $member->id,
        'document_id' => null,
        'content' => 'Experiência avançada em Laravel e gestão de times',
        'order' => 0,
    ]);

    $service = new TeamKnowledgeService;
    $results = $service->retrieve($member, 'Laravel');

    expect($results)->toHaveCount(1)
        ->and($results->first()->content)->toContain('Laravel');
});

test('retrieve nao retorna chunks de outros usuarios no fallback or', function () {
    $memberA = User::factory()->liderado()->create();
    $memberB = User::factory()->liderado()->create();

    TeamMemberKnowledgeChunk::create([
        'user_id' => $memberA->id,
        'document_id' => null,
        'content' => 'Experiência avançada em Laravel',
        'order' => 0,
    ]);

    TeamMemberKnowledgeChunk::create([
        'user_id' => $memberB->id,
        'document_id' => null,
        'content' => 'Experiência avançada em Laravel também',
        'order' => 0,
    ]);

    $service = new TeamKnowledgeService;
    $results = $service->retrieve($memberA, 'Laravel experiência avançada');

    expect($results)->toHaveCount(1)
        ->and($results->first()->user_id)->toBe($memberA->id);
});

test('deleteDocument remove arquivo e chunks', function () {
    $member = User::factory()->liderado()->create();
    $file = UploadedFile::fake()->createWithContent('notas.txt', 'Anotações do liderado');
    $service = new TeamKnowledgeService;
    $document = $service->storeDocument($member, $file);

    $service->deleteDocument($document);

    expect(TeamMemberDocument::find($document->id))->toBeNull()
        ->and(TeamMemberKnowledgeChunk::where('document_id', $document->id)->count())->toBe(0);
});
