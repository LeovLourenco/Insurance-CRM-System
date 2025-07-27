<?php

namespace App\Http\Controllers;

use App\Models\Cotacao;
use App\Models\Corretora;
use App\Models\Produto;
use App\Models\Segurado;
use App\Models\Seguradora;
use Illuminate\Http\Request;

class CotacaoController extends Controller
{
    public function index(Request $request)
    {
        // Query base com relacionamentos
        $query = Cotacao::with([
            'corretora', 
            'produto', 
            'segurado', 
            'cotacaoSeguradoras'
        ]);

        // Filtro por status geral (da tabela)
        if ($request->filled('status_geral')) {
            $query->where('status', $request->status_geral);
        }

        // Filtro por corretora
        if ($request->filled('corretora_id')) {
            $query->where('corretora_id', $request->corretora_id);
        }

        // Filtro por produto  
        if ($request->filled('produto_id')) {
            $query->where('produto_id', $request->produto_id);
        }

        // Filtro por busca (nome do segurado)
        if ($request->filled('busca')) {
            $query->whereHas('segurado', function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->busca . '%');
            });
        }

        // Filtro por status consolidado (das seguradoras)
        if ($request->filled('status_consolidado')) {
            $statusFiltro = $request->status_consolidado;
            
            $query->whereHas('cotacaoSeguradoras', function($q) use ($statusFiltro) {
                $q->where('status', $statusFiltro);
            });
        }

        // Debug para ver os filtros aplicados
        \Log::info('Filtros aplicados', [
            'status_geral' => $request->status_geral,
            'corretora_id' => $request->corretora_id,
            'produto_id' => $request->produto_id,
            'busca' => $request->busca,
            'status_consolidado' => $request->status_consolidado,
            'query_sql' => $query->toSql(),
            'query_bindings' => $query->getBindings()
        ]);

        // Ordenação
        $cotacoes = $query->orderBy('created_at', 'desc')->paginate(15);

        // NÃO manter filtros na paginação (para não ficar "grudado")
        // $cotacoes->appends($request->query());

        // Métricas para o dashboard
        $metricas = $this->calcularMetricas();

        return view('cotacoes.index', compact('cotacoes', 'metricas'));
    }

    /**
     * Calcula métricas para o dashboard usando o novo sistema de status
     */
    private function calcularMetricas()
    {
        $total = Cotacao::count();
        $emAndamento = Cotacao::where('status', 'em_andamento')->count();
        $finalizadas = Cotacao::where('status', 'finalizada')->count();
        $canceladas = Cotacao::where('status', 'cancelada')->count();

        // Taxa de sucesso: cotações com pelo menos uma seguradora aprovada
        $cotacoesComAprovacao = Cotacao::whereHas('cotacaoSeguradoras', function($q) {
            $q->where('status', 'aprovada');
        })->count();

        $taxaSucesso = $total > 0 ? round(($cotacoesComAprovacao / $total) * 100, 1) : 0;

        // Métricas adicionais por status consolidado
        $metricasDetalhadas = [
            'aguardando' => 0,
            'em_analise' => 0,
            'aprovadas' => 0,
            'rejeitadas' => 0
        ];

        // Contar cotações por status consolidado (apenas as em andamento)
        $cotacoesEmAndamento = Cotacao::where('status', 'em_andamento')
            ->with('cotacaoSeguradoras')
            ->get();

        foreach ($cotacoesEmAndamento as $cotacao) {
            $statusConsolidado = $cotacao->status_consolidado;
            if (isset($metricasDetalhadas[$statusConsolidado])) {
                $metricasDetalhadas[$statusConsolidado]++;
            }
        }

        return [
            'total' => $total,
            'em_andamento' => $emAndamento,
            'finalizadas' => $finalizadas,
            'canceladas' => $canceladas,
            'taxa_sucesso' => $taxaSucesso,
            'detalhadas' => $metricasDetalhadas
        ];
    }

    public function create(Request $request)
    {
        $corretoras = Corretora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $segurados = Segurado::orderBy('nome')->get();
        
        // Capturar parâmetros de pré-seleção da URL
        $corretoraId = $request->input('corretora_id');
        $produtoId = $request->input('produto_id');
        $seguradoId = $request->input('segurado_id');
        
        // Validar se os IDs existem (para segurança)
        if ($corretoraId && !$corretoras->contains('id', $corretoraId)) {
            $corretoraId = null;
        }
        
        if ($produtoId && !$produtos->contains('id', $produtoId)) {
            $produtoId = null;
        }
        
        if ($seguradoId && !$segurados->contains('id', $seguradoId)) {
            $seguradoId = null;
        }

        return view('cotacoes.create', compact(
            'corretoras', 
            'produtos', 
            'segurados', 
            'corretoraId', 
            'produtoId', 
            'seguradoId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'corretora_id' => 'required|exists:corretoras,id',
            'produto_id' => 'required|exists:produtos,id',
            'segurado_id' => 'required|exists:segurados,id',
            'seguradoras' => 'required|array|min:1',
            'seguradoras.*' => 'exists:seguradoras,id',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        // Criar cotação master
        $cotacao = Cotacao::create([
            'corretora_id' => $request->corretora_id,
            'produto_id' => $request->produto_id,
            'segurado_id' => $request->segurado_id,
            'observacoes' => $request->observacoes,
            'status' => 'em_andamento'
        ]);

        // Criar registros para cada seguradora
        foreach ($request->seguradoras as $seguradoraId) {
            $cotacao->cotacaoSeguradoras()->create([
                'seguradora_id' => $seguradoraId,
                'status' => 'aguardando'
            ]);
        }

        // Registrar atividade
        $cotacao->atividades()->create([
            'user_id' => auth()->id(),
            'tipo' => 'geral',
            'descricao' => 'Cotação criada para ' . count($request->seguradoras) . ' seguradora(s)'
        ]);

        return redirect()
            ->route('cotacoes.show', $cotacao->id)
            ->with('success', 'Cotação criada com sucesso!');
    }

    public function show($id)
    {
        $cotacao = Cotacao::with([
            'corretora',
            'produto', 
            'segurado',
            'cotacaoSeguradoras.seguradora',
            'atividades.user'
        ])->findOrFail($id);

        return view('cotacoes.show', compact('cotacao'));
    }

    public function edit($id)
    {
        $cotacao = Cotacao::with('cotacaoSeguradoras')->findOrFail($id);
        
        // Verificar se pode editar
        if (!$cotacao->pode_editar) {
            return redirect()
                ->route('cotacoes.show', $cotacao->id)
                ->with('error', 'Esta cotação não pode ser editada.');
        }

        $corretoras = Corretora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $segurados = Segurado::orderBy('nome')->get();

        return view('cotacoes.edit', compact('cotacao', 'corretoras', 'produtos', 'segurados'));
    }

    public function update(Request $request, $id)
    {
        $cotacao = Cotacao::findOrFail($id);
        
        // Verificar se pode editar
        if (!$cotacao->pode_editar) {
            return redirect()
                ->route('cotacoes.show', $cotacao->id)
                ->with('error', 'Esta cotação não pode ser editada.');
        }

        $request->validate([
            'observacoes' => 'nullable|string|max:1000',
            'status' => 'required|in:em_andamento,finalizada,cancelada'
        ]);

        $statusAnterior = $cotacao->status;
        
        $cotacao->update([
            'observacoes' => $request->observacoes,
            'status' => $request->status
        ]);

        // Registrar mudança de status
        if ($statusAnterior !== $request->status) {
            $cotacao->atividades()->create([
                'user_id' => auth()->id(),
                'tipo' => 'geral',
                'descricao' => "Status alterado de '{$statusAnterior}' para '{$request->status}'"
            ]);
        }

        return redirect()
            ->route('cotacoes.show', $cotacao->id)
            ->with('success', 'Cotação atualizada com sucesso!');
    }

    /**
     * Busca seguradoras elegíveis baseada na corretora e produto selecionados
     * Rota: GET /cotacoes/api/seguradoras
     */
    public function seguradoras(Request $request)
    {
        $corretoraId = $request->input('corretora_id');
        $produtoId = $request->input('produto_id');

        // Log para debug
        \Log::info('Buscando seguradoras', [
            'corretora_id' => $corretoraId,
            'produto_id' => $produtoId
        ]);

        if (!$corretoraId || !$produtoId) {
            return response()->json([
                'seguradoras' => [],
                'message' => 'Corretora e produto são obrigatórios'
            ]);
        }

        try {
            // Buscar seguradoras que têm vínculo com a corretora E o produto
            $seguradoras = Seguradora::whereHas('corretoras', function($q) use ($corretoraId) {
                $q->where('corretora_id', $corretoraId);
            })
            ->whereHas('produtos', function($q) use ($produtoId) {
                $q->where('produto_id', $produtoId);
            })
            ->orderBy('nome')
            ->get(['id', 'nome']);

            \Log::info('Seguradoras encontradas', [
                'count' => $seguradoras->count(),
                'seguradoras' => $seguradoras->pluck('nome')->toArray()
            ]);

            return response()->json([
                'seguradoras' => $seguradoras,
                'total' => $seguradoras->count(),
                'message' => $seguradoras->count() > 0 
                    ? "Encontradas {$seguradoras->count()} seguradoras elegíveis" 
                    : 'Nenhuma seguradora encontrada para esta combinação'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar seguradoras', [
                'error' => $e->getMessage(),
                'corretora_id' => $corretoraId,
                'produto_id' => $produtoId
            ]);

            return response()->json([
                'seguradoras' => [],
                'error' => 'Erro interno do servidor',
                'message' => 'Erro ao consultar seguradoras. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Atualizar status de uma cotação-seguradora específica
     */
    public function atualizarStatusSeguradora(Request $request, $cotacaoId, $seguradoraId)
    {
        $request->validate([
            'status' => 'required|in:aguardando,em_analise,aprovada,rejeitada,repique',
            'observacoes' => 'nullable|string|max:500',
            'valor_premio' => 'nullable|numeric|min:0',
            'valor_is' => 'nullable|numeric|min:0'
        ]);

        $cotacao = Cotacao::findOrFail($cotacaoId);
        $cotacaoSeguradora = $cotacao->cotacaoSeguradoras()
            ->where('seguradora_id', $seguradoraId)
            ->firstOrFail();

        $statusAnterior = $cotacaoSeguradora->status;

        $cotacaoSeguradora->update([
            'status' => $request->status,
            'observacoes' => $request->observacoes,
            'valor_premio' => $request->valor_premio,
            'valor_is' => $request->valor_is,
            'data_retorno' => now()
        ]);

        // Registrar atividade específica da seguradora
        $cotacao->atividades()->create([
            'cotacao_seguradora_id' => $cotacaoSeguradora->id,
            'user_id' => auth()->id(),
            'tipo' => 'seguradora',
            'descricao' => "Status da {$cotacaoSeguradora->seguradora->nome} alterado: {$statusAnterior} → {$request->status}"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso!',
            'status_consolidado' => $cotacao->fresh()->status_consolidado
        ]);
    }

    /**
     * Enviar cotação para todas as seguradoras
     */
    public function enviarTodas($id)
    {
        $cotacao = Cotacao::with('cotacaoSeguradoras')->findOrFail($id);
        
        if (!$cotacao->pode_enviar) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cotação não pode ser enviada.'
            ], 400);
        }

        $enviadas = 0;
        
        foreach ($cotacao->cotacaoSeguradoras as $cotacaoSeguradora) {
            if ($cotacaoSeguradora->status === 'aguardando') {
                $cotacaoSeguradora->update([
                    'data_envio' => now(),
                    'status' => 'em_analise'
                ]);
                $enviadas++;
            }
        }

        // Registrar atividade
        $cotacao->atividades()->create([
            'user_id' => auth()->id(),
            'tipo' => 'geral',
            'descricao' => "Cotação enviada para {$enviadas} seguradora(s)"
        ]);

        return response()->json([
            'success' => true,
            'message' => "Cotação enviada para {$enviadas} seguradora(s)!",
            'status_consolidado' => $cotacao->fresh()->status_consolidado
        ]);
    }
}