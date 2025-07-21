<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VinculoController;
use App\Http\Controllers\CotacaoController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\Usuarios\UsuarioController;
use App\Http\Controllers\SeguradoController;
use App\Http\Controllers\CorretoraController;
use App\Http\Controllers\SeguradoraController;
use App\Http\Controllers\ProdutoController;
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

    // Rotas de cadastros 
    Route::post('/segurados', [SeguradoController::class, 'store'])->name('segurados.store');
    Route::post('/corretoras', [CorretoraController::class, 'store'])->name('corretoras.store');
    
    // rota de produtos
    Route::get('/produtos', [ProdutoController::class, 'index'])->name('produtos.index');
    Route::get('/produtos/create', [ProdutoController::class, 'create'])->name('produtos.create');
    Route::post('/produtos', [ProdutoController::class, 'store'])->name('produtos.store');
    Route::get('/produtos/{produto}', [ProdutoController::class, 'show'])->name('produtos.show');
    Route::get('/produtos/{produto}/edit', [ProdutoController::class, 'edit'])->name('produtos.edit');
    Route::put('/produtos/{produto}', [ProdutoController::class, 'update'])->name('produtos.update');
    Route::delete('/produtos/{produto}', [ProdutoController::class, 'destroy'])->name('produtos.destroy');
    
    // Rotas de seguradoras
    Route::get('/seguradoras', [SeguradoraController::class, 'index'])->name('seguradoras.index');
    Route::get('/seguradoras/create', [SeguradoraController::class, 'create'])->name('seguradoras.create');
    Route::post('/seguradoras', [SeguradoraController::class, 'store'])->name('seguradoras.store');
    Route::get('/seguradoras/{seguradora}', [SeguradoraController::class, 'show'])->name('seguradoras.show');
    Route::get('/seguradoras/{seguradora}/edit', [SeguradoraController::class, 'edit'])->name('seguradoras.edit');
    Route::put('/seguradoras/{seguradora}', [SeguradoraController::class, 'update'])->name('seguradoras.update');
    Route::delete('/seguradoras/{seguradora}', [SeguradoraController::class, 'destroy'])->name('seguradoras.destroy');
    
    // Rotas de corretoras
    Route::get('/corretoras', [CorretoraController::class, 'index'])->name('corretoras.index');
    Route::get('/corretoras/create', [CorretoraController::class, 'create'])->name('corretoras.create');
    Route::post('/corretoras', [CorretoraController::class, 'store'])->name('corretoras.store');
    Route::get('/corretoras/{corretora}', [CorretoraController::class, 'show'])->name('corretoras.show');
    Route::get('/corretoras/{corretora}/edit', [CorretoraController::class, 'edit'])->name('corretoras.edit');
    Route::put('/corretoras/{corretora}', [CorretoraController::class, 'update'])->name('corretoras.update');
    Route::delete('/corretoras/{corretora}', [CorretoraController::class, 'destroy'])->name('corretoras.destroy');
    
    // Rotas de segurados
    Route::get('/segurados', [SeguradoController::class, 'index'])->name('segurados.index');
    Route::get('/segurados/create', [SeguradoController::class, 'create'])->name('segurados.create');
    Route::post('/segurados', [SeguradoController::class, 'store'])->name('segurados.store');
    Route::get('/segurados/{segurado}', [SeguradoController::class, 'show'])->name('segurados.show');
    Route::get('/segurados/{segurado}/edit', [SeguradoController::class, 'edit'])->name('segurados.edit');
    Route::put('/segurados/{segurado}', [SeguradoController::class, 'update'])->name('segurados.update');
    Route::delete('/segurados/{segurado}', [SeguradoController::class, 'destroy'])->name('segurados.destroy');

    
    // Rota de logout
    //Route::post('/logout', [UsuarioController::class, 'logout'])->name('logout');
    
    // routes/web.php
    //Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    Route::get('/cadastro', function () {
        $seguradoras = \App\Models\Seguradora::all();
        $produtos = \App\Models\Produto::all();
        return view('cadastro', compact('seguradoras', 'produtos'));
    })->name('cadastro');

});
