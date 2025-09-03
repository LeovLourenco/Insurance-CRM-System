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

        // ===== MÉTRICAS PRINCIPAIS =====
        
        // 1. Cotações Ativas (em andamento)
        $cotacoesAtivas = Cotacao::where('status', 'em_andamento')->count();
        $cotacoesAtivasAnterior = Cotacao::where('status', 'em_andamento')
            ->where('created_at', '>=', $mesAnterior)
            ->where('created_at', '<=', $fimMesAnterior)
            ->count();
        
        // 2. Cotações Aprovadas (este mês)
        $cotacoesAprovadas = CotacaoSeguradora::where('status', 'aprovada')
            ->where('created_at', '>=', $mesAtual)
            ->count();
        $cotacoesAprovadasAnterior = CotacaoSeguradora::where('status', 'aprovada')
            ->where('created_at', '>=', $mesAnterior)
            ->where('created_at', '<=', $fimMesAnterior)
            ->count();

        // 3. Cotações Pendentes (aguardando + em_analise)
        $cotacoesPendentes = CotacaoSeguradora::whereIn('status', ['aguardando', 'em_analise'])->count();

        // 4. Clientes Ativos (segurados com cotações nos últimos 30 dias)
        $clientesAtivos = Segurado::whereHas('cotacoes', function($query) {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        })->count();
        $clientesNovos = Segurado::where('created_at', '>=', $mesAtual)->count();

        // ===== COTAÇÕES RECENTES =====
        $cotacoesRecentes = Cotacao::with(['segurado', 'produto', 'cotacaoSeguradoras'])
            ->latest()
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
        $atividadesRecentes = AtividadeCotacao::with(['cotacao.segurado', 'user'])
            ->latest()
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
        
        // Taxa de aprovação (últimos 30 dias)
        $totalRespostas = CotacaoSeguradora::whereNotNull('data_retorno')
            ->where('data_retorno', '>=', Carbon::now()->subDays(30))
            ->count();
        $totalAprovadas = CotacaoSeguradora::where('status', 'aprovada')
            ->where('data_retorno', '>=', Carbon::now()->subDays(30))
            ->count();
        $taxaAprovacao = $totalRespostas > 0 ? round(($totalAprovadas / $totalRespostas) * 100) : 0;

        // Receita do mês (soma dos prêmios aprovados)
        $receitaMes = CotacaoSeguradora::where('status', 'aprovada')
            ->where('created_at', '>=', $mesAtual)
            ->whereNotNull('valor_premio')
            ->sum('valor_premio');

        // Tempo médio de resposta (em dias)
        $tempoMedio = CotacaoSeguradora::whereNotNull('data_envio')
            ->whereNotNull('data_retorno')
            ->where('data_retorno', '>=', Carbon::now()->subDays(30))
            ->selectRaw('AVG(DATEDIFF(data_retorno, data_envio)) as tempo_medio')
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
