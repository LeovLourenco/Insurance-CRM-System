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
use App\Http\Controllers\DownloadsCadastrosController;
use App\Http\Controllers\Admin\AtribuicoesController;
use Illuminate\Support\Facades\Auth;

// Página inicial - redireciona para login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de autenticação (login, registro, etc.)
Auth::routes();

// ===== ROTAS PÚBLICAS AUTENTICADAS =====
Route::middleware(['auth'])->group(function () {
    // Página inicial pós-login
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
});

// ===== GRUPO: COTAÇÕES - Para usuários com acesso a cotações =====
Route::middleware(['auth', 'role:comercial|diretor|admin'])->group(function () {
    
    // Rotas específicas (ANTES do resource)
    Route::get('/cotacoes/api/seguradoras', [CotacaoController::class, 'seguradoras'])
        ->name('cotacoes.api.seguradoras');
    
    Route::get('/cotacoes-dashboard', [CotacaoController::class, 'dashboard'])
        ->name('cotacoes.dashboard');
    
    Route::get('/cotacoes/relatorio', [CotacaoController::class, 'relatorioFiltrado'])
        ->name('cotacoes.relatorio');
    
    // Resource principal
    Route::resource('cotacoes', CotacaoController::class)->parameters([
        'cotacoes' => 'cotacao'
    ]);
    
    // Ações da cotação - policies aplicam isolamento automático
    Route::patch('/cotacoes/{cotacao}/status', [CotacaoController::class, 'updateStatus'])
        ->name('cotacoes.update-status');
    
    Route::post('/cotacoes/{cotacao}/marcar-enviada', [CotacaoController::class, 'marcarEnviada'])
        ->name('cotacoes.marcar-enviada');
    
    Route::post('/cotacoes/{cotacao}/comentario', [CotacaoController::class, 'adicionarComentario'])
        ->name('cotacoes.comentario');
    
    Route::post('/cotacoes/{cotacao}/atividade', [CotacaoController::class, 'adicionarAtividade'])
        ->name('cotacoes.atividade');
    
    Route::post('/cotacoes/{cotacao}/duplicar', [CotacaoController::class, 'duplicar'])
        ->name('cotacoes.duplicar');
    
    // Cotação Seguradoras - policies aplicam isolamento
    Route::get('/cotacao-seguradoras/{cotacaoSeguradora}', [CotacaoSeguradoraController::class, 'show'])
        ->name('cotacao-seguradoras.show');
    
    Route::get('/cotacao-seguradoras/{cotacaoSeguradora}/edit', [CotacaoSeguradoraController::class, 'edit'])
        ->name('cotacao-seguradoras.edit');
    
    Route::put('/cotacao-seguradoras/{cotacaoSeguradora}', [CotacaoSeguradoraController::class, 'update'])
        ->name('cotacao-seguradoras.update');
    
    Route::patch('/cotacao-seguradoras/{cotacaoSeguradora}/status', [CotacaoSeguradoraController::class, 'updateStatus'])
        ->name('cotacao-seguradoras.update-status');
    
    Route::post('/cotacao-seguradoras/{cotacaoSeguradora}/marcar-enviada', [CotacaoSeguradoraController::class, 'marcarEnviada'])
        ->name('cotacao-seguradoras.marcar-enviada');
    
    Route::post('/cotacao-seguradoras/{cotacaoSeguradora}/observacao', [CotacaoSeguradoraController::class, 'adicionarObservacao'])
        ->name('cotacao-seguradoras.observacao');
    
    // Rotas de compatibilidade (deprecated)
    Route::post('/cotacoes/{cotacao}/enviar-todas', [CotacaoController::class, 'enviarTodas'])
        ->name('cotacoes.enviar-todas');
    
    Route::put('/cotacoes/{cotacao}/seguradoras/{seguradora}/status', [CotacaoController::class, 'atualizarStatusSeguradora'])
        ->name('cotacoes.seguradoras.status');
    
    // Exportações por cotação individual - policies aplicam isolamento
    Route::get('/cotacoes/{cotacao}/pdf', [CotacaoController::class, 'gerarPdf'])
        ->name('cotacoes.pdf');
    
    Route::get('/cotacoes/{cotacao}/excel', [CotacaoController::class, 'exportarExcel'])
        ->name('cotacoes.excel');
});

