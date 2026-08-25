<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

test('gestor acessa dashboard do pulse', function () {
    $gestor = User::factory()->gestor()->create();

    $this->actingAs($gestor)->get('/pulse')->assertOk();
});

test('liderado nao acessa dashboard do pulse', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)->get('/pulse')->assertForbidden();
});

test('visitante nao acessa pulse', function () {
    $this->get('/pulse')->assertForbidden();
});

test('feature ai-assistant ativa apenas para gestor', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    expect(Feature::for($gestor)->active('ai-assistant'))->toBeTrue();
    expect(Feature::for($liderado)->active('ai-assistant'))->toBeFalse();
});

test('feature auto-scheduling ativa apenas para gestor', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    expect(Feature::for($gestor)->active('auto-scheduling'))->toBeTrue();
    expect(Feature::for($liderado)->active('auto-scheduling'))->toBeFalse();
});
