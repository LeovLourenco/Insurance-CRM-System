<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotacao;
use App\Models\Segurado;
use App\Models\CotacaoSeguradora;
use App\Models\AtividadeCotacao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Período para comparações (mês atual vs anterior)
        $mesAtual = Carbon::now()->startOfMonth();
        $mesAnterior = Carbon::now()->subMonth()->startOfMonth();
        $fimMesAnterior = Carbon::now()->subMonth()->endOfMonth();

        // Aplicar filtro por role do usuário
        $user = auth()->user();

        // ===== MÉTRICAS PRINCIPAIS =====
        
        // 1. Cotações Ativas (em andamento) - com filtro por role
        $cotacoesAtivasQuery = Cotacao::where('status', 'em_andamento');
        if ($user->hasRole('comercial')) {
            $cotacoesAtivasQuery->where('user_id', $user->id);
        }
        $cotacoesAtivas = $cotacoesAtivasQuery->count();
        $cotacoesAtivasAnteriorQuery = Cotacao::where('status', 'em_andamento')
            ->where('created_at', '>=', $mesAnterior)
            ->where('created_at', '<=', $fimMesAnterior);
        if ($user->hasRole('comercial')) {
            $cotacoesAtivasAnteriorQuery->where('user_id', $user->id);
        }
        $cotacoesAtivasAnterior = $cotacoesAtivasAnteriorQuery->count();
        
        // 2. Cotações Aprovadas (este mês) - com filtro por role
        $cotacoesAprovadasQuery = CotacaoSeguradora::where('status', 'aprovada')
            ->where('created_at', '>=', $mesAtual);
        if ($user->hasRole('comercial')) {
            $cotacoesAprovadasQuery->whereHas('cotacao', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $cotacoesAprovadas = $cotacoesAprovadasQuery->count();

        $cotacoesAprovadasAnteriorQuery = CotacaoSeguradora::where('status', 'aprovada')
            ->where('created_at', '>=', $mesAnterior)
            ->where('created_at', '<=', $fimMesAnterior);
        if ($user->hasRole('comercial')) {
            $cotacoesAprovadasAnteriorQuery->whereHas('cotacao', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $cotacoesAprovadasAnterior = $cotacoesAprovadasAnteriorQuery->count();

        // 3. Cotações Pendentes (aguardando + em_analise) - com filtro por role
        $cotacoesPendentesQuery = CotacaoSeguradora::whereIn('status', ['aguardando', 'em_analise']);
        if ($user->hasRole('comercial')) {
            $cotacoesPendentesQuery->whereHas('cotacao', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $cotacoesPendentes = $cotacoesPendentesQuery->count();

        // 4. Clientes Ativos (segurados com cotações nos últimos 30 dias) - com filtro por role
        $clientesAtivosQuery = Segurado::whereHas('cotacoes', function($query) use ($user) {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
            if ($user->hasRole('comercial')) {
                $query->where('user_id', $user->id);
            }
        });
        $clientesAtivos = $clientesAtivosQuery->count();
        
        // Clientes Novos (este mês) - com filtro por role
        $clientesNovosQuery = Segurado::where('created_at', '>=', $mesAtual);
        if ($user->hasRole('comercial')) {
            $clientesNovosQuery->whereHas('cotacoes', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $clientesNovos = $clientesNovosQuery->count();

        // ===== COTAÇÕES RECENTES =====
        $cotacoesRecentesQuery = Cotacao::with(['segurado', 'produto', 'cotacaoSeguradoras']);
        if ($user->hasRole('comercial')) {
            $cotacoesRecentesQuery->where('user_id', $user->id);
        }
        $cotacoesRecentes = $cotacoesRecentesQuery->latest()
            ->limit(5)
            ->get()
            ->map(function($cotacao) {
                $melhorProposta = $cotacao->getMelhorProposta();
                return [
                    'id' => $cotacao->id,
                    'cliente_nome' => $cotacao->segurado->nome ?? 'N/A',
                    'produto_nome' => $cotacao->produto->nome ?? 'N/A',
                    'status' => $cotacao->status_exibicao,
                    'status_formatado' => $cotacao->status_exibicao_formatado,
                    'status_classe' => $cotacao->status_exibicao_classe,
                    'valor' => $melhorProposta ? $melhorProposta->valor_premio : null,
                    'created_at' => $cotacao->created_at,
                    'route' => route('cotacoes.show', $cotacao->id)
                ];
            });

        // ===== ATIVIDADES RECENTES =====
        $atividadesRecentesQuery = AtividadeCotacao::with(['cotacao.segurado', 'user']);
        if ($user->hasRole('comercial')) {
            $atividadesRecentesQuery->whereHas('cotacao', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $atividadesRecentes = $atividadesRecentesQuery->latest()
            ->limit(5)
            ->get()
            ->map(function($atividade) {
                return [
                    'descricao' => $atividade->descricao,
                    'cliente' => $atividade->cotacao->segurado->nome ?? 'N/A',
                    'usuario' => $atividade->user->name ?? 'Sistema',
                    'created_at' => $atividade->created_at,
                    'tipo_icon' => $this->getTipoIcon($atividade->tipo),
                    'tipo_classe' => $this->getTipoClasse($atividade->tipo)
                ];
            });

        // ===== MÉTRICAS PERFORMANCE =====
        
        // Taxa de aprovação (últimos 30 dias) - com filtro por role
        $totalRespostasQuery = CotacaoSeguradora::whereNotNull('data_retorno')
            ->where('data_retorno', '>=', Carbon::now()->subDays(30));
        if ($user->hasRole('comercial')) {
            $totalRespostasQuery->whereHas('cotacao', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $totalRespostas = $totalRespostasQuery->count();

        $totalAprovadasQuery = CotacaoSeguradora::where('status', 'aprovada')
            ->where('data_retorno', '>=', Carbon::now()->subDays(30));
        if ($user->hasRole('comercial')) {
            $totalAprovadasQuery->whereHas('cotacao', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $totalAprovadas = $totalAprovadasQuery->count();
        $taxaAprovacao = $totalRespostas > 0 ? round(($totalAprovadas / $totalRespostas) * 100) : 0;

        // Receita do mês (soma dos prêmios aprovados) - com filtro por role
        $receitaMesQuery = CotacaoSeguradora::where('status', 'aprovada')
            ->where('created_at', '>=', $mesAtual)
            ->whereNotNull('valor_premio');
        if ($user->hasRole('comercial')) {
            $receitaMesQuery->whereHas('cotacao', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $receitaMes = $receitaMesQuery->sum('valor_premio');

        // Tempo médio de resposta (em dias) - com filtro por role
        $tempoMedioQuery = CotacaoSeguradora::whereNotNull('data_envio')
            ->whereNotNull('data_retorno')
            ->where('data_retorno', '>=', Carbon::now()->subDays(30));
        if ($user->hasRole('comercial')) {
            $tempoMedioQuery->whereHas('cotacao', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $tempoMedio = $tempoMedioQuery->selectRaw('AVG(DATEDIFF(data_retorno, data_envio)) as tempo_medio')
            ->first()
            ->tempo_medio ?? 0;

        // ===== CÁLCULO DE VARIAÇÕES =====
        $variacaoAtivas = $this->calcularVariacao($cotacoesAtivas, $cotacoesAtivasAnterior);
        $variacaoAprovadas = $this->calcularVariacao($cotacoesAprovadas, $cotacoesAprovadasAnterior);

        return view('home', compact(
            'cotacoesAtivas',
            'cotacoesAprovadas', 
            'cotacoesPendentes',
            'clientesAtivos',
            'clientesNovos',
            'cotacoesRecentes',
            'atividadesRecentes',
            'taxaAprovacao',
            'receitaMes',
            'tempoMedio',
            'variacaoAtivas',
            'variacaoAprovadas'
        ));
    }

    /**
     * Calcular variação percentual entre dois períodos
     */
    private function calcularVariacao($valorAtual, $valorAnterior)
    {
        if ($valorAnterior == 0) {
            return $valorAtual > 0 ? 100 : 0;
        }
        
        return round((($valorAtual - $valorAnterior) / $valorAnterior) * 100);
    }

    /**
     * Obter ícone baseado no tipo de atividade
     */
    private function getTipoIcon($tipo)
    {
        switch($tipo) {
            case 'cotacao':
                return 'bi-plus-circle';
            case 'seguradora':
                return 'bi-building';
            case 'status':
                return 'bi-arrow-repeat';
            case 'aprovacao':
                return 'bi-check-circle';
            case 'rejeicao':
                return 'bi-x-circle';
            default:
                return 'bi-info-circle';
        }
    }

    /**
     * Obter classe CSS baseada no tipo de atividade
     */
    private function getTipoClasse($tipo)
    {
        switch($tipo) {
            case 'cotacao':
                return 'primary';
            case 'seguradora':
                return 'info';
            case 'aprovacao':
                return 'success';
            case 'rejeicao':
                return 'danger';
            case 'status':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}
