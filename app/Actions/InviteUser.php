<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InviteUser
{
    public function execute(User $gestor, array $data): array
    {
        $password = Str::random(64);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => $data['role'],
            'invited_at' => now(),
        ]);

        return [
            'user' => $user,
            'token' => $this->createToken($user),
        ];
    }

    /**
     * Gera (ou regenera) o token de definição de senha do usuário.
     * Usado no convite inicial e no "novo link" (link expirado em 48h
     * ou liderado que esqueceu a senha).
     */
    public function createToken(User $user): string
    {
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => now()]
        );

        return $token;
    }
}
