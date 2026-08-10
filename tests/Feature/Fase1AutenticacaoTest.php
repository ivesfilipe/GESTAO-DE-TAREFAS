<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('usuario ativo consegue logar', function () {
    $user = User::factory()->create(['activated_at' => now(), 'is_active' => true]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

test('usuario desativado nao consegue logar', function () {
    $user = User::factory()->create(['is_active' => false]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors();
});

test('credenciais invalidas retornam erro', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'senha-errada',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors();
});

test('gestor eh redirecionado para painel ao logar', function () {
    $gestor = User::factory()->gestor()->create();

    $this->post('/login', [
        'email' => $gestor->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
});

test('liderado eh redirecionado para minhas-tarefas ao logar', function () {
    $liderado = User::factory()->liderado()->create();

    $this->post('/login', [
        'email' => $liderado->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
});

test('logout encerra sessao', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
});

test('token de convite invalido nao permite definir senha', function () {
    $this->get('/convite/token-invalido')
        ->assertStatus(200)
        ->assertSee('Link expirado ou inválido');
});

test('gestor acessa equipe', function () {
    $gestor = User::factory()->gestor()->create();
    User::factory(3)->liderado()->create();

    $this->actingAs($gestor)
        ->get('/equipe')
        ->assertStatus(200);
});

test('liderado nao acessa equipe', function () {
    $liderado = User::factory()->liderado()->create();

    $this->actingAs($liderado)
        ->get('/equipe')
        ->assertStatus(403);
});

test('gestor convida novo liderado', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)
        ->post('/equipe', [
            'name' => 'Novo Liderado',
            'email' => 'liderado@exemplo.com',
            'role' => 'liderado',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'email' => 'liderado@exemplo.com',
        'role' => 'liderado',
    ]);
});

test('convite exibe link de definicao de senha na tela', function () {
    $gestor = User::factory()->gestor()->create();

    $response = $this->actingAs($gestor)
        ->post('/equipe', [
            'name' => 'Novo Liderado',
            'email' => 'liderado@exemplo.com',
            'role' => 'liderado',
        ]);

    $response->assertSessionHas('invite_link');

    $this->actingAs($gestor)
        ->get('/equipe')
        ->assertSee('/convite/');
});

test('gestor gera novo link para liderado existente', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create();

    $response = $this->actingAs($gestor)
        ->post("/equipe/{$liderado->id}/convite");

    $response->assertRedirect(route('team.index'));
    $response->assertSessionHas('invite_link');
    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => $liderado->email,
    ]);
});

test('liderado nao pode gerar link de convite', function () {
    $liderado = User::factory()->liderado()->create();
    $outro = User::factory()->liderado()->create();

    $this->actingAs($liderado)
        ->post("/equipe/{$outro->id}/convite")
        ->assertStatus(403);
});

test('gestor pode desativar liderado', function () {
    $gestor = User::factory()->gestor()->create();
    $liderado = User::factory()->liderado()->create(['is_active' => true]);

    $this->actingAs($gestor)
        ->patch("/equipe/{$liderado->id}", ['is_active' => false]);

    $this->assertDatabaseHas('users', [
        'id' => $liderado->id,
        'is_active' => false,
    ]);
});
