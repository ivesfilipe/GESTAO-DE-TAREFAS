<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InviteController extends Controller
{
    public function showSetPassword($token)
    {
        $record = DB::table('password_reset_tokens')
            ->where('token', hash('sha256', $token))
            ->first();

        if (! $record || now()->diffInHours($record->created_at) > 48) {
            return view('auth.set-password', [
                'email' => null,
                'token' => $token,
                'error' => 'Link expirado ou inválido.',
            ]);
        }

        return view('auth.set-password', [
            'email' => $record->email,
            'token' => $token,
        ]);
    }

    public function setPassword(Request $request, $token)
    {
        $request->validate([
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('token', hash('sha256', $token))
            ->first();

        if (! $record || now()->diffInHours($record->created_at) > 48) {
            return back()->withErrors([
                'password' => 'Link expirado ou inválido.',
            ]);
        }

        $user = User::where('email', $request->email ?? $record->email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Usuário não encontrado.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'activated_at' => now(),
        ]);

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return redirect('/login')->with('success', 'Senha definida com sucesso. Faça login.');
    }
}
