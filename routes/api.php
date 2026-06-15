<?php

use App\Http\Controllers\Api\{ProdutoController, AuthController};
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'registerUser']);
Route::post('/login', [AuthController::class, 'loginUser']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('produtos', ProdutoController::class);
    Route::get('buscar-produtos', [ProdutoController::class, 'buscarProduto']);
    Route::get('/profile', [AuthController::class, 'profileUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
