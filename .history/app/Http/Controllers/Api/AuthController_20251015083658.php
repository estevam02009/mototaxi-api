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
     * Regista um novo usuário (Passageiro ou Mototáxi).
     */
    public function register(Request $request)
    {
        // 1. Validação dos Dados
        // Garante que todos os campos necessários estão presentes e no formato correto.
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'type' => ['required', 'in:passenger,driver'], // 'in' garante que o tipo é válido
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422); // Código 422 para Erro de Entidade Não Processável
        }

        // 2. Criação do Usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Criptografa a senha
            'type' => $request->type,
        ]);

        // 3. Criação do Token de Acesso (Sanctum)
        // O token será usado pelo React Native para provar a identidade.
        $token = $user->createToken('mototaxi-token')->plainTextToken;

        // 4. Resposta de Sucesso
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201); // Código 201 para Criado
    }

    /**
     * Autentica um usuário existente (login).
     */
    public function login(Request $request)
    {
        // 1. Validação dos Dados
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Tentar Encontrar o Usuário
        $user = User::where('email', $request->email)->first();

        // 3. Verificar Credenciais e Criar Token
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Se o usuário não existe OU a senha está errada
            return response()->json([
                'message' => 'Credenciais inválidas.'
            ], 401); // Código 401 para Não Autorizado (Unauthorized)
        }

        // Limpar tokens antigos de segurança (opcional, mas recomendado)
        $user->tokens()->delete();

        // Criar novo token de acesso
        $token = $user->createToken('mototaxi-token')->plainTextToken;

        // 4. Resposta de Sucesso
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Faz o logout do usuário (revoga o token).
     */
    public function logout(Request $request)
    {
        // O Laravel Sanctum permite aceder ao token atual pelo request.
        // O 'currentAccessToken' é o token que o usuário usou para fazer esta requisição.
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessão encerrada com sucesso.'], 200);
    }
}
