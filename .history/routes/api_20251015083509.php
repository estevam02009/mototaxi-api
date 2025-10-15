<?php

// Arquivo: routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // Importar o novo Controller

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação (Acesso Público)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Requer Autenticação)
|--------------------------------------------------------------------------
| O 'middleware('auth:sanctum')' garante que o usuário enviou um token
| válido na requisição (Header: Authorization: Bearer <token>)
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Rota de exemplo para testar a autenticação
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Aqui adicionaremos as rotas de viagens, mototáxis, etc.
});
