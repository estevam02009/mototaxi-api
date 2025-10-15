<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registra um novo usuário.
     */
    public function register(Request $request)
    {
        // 1. Validação dos dados de entrada
        // Garante que os dados fornecidos são válidos
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'type' => ['required', 'in:passenger,driver'], // 'in' garante que o valor é um dos especificados
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        }

        // 2. Criação do usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type, // Armazena o tipo de usuário
        ]);

        // 3. Geração do token de autenticação
        // O token será usado para autenticar futuras requisições
        $token = $user->createToken('mototaxi-token')->plainTextToken;

        // 4. Retorno da resposta
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Autentica um usuário existente.
     */
    public function login(Request $request)
    {
        // 1. Validação dos dados de entrada
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        }

        // 2. Verificação do usuário
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        // 3. Geração do token de autenticação
        $token = $user->createToken('mototaxi-token')->plainTextToken;

        // 4. Retorno da resposta
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Desloga o usuário autenticado.
     */
    public function logout(Request $request)
    {
        // 1. Revogar o token atual
        $request->user()->currentAccessToken()->delete();

        // 2. Retorno da resposta
        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ], 200);
    }
}
