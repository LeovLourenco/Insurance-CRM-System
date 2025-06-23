<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VinculoController;
use App\Http\Controllers\CotacaoController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\Usuarios\UsuarioController;
use Illuminate\Support\Facades\Auth;

// Página inicial pública
Route::get('/', function () {
    return view('welcome');
});

// Rotas de autenticação (login, registro, etc.)
Auth::routes();

// Agrupar todas as rotas protegidas pelo middleware 'auth'
Route::middleware(['auth'])->group(function () {

    // Página inicial pós-login
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // Rotas de vínculos
    Route::get('/vinculos', [VinculoController::class, 'index'])->name('vinculos.index');
    Route::post('/vinculos', [VinculoController::class, 'store'])->name('vinculos.store');

    // Rotas de cotações
    Route::get('/cotacoes', [CotacaoController::class, 'index'])->name('cotacoes.index');
    Route::get('/cotacoes/nova', [CotacaoController::class, 'create'])->name('cotacoes.create');
    Route::post('/cotacoes', [CotacaoController::class, 'store'])->name('cotacoes.store');

    // Rotas de consultas
    Route::get('/consultas/seguros', [ConsultaController::class, 'index'])->name('consultas.seguros');
    Route::post('/consultas/seguros', [ConsultaController::class, 'buscar'])->name('consultas.buscar');

    // rota para perfil do usuario

    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil'])->name('usuario.perfil')->middleware('auth');
    Route::put('/usuario/perfil', [UsuarioController::class, 'atualizar'])->name('usuario.atualizar')->middleware('auth');

});
