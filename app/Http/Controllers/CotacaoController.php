<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotacao;
use App\Models\CotacaoSeguradora;
use App\Models\Corretora;
use App\Models\Produto;
use App\Models\Seguradora;
use App\Models\AtividadeCotacao;
use App\Models\Segurado;
use Illuminate\Support\Facades\DB;

class CotacaoController extends Controller
{
    /**
     * ATUALIZADO: Listagem com novo relacionamento master-detail
     */
    public function index()
    {
        $cotacoes = Cotacao::with([
            'corretora',
            'produto', 
            'segurado',
            'cotacaoSeguradoras.seguradora', // NOVO: Carregar seguradoras
            'atividades' => function ($query) {
                $query->latest()->with('user')->limit(3); // Só as 3 mais recentes
            }
        ])
        ->latest()
        ->get();

        return view('cotacoes.index', compact('cotacoes'));
    }

    /**
     * ATUALIZADO: Formulário de criação - STEP 1
     */
    public function create(Request $request)
    {
        $corretoraId = $request->input('corretora_id');
        $produtoId = $request->input('produto_id');

        $corretoras = Corretora::all();
        $produtos = Produto::all();
        $segurados = Segurado::all();

        // Se corretora e produto foram selecionados, buscar seguradoras elegíveis
        $seguradoras = collect();
        if ($corretoraId && $produtoId) {
            $seguradoras = $this->buscarSeguradoras($corretoraId, $produtoId);
        }

        return view('cotacoes.create', compact(
            'corretoras', 'produtos', 'segurados', 'seguradoras',
            'corretoraId', 'produtoId'
        ));
    }

    /**
     * NOVO: AJAX - Buscar seguradoras elegíveis
     */
    public function buscarSeguradoras($corretoraId, $produtoId)
    {
        // Buscar seguradoras vinculadas à corretora
        $seguradorasDaCorretora = DB::table('corretora_seguradora')
            ->where('corretora_id', $corretoraId)
            ->pluck('seguradora_id');

        // Buscar seguradoras que oferecem o produto
        $seguradorasComProduto = DB::table('seguradora_produto')
            ->where('produto_id', $produtoId)
            ->pluck('seguradora_id');

        // Interseção das seguradoras válidas
        $seguradorasIds = $seguradorasDaCorretora->intersect($seguradorasComProduto);

        // Carregar as seguradoras filtradas
        return Seguradora::whereIn('id', $seguradorasIds)->get();
    }

    /**
     * NOVO: AJAX - Retornar seguradoras em JSON
     */
    public function seguradoras(Request $request)
    {
        $corretoraId = $request->input('corretora_id');
        $produtoId = $request->input('produto_id');

        if (!$corretoraId || !$produtoId) {
            return response()->json(['seguradoras' => []]);
        }

        $seguradoras = $this->buscarSeguradoras($corretoraId, $produtoId);

        return response()->json([
            'seguradoras' => $seguradoras->map(function($seguradora) {
                return [
                    'id' => $seguradora->id,
                    'nome' => $seguradora->nome,
                    'logo' => $seguradora->logo ?? null
                ];
            })
        ]);
    }

    /**
     * NOVO: STEP 2 - Seleção de seguradoras
     */
    public function selecionarSeguradoras(Request $request)
    {
        $validated = $request->validate([
            'corretora_id' => 'required|exists:corretoras,id',
            'produto_id' => 'required|exists:produtos,id',
            'segurado_id' => 'required|exists:segurados,id',
            'observacoes' => 'nullable|string',
        ]);

        $seguradoras = $this->buscarSeguradoras(
            $validated['corretora_id'], 
            $validated['produto_id']
        );

        return view('cotacoes.selecionar-seguradoras', compact('validated', 'seguradoras'));
    }

