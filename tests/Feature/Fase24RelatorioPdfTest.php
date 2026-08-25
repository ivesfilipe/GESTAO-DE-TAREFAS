<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gestor baixa relatorio pdf de funcionalidades', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)->get('/relatorio-funcionalidades');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('pdf');
    expect(substr($response->getContent(), 0, 5))->toBe('%PDF-');
});

test('liderado nao baixa o relatorio', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)->get('/relatorio-funcionalidades')->assertForbidden();
});
