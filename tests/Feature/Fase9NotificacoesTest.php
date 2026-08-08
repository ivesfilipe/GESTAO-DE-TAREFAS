<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('usuario acessa lista de notificacoes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/notificacoes')
        ->assertStatus(200);
});

test('usuario pode marcar notificacao como lida', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\NovaTarefaNotification',
        'data' => json_encode(['message' => 'teste']),
    ]);

    $notification = $user->notifications()->first();

    $this->actingAs($user)
        ->patch("/notificacoes/{$notification->id}/lida");

    $this->assertNotNull($notification->fresh()->read_at);
});