// ===== GRUPO: RELATÓRIOS - Admin e Diretor apenas =====
Route::middleware(['auth', 'role:admin|diretor'])->group(function () {
    // Relatórios de auditoria
    Route::get('/relatorios/auditoria', [App\Http\Controllers\AuditoriaController::class, 'index'])
        ->name('relatorios.auditoria');
    
    Route::get('/relatorios/auditoria/{id}/detalhes', [App\Http\Controllers\AuditoriaController::class, 'detalhes'])
        ->name('relatorios.auditoria.detalhes');
    
    // Relatórios de cotações
    Route::get('/relatorios/cotacoes/dashboard-avancado', [CotacaoController::class, 'dashboardAvancado'])
        ->name('relatorios.cotacoes.dashboard');
    
    Route::get('/relatorios/cotacoes/performance', [CotacaoController::class, 'relatorioPerformance'])
        ->name('relatorios.cotacoes.performance');
    
    Route::get('/relatorios/cotacoes/seguradoras', [CotacaoController::class, 'relatorioSeguradoras'])
        ->name('relatorios.cotacoes.seguradoras');
});

// ===== GRUPO: CADASTROS BASE - Todos os usuários autenticados =====
Route::middleware(['auth'])->group(function () {
    // Consultas de seguros
    Route::get('/consultas/seguros', [ConsultaController::class, 'index'])->name('consultas.seguros');
    Route::post('/consultas/seguros', [ConsultaController::class, 'buscar'])->name('consultas.buscar');

    // Perfil do usuário
    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil'])->name('usuario.perfil');
    Route::put('/usuario/perfil', [UsuarioController::class, 'atualizar'])->name('usuario.atualizar');
    Route::put('/usuario/alterar-senha', [UsuarioController::class, 'alterarSenha'])->name('usuario.alterar.senha');

    // Página de cadastro geral
    Route::get('/cadastro', function () {
        $seguradoras = \App\Models\Seguradora::all();
        $produtos = \App\Models\Produto::all();
        return view('cadastro', compact('seguradoras', 'produtos'));
    })->name('cadastro');
});

// ===== GRUPO: CADASTROS COMERCIAIS - Comercial/Diretor/Admin =====
Route::middleware(['auth', 'role:comercial|diretor|admin'])->group(function () {
    
    // Resources com policies aplicando isolamento automático
    Route::resource('segurados', SeguradoController::class);
    Route::resource('corretoras', CorretoraController::class);
});

// ===== GRUPO: PRODUTOS E SEGURADORAS - Policy-based =====
Route::middleware(['auth'])->group(function () {
    // Policies controlam acesso: todos veem, apenas admin gere
    Route::resource('produtos', ProdutoController::class);
    Route::resource('seguradoras', SeguradoraController::class);
});

// ===== GRUPO: ADMINISTRAÇÃO - Apenas Admin =====
Route::middleware(['auth', 'role:admin|diretor'])->group(function () {
    // Downloads de cadastros
    Route::get('/admin/downloads-cadastros', [DownloadsCadastrosController::class, 'index'])
        ->name('admin.downloads-cadastros');
    
    Route::get('/admin/downloads-cadastros/csv', [DownloadsCadastrosController::class, 'downloadCSV'])
        ->name('admin.downloads-cadastros.csv');
    
    // Atribuições de comerciais
    Route::get('/admin/atribuicoes', [AtribuicoesController::class, 'index'])
        ->name('admin.atribuicoes');
    
    Route::post('/admin/atribuicoes/{corretora}', [AtribuicoesController::class, 'update'])
        ->name('admin.atribuicoes.update');
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