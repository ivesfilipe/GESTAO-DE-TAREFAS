<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Credenciais inválidas.',
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Conta desativada.',
            ]);
        }

        $request->session()->regenerate();

        if ($user->isGestor()) {
            return redirect('/painel');
        }

        return redirect('/minhas-tarefas');
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }
}