    /**
     * REFATORADO: Criar cotação master + detail
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'corretora_id' => 'required|exists:corretoras,id',
            'produto_id' => 'required|exists:produtos,id',
            'segurado_id' => 'required|exists:segurados,id',
            'observacoes' => 'nullable|string',
            'seguradoras' => 'required|array|min:1', // NOVO: Array de seguradoras selecionadas
            'seguradoras.*' => 'exists:seguradoras,id',
        ]);

        DB::beginTransaction();
        
        try {
            // 1. Criar cotação MASTER (cabeçalho)
            $cotacao = Cotacao::create([
                'corretora_id' => $validated['corretora_id'],
                'produto_id' => $validated['produto_id'],
                'segurado_id' => $validated['segurado_id'],
                'observacoes' => $validated['observacoes'],
                'status' => 'em_andamento'
            ]);

            // 2. Criar cotações DETAIL para cada seguradora selecionada
            $cotacao->criarCotacoesSeguradoras($validated['seguradoras'], auth()->id());

            // 3. Registrar atividade geral
            $cotacao->adicionarAtividade(
                "Cotação #{$cotacao->id} criada com " . count($validated['seguradoras']) . " seguradoras por " . auth()->user()->name,
                auth()->id()
            );

            DB::commit();

            return redirect()
                ->route('cotacoes.show', $cotacao)
                ->with('success', 'Cotação criada com sucesso! Distribuída para ' . count($validated['seguradoras']) . ' seguradoras.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()
                ->withErrors(['error' => 'Erro ao criar cotação: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * NOVO: Visualizar cotação com timeline completa
     */
    public function show($id) // Receber ID diretamente
    {
        $cotacao = \App\Models\Cotacao::findOrFail($id);
        
        $cotacao->load([
            'corretora',
            'produto',
            'segurado',
            'cotacaoSeguradoras.seguradora',
            'atividades.user'
        ]);

        $timeline = collect();
        if (method_exists('App\Models\AtividadeCotacao', 'timelineCotacao')) {
            $timeline = \App\Models\AtividadeCotacao::timelineCotacao($cotacao->id);
        }

        return view('cotacoes.show', compact('cotacao', 'timeline'));
    }
    /**
     * NOVO: Atualizar status de uma cotação-seguradora específica
     */
    public function atualizarStatus(Request $request, Cotacao $cotacao, CotacaoSeguradora $cotacaoSeguradora)
    {
        $validated = $request->validate([
            'status' => 'required|in:aguardando,em_analise,aprovada,rejeitada,repique,cancelada',
            'observacoes' => 'nullable|string',
            'valor_premio' => 'nullable|numeric|min:0',
            'valor_is' => 'nullable|numeric|min:0',
        ]);

        $cotacaoSeguradora->update($validated);

        // Se está marcando como retorno, definir data_retorno
        if (in_array($validated['status'], ['aprovada', 'rejeitada', 'repique'])) {
            $cotacaoSeguradora->update(['data_retorno' => now()]);
        }

        // Registrar atividade
        $cotacaoSeguradora->adicionarAtividade(
            "Status alterado para '{$cotacaoSeguradora->status_formatado}'" . 
            ($validated['observacoes'] ? " - {$validated['observacoes']}" : ""),
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso!',
            'status_formatado' => $cotacaoSeguradora->status_formatado,
            'status_classe' => $cotacaoSeguradora->status_classe
        ]);
    }

    /**
     * NOVO: Enviar cotação para seguradora específica
     */
    public function enviar(Request $request, Cotacao $cotacao, CotacaoSeguradora $cotacaoSeguradora)
    {
        if ($cotacaoSeguradora->data_envio) {
            return response()->json([
                'success' => false,
                'message' => 'Cotação já foi enviada para esta seguradora.'
            ]);
        }

        $cotacaoSeguradora->marcarComoEnviada(auth()->id());

        return response()->json([
            'success' => true,
            'message' => "Cotação enviada para {$cotacaoSeguradora->seguradora->nome}!",
            'data_envio' => $cotacaoSeguradora->data_envio->format('d/m/Y H:i')
        ]);
    }

    /**
     * NOVO: Enviar para todas as seguradoras de uma cotação
     */
    public function enviarTodas(Cotacao $cotacao)
    {
        $pendentes = $cotacao->cotacaoSeguradoras()
                            ->whereNull('data_envio')
                            ->get();

        if ($pendentes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Todas as seguradoras já receberam a cotação.'
            ]);
        }

        foreach ($pendentes as $cotacaoSeguradora) {
            $cotacaoSeguradora->marcarComoEnviada(auth()->id());
        }

        $cotacao->adicionarAtividade(
            "Cotação enviada para " . $pendentes->count() . " seguradoras em lote",
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => "Cotação enviada para {$pendentes->count()} seguradoras!"
        ]);
    }

    /**
     * NOVO: Dashboard de métricas
     */
    public function dashboard()
    {
        $metricas = [
            'total_cotacoes' => Cotacao::count(),
            'cotacoes_pendentes' => Cotacao::pendentes()->count(),
            'cotacoes_finalizadas' => Cotacao::totalmenteFinalizadas()->count(),
            'aprovacoes_mes' => CotacaoSeguradora::aprovadas()
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'taxa_aprovacao' => $this->calcularTaxaAprovacao(),
            'seguradoras_performance' => $this->performanceSeguradoras(),
            'atividades_recentes' => AtividadeCotacao::with(['user', 'cotacao', 'cotacaoSeguradora.seguradora'])
                ->latest()
                ->limit(10)
                ->get()
        ];

        return view('cotacoes.dashboard', compact('metricas'));
    }

    /**
     * NOVO: Calcular taxa de aprovação geral
     */
    private function calcularTaxaAprovacao($periodo = 30)
    {
        $total = CotacaoSeguradora::where('created_at', '>=', now()->subDays($periodo))->count();
        
        if ($total == 0) return 0;

        $aprovadas = CotacaoSeguradora::aprovadas()
                                    ->where('created_at', '>=', now()->subDays($periodo))
                                    ->count();

        return round(($aprovadas / $total) * 100, 1);
    }

    /**
     * NOVO: Performance por seguradora
     */
    private function performanceSeguradoras($periodo = 30)
    {
        return CotacaoSeguradora::with('seguradora')
                                ->where('created_at', '>=', now()->subDays($periodo))
                                ->selectRaw('
                                    seguradora_id,
                                    COUNT(*) as total,
                                    SUM(CASE WHEN status = "aprovada" THEN 1 ELSE 0 END) as aprovadas,
                                    AVG(CASE 
                                        WHEN data_envio IS NOT NULL AND data_retorno IS NOT NULL 
                                        THEN DATEDIFF(data_retorno, data_envio) 
                                        ELSE NULL 
                                    END) as tempo_medio_resposta
                                ')
                                ->groupBy('seguradora_id')
                                ->orderByDesc('aprovadas')
                                ->get();
    }

    /**
     * NOVO: Relatório detalhado
     */
    public function relatorio(Request $request)
    {
        $filtros = $request->only(['corretora_id', 'produto_id', 'seguradora_id', 'status', 'data_inicio', 'data_fim']);

        $query = CotacaoSeguradora::with(['cotacao.corretora', 'cotacao.produto', 'cotacao.segurado', 'seguradora']);

        // Aplicar filtros
        if ($filtros['corretora_id']) {
            $query->whereHas('cotacao', function($q) use ($filtros) {
                $q->where('corretora_id', $filtros['corretora_id']);
            });
        }

        if ($filtros['seguradora_id']) {
            $query->where('seguradora_id', $filtros['seguradora_id']);
        }

        if ($filtros['status']) {
            $query->where('status', $filtros['status']);
        }

        if ($filtros['data_inicio']) {
            $query->where('created_at', '>=', $filtros['data_inicio']);
        }

        if ($filtros['data_fim']) {
            $query->where('created_at', '<=', $filtros['data_fim']);
        }

        $cotacoes = $query->latest()->paginate(50);

        return view('cotacoes.relatorio', compact('cotacoes', 'filtros'));
    }
    // No CotacaoController.php, adicionar:

    public function edit($id)
    {
        $cotacao = \App\Models\Cotacao::findOrFail($id);
        
        $cotacao->load([
            'corretora',
            'produto',
            'segurado',
            'cotacaoSeguradoras.seguradora'
        ]);

        // Buscar seguradoras elegíveis (para adicionar novas)
        $seguradoras = $this->buscarSeguradoras($cotacao->corretora_id, $cotacao->produto_id);
        
        // Seguradoras já cotadas (para não duplicar)
        $seguradorasJaCotadas = $cotacao->cotacaoSeguradoras->pluck('seguradora_id')->toArray();
        
        // Seguradoras disponíveis para adicionar
        $seguradoresDisponiveis = $seguradoras->whereNotIn('id', $seguradorasJaCotadas);
        
        $segurados = \App\Models\Segurado::all();

        return view('cotacoes.edit', compact('cotacao', 'seguradoresDisponiveis', 'segurados'));
    }

    public function update(Request $request, $id)
    {
        $cotacao = \App\Models\Cotacao::findOrFail($id);
        
        $validated = $request->validate([
            'segurado_id' => 'required|exists:segurados,id',
            'observacoes' => 'nullable|string',
            'status' => 'required|string',
            'novas_seguradoras' => 'nullable|array',
            'novas_seguradoras.*' => 'exists:seguradoras,id',
            'remover_seguradoras' => 'nullable|array',
            'remover_seguradoras.*' => 'exists:cotacao_seguradoras,id'
        ]);

        \DB::beginTransaction();
        
        try {
            // Atualizar dados básicos
            $cotacao->update([
                'segurado_id' => $validated['segurado_id'],
                'observacoes' => $validated['observacoes'],
                'status' => $validated['status']
            ]);

            // Adicionar novas seguradoras
            if (!empty($validated['novas_seguradoras'])) {
                foreach ($validated['novas_seguradoras'] as $seguradoraId) {
                    \App\Models\CotacaoSeguradora::create([
                        'cotacao_id' => $cotacao->id,
                        'seguradora_id' => $seguradoraId,
                        'status' => 'aguardando'
                    ]);
                }
                
                $cotacao->adicionarAtividade(
                    "Adicionadas " . count($validated['novas_seguradoras']) . " seguradoras à cotação",
                    auth()->id()
                );
            }

            // Remover seguradoras
            if (!empty($validated['remover_seguradoras'])) {
                \App\Models\CotacaoSeguradora::whereIn('id', $validated['remover_seguradoras'])->delete();
                
                $cotacao->adicionarAtividade(
                    "Removidas " . count($validated['remover_seguradoras']) . " seguradoras da cotação",
                    auth()->id()
                );
            }

            // Registrar atividade de edição
            $cotacao->adicionarAtividade(
                "Cotação editada por " . auth()->user()->name,
                auth()->id()
            );

            \DB::commit();

            return redirect()
                ->route('cotacoes.show', $cotacao->id)
                ->with('success', 'Cotação atualizada com sucesso!');

        } catch (\Exception $e) {
            \DB::rollback();
            
            return back()
                ->withErrors(['error' => 'Erro ao atualizar cotação: ' . $e->getMessage()])
                ->withInput();
        }
    }
}