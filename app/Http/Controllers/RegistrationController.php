<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    /** Cadastro público: toda conta nova recebe o papel "Usuário" (somente leitura). */
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower((string) $request->input('email'))]);

        $data = $request->validateWithBag('register', [
            'name' => ['required', 'string', 'max:250'],
            'email' => ['required', 'string', 'email', 'max:250', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Informe seu nome.',
            'name.max' => 'O nome pode ter no máximo 250 caracteres.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'Crie uma senha.',
            'password.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);

        $user = User::create($data);
        $user->assignRole('Usuário');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('aeronaves.index')->withSuccess('Conta criada. Bem-vindo ao hangar!');
    }
}
