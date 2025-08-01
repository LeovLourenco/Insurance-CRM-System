<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VinculoController;
use App\Http\Controllers\CotacaoController;
use App\Http\Controllers\CotacaoSeguradoraController; // ⬅️ NOVA IMPORTAÇÃO
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

    // ===== ROTAS DE COTAÇÕES (ATUALIZADAS) =====
    
    // Rotas AJAX/API específicas (ANTES do resource para não conflitar)
    Route::get('/cotacoes/api/seguradoras', [CotacaoController::class, 'seguradoras'])
        ->name('cotacoes.api.seguradoras');
    
    // Dashboard específico (ANTES do resource)
    Route::get('/cotacoes-dashboard', [CotacaoController::class, 'dashboard'])
        ->name('cotacoes.dashboard');
    
    // Relatório com filtros (ANTES do resource - para não conflitar com {cotacao})
    Route::get('/cotacoes/relatorio', [CotacaoController::class, 'relatorioFiltrado'])
        ->name('cotacoes.relatorio');
    
    // Rotas principais do resource
    Route::resource('cotacoes', CotacaoController::class);
    
    // ===== NOVAS ROTAS PARA VIEWS OTIMIZADAS =====
    
    // Status da cotação (finalizar/cancelar - usado no show)
    Route::patch('/cotacoes/{cotacao}/status', [CotacaoController::class, 'updateStatus'])
        ->name('cotacoes.update-status');
    
    // Marcar como enviada (substitui o antigo "enviar-todas")
    Route::post('/cotacoes/{cotacao}/marcar-enviada', [CotacaoController::class, 'marcarEnviada'])
        ->name('cotacoes.marcar-enviada');
    
    // Comentário rápido (modal do index)
    Route::post('/cotacoes/{cotacao}/comentario', [CotacaoController::class, 'adicionarComentario'])
        ->name('cotacoes.comentario');
    
    // Atividade completa (modal do show)
    Route::post('/cotacoes/{cotacao}/atividade', [CotacaoController::class, 'adicionarAtividade'])
        ->name('cotacoes.atividade');
    
    // Duplicar cotação (ação rápida do show)
    Route::post('/cotacoes/{cotacao}/duplicar', [CotacaoController::class, 'duplicar'])
        ->name('cotacoes.duplicar');
    
    // ===== ROTAS PARA COTACAO_SEGURADORAS (GESTÃO INDIVIDUAL) =====
    
    // Dados da seguradora (para modal de edição no show)
    Route::get('/cotacao-seguradoras/{cotacaoSeguradora}', [CotacaoSeguradoraController::class, 'show'])
        ->name('cotacao-seguradoras.show');
    
    // Página de edição da seguradora
    Route::get('/cotacao-seguradoras/{cotacaoSeguradora}/edit', [CotacaoSeguradoraController::class, 'edit'])
        ->name('cotacao-seguradoras.edit');
    
    // Atualizar dados completos da seguradora
    Route::put('/cotacao-seguradoras/{cotacaoSeguradora}', [CotacaoSeguradoraController::class, 'update'])
        ->name('cotacao-seguradoras.update');
    
    // Status inline da seguradora (usado em ambas as views)
    Route::patch('/cotacao-seguradoras/{cotacaoSeguradora}/status', [CotacaoSeguradoraController::class, 'updateStatus'])
        ->name('cotacao-seguradoras.update-status');
    
    // Marcar seguradora como enviada (individual)
    Route::post('/cotacao-seguradoras/{cotacaoSeguradora}/marcar-enviada', [CotacaoSeguradoraController::class, 'marcarEnviada'])
        ->name('cotacao-seguradoras.marcar-enviada');
    
    // Adicionar observação específica (show)
    Route::post('/cotacao-seguradoras/{cotacaoSeguradora}/observacao', [CotacaoSeguradoraController::class, 'adicionarObservacao'])
        ->name('cotacao-seguradoras.observacao');
    
    // ===== ROTAS DE COMPATIBILIDADE (MANTER TEMPORARIAMENTE) =====
    
    // Rota original - manter funcionando mas marcar como DEPRECATED
    Route::post('/cotacoes/{id}/enviar-todas', [CotacaoController::class, 'enviarTodas'])
        ->name('cotacoes.enviar-todas'); // ⚠️ DEPRECATED - usar marcar-enviada
    
    // Status por seguradora (rota original - manter temporariamente)  
    Route::put('/cotacoes/{cotacao}/seguradoras/{seguradora}/status', [CotacaoController::class, 'atualizarStatusSeguradora'])
        ->name('cotacoes.seguradoras.status'); // ⚠️ DEPRECATED - usar cotacao-seguradoras
    
    // ===== EXPORTAÇÕES E RELATÓRIOS =====
    
    // Exportações por cotação individual
    Route::prefix('cotacoes/{id}')->group(function () {
        Route::get('/pdf', [CotacaoController::class, 'gerarPdf'])
            ->name('cotacoes.pdf');
        
        Route::get('/excel', [CotacaoController::class, 'exportarExcel'])
            ->name('cotacoes.excel');
    });
    
    // Rotas de relatórios gerenciais (futuras implementações)
    Route::prefix('relatorios/cotacoes')->group(function () {
        Route::get('/dashboard-avancado', [CotacaoController::class, 'dashboardAvancado'])
            ->name('relatorios.cotacoes.dashboard');
        
        Route::get('/performance', [CotacaoController::class, 'relatorioPerformance'])
            ->name('relatorios.cotacoes.performance');
        
        Route::get('/seguradoras', [CotacaoController::class, 'relatorioSeguradoras'])
            ->name('relatorios.cotacoes.seguradoras');
    });

    // ===== ROTAS DE CONSULTAS =====
    Route::get('/consultas/seguros', [ConsultaController::class, 'index'])->name('consultas.seguros');
    Route::post('/consultas/seguros', [ConsultaController::class, 'buscar'])->name('consultas.buscar');

    // ===== ROTAS DE USUÁRIO =====
    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil'])->name('usuario.perfil');
    Route::put('/usuario/perfil', [UsuarioController::class, 'atualizar'])->name('usuario.atualizar');

    // ===== ROTAS DE CADASTROS RÁPIDOS =====
    Route::post('/segurados', [SeguradoController::class, 'store'])->name('segurados.store');
    Route::post('/corretoras', [CorretoraController::class, 'store'])->name('corretoras.store');
    
    // ===== ROTAS DE RECURSOS (CRUD COMPLETO) =====
    Route::resource('produtos', ProdutoController::class);
    Route::resource('seguradoras', SeguradoraController::class);
    Route::resource('corretoras', CorretoraController::class);
    Route::resource('segurados', SeguradoController::class);

    // ===== PÁGINA DE CADASTRO =====
    Route::get('/cadastro', function () {
        $seguradoras = \App\Models\Seguradora::all();
        $produtos = \App\Models\Produto::all();
        return view('cadastro', compact('seguradoras', 'produtos'));
    })->name('cadastro');

});

// ===== ROTAS DE DESENVOLVIMENTO (REMOVER EM PRODUÇÃO) =====
if (app()->environment('local')) {
    Route::get('/debug/routes', function() {
        return response()->json([
            'cotacoes_routes' => collect(Route::getRoutes())
                ->filter(fn($route) => str_contains($route->uri(), 'cotac'))
                ->map(fn($route) => [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName()
                ])
                ->values()
        ]);
    });
}