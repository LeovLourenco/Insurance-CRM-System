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

    // ===== ROTAS DE COTAÇÕES (REORGANIZADAS) =====
    
    // Rotas AJAX/API específicas (ANTES do resource para não conflitar)
    Route::get('/cotacoes/api/seguradoras', [CotacaoController::class, 'seguradoras'])
        ->name('cotacoes.api.seguradoras');
    
    // Dashboard específico (ANTES do resource)
    Route::get('/cotacoes-dashboard', [CotacaoController::class, 'dashboard'])
        ->name('cotacoes.dashboard');
    
    // Rotas principais do resource
    Route::resource('cotacoes', CotacaoController::class);
    
    // Ações específicas de cotação (APÓS o resource)
    Route::post('/cotacoes/{id}/enviar-todas', [CotacaoController::class, 'enviarTodas'])
        ->name('cotacoes.enviar-todas');
    
    Route::put('/cotacoes/{cotacao}/seguradoras/{seguradora}/status', [CotacaoController::class, 'atualizarStatusSeguradora'])
        ->name('cotacoes.seguradoras.status');
    
    // Relatórios e exportações (para futuras implementações)
    Route::prefix('cotacoes/{id}')->group(function () {
        Route::get('/pdf', [CotacaoController::class, 'gerarPdf'])
            ->name('cotacoes.pdf');
        
        Route::get('/excel', [CotacaoController::class, 'exportarExcel'])
            ->name('cotacoes.excel');
    });
    
    // Rotas de relatórios gerenciais (para futuras implementações)
    Route::prefix('relatorios/cotacoes')->group(function () {
        Route::get('/dashboard-avancado', [CotacaoController::class, 'dashboardAvancado'])
            ->name('relatorios.cotacoes.dashboard');
        
        Route::get('/performance', [CotacaoController::class, 'relatorioPerformance'])
            ->name('relatorios.cotacoes.performance');
        
        Route::get('/seguradoras', [CotacaoController::class, 'relatorioSeguradoras'])
            ->name('relatorios.cotacoes.seguradoras');
    });

    // Rotas de consultas
    Route::get('/consultas/seguros', [ConsultaController::class, 'index'])->name('consultas.seguros');
    Route::post('/consultas/seguros', [ConsultaController::class, 'buscar'])->name('consultas.buscar');

    // Rota para perfil do usuário
    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil'])->name('usuario.perfil');
    Route::put('/usuario/perfil', [UsuarioController::class, 'atualizar'])->name('usuario.atualizar');

    // ===== ROTAS DE CADASTROS RÁPIDOS =====
    Route::post('/segurados', [SeguradoController::class, 'store'])->name('segurados.store');
    Route::post('/corretoras', [CorretoraController::class, 'store'])->name('corretoras.store');
    
    // ===== ROTAS DE PRODUTOS =====
    Route::resource('produtos', ProdutoController::class);
    
    // ===== ROTAS DE SEGURADORAS =====
    Route::resource('seguradoras', SeguradoraController::class);
    
    // ===== ROTAS DE CORRETORAS =====
    Route::resource('corretoras', CorretoraController::class);
    
    // ===== ROTAS DE SEGURADOS =====
    Route::resource('segurados', SeguradoController::class);

    // Página de cadastro com dados
    Route::get('/cadastro', function () {
        $seguradoras = \App\Models\Seguradora::all();
        $produtos = \App\Models\Produto::all();
        return view('cadastro', compact('seguradoras', 'produtos'));
    })->name('cadastro');

});