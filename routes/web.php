<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VinculoController;
use App\Http\Controllers\CotacaoController;

Route::get('/', function () {
    return view('welcome');
});

// Rotas de vínculos
Route::get('/vinculos', [VinculoController::class, 'index'])->name('vinculos.index');
Route::post('/vinculos', [VinculoController::class, 'store'])->name('vinculos.store');

// Rotas de cotações
Route::get('/cotacoes', [CotacaoController::class, 'index'])->name('cotacoes.index');
Route::get('/cotacoes/nova', [CotacaoController::class, 'create'])->name('cotacoes.create');
Route::post('/cotacoes', [CotacaoController::class, 'store'])->name('cotacoes.store');