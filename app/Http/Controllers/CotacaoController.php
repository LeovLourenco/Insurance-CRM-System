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
    public function __construct()
    {
        $this->authorizeResource(Cotacao::class, 'cotacao');
    }

    public function index(Request $request)
    {
        // Query base com relacionamentos
        $query = Cotacao::with([
            'corretora', 
            'produto', 
            'segurado', 
            'cotacaoSeguradoras',
            'user' // Para exibir responsável da cotação
        ]);

        // Aplicar filtro por role
        $user = auth()->user();
        if ($user->hasRole('comercial')) {
            // Comerciais só veem suas próprias cotações
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('diretor')) {
            // Diretores veem todas (já aplicado automaticamente)
        } elseif ($user->hasRole('admin')) {
            // Admins veem todas (já aplicado automaticamente)
        }

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
        $cotacoes = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // ✅ CORRIGIDO: Mantém filtros na paginação para UX consistente

        // Métricas para o dashboard
        $metricas = $this->calcularMetricas();

        return view('cotacoes.index', compact('cotacoes', 'metricas'));
    }

    /**
     * Calcula métricas para o dashboard usando o novo sistema de status
     */
    private function calcularMetricas()
    {
        $user = auth()->user();
        
        // Aplicar filtro por role
        $queryBase = Cotacao::query();
        if ($user->hasRole('comercial')) {
            $queryBase->where('user_id', $user->id);
        }

        $total = $queryBase->count();
        $emAndamento = (clone $queryBase)->where('status', 'em_andamento')->count();
        $finalizadas = (clone $queryBase)->where('status', 'finalizada')->count();
        $canceladas = (clone $queryBase)->where('status', 'cancelada')->count();

        // Taxa de sucesso: cotações com pelo menos uma seguradora aprovada (filtrar por role)
        $cotacoesComAprovacaoQuery = (clone $queryBase)->whereHas('cotacaoSeguradoras', function($q) {
            $q->where('status', 'aprovada');
        });
        $cotacoesComAprovacao = $cotacoesComAprovacaoQuery->count();

        $taxaSucesso = $total > 0 ? round(($cotacoesComAprovacao / $total) * 100, 1) : 0;

        // Métricas adicionais por status consolidado
        $metricasDetalhadas = [
            'aguardando' => 0,
            'em_analise' => 0,
            'aprovadas' => 0,
            'rejeitadas' => 0
        ];

        // Contar cotações por status consolidado (apenas as em andamento) - filtrar por role
        $cotacoesEmAndamentoQuery = (clone $queryBase)->where('status', 'em_andamento')
            ->with('cotacaoSeguradoras');
        $cotacoesEmAndamento = $cotacoesEmAndamentoQuery->get();

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
        // ✅ ENTIDADES BASE: Todos veem todas (arquitetura correta)
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
        // ✅ VALIDAÇÃO DUPLA DE SEGURANÇA
        $this->authorize('create', Cotacao::class);

        // ⚠️ SEGURANÇA CRÍTICA: FORÇAR user_id do usuário autenticado
        $user = $request->user();
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não autenticado.');
        }

        // Validação com regra customizada para comerciais
        $rules = [
            'corretora_id' => 'required|exists:corretoras,id',
            'produto_id' => 'required|exists:produtos,id',
            'segurado_id' => 'required|exists:segurados,id',
            'seguradoras' => 'required|array|min:1',
            'seguradoras.*' => 'exists:seguradoras,id',
            'observacoes' => 'nullable|string|max:1000'
        ];

        // ✅ CORE OPERACIONAL: DIRETOR e COMERCIAL só podem criar cotações para corretoras que atendem
        if ($user->hasRole(['comercial', 'diretor'])) {
            $rules['corretora_id'] = [
                'required',
                'exists:corretoras,id',
                function ($attribute, $value, $fail) use ($user) {
                    $corretora = Corretora::find($value);
                    if (!$corretora || $corretora->usuario_id !== $user->id) {
                        \Log::warning('Tentativa de criar cotação para corretora não própria', [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'user_role' => $user->getRoleNames()->first(),
                            'corretora_id' => $value,
                            'corretora_usuario_id' => $corretora?->usuario_id,
                            'ip' => request()->ip()
                        ]);
                        $fail('Você só pode criar cotações para corretoras que lhe pertencem.');
                    }
                }
            ];
        }

        $request->validate($rules);
        $userId = $user->id;
        
        $cotacaoData = [
            'corretora_id' => $request->corretora_id,
            'produto_id' => $request->produto_id,
            'segurado_id' => $request->segurado_id,
            'observacoes' => $request->observacoes,
            'status' => 'em_andamento',
            'user_id' => $userId // ← SEMPRE do usuário logado
        ];

        // ⚠️ NUNCA permitir user_id via request (proteção contra ataques)
        unset($request->user_id);

        // Criar cotação master
        $cotacao = Cotacao::create($cotacaoData);

        // Criar registros para cada seguradora
        foreach ($request->seguradoras as $seguradoraId) {
            $cotacao->cotacaoSeguradoras()->create([
                'seguradora_id' => $seguradoraId,
                'status' => 'aguardando'
            ]);
        }

        // Registrar atividade
        $cotacao->atividades()->create([
            'user_id' => $userId,
            'tipo' => 'geral',
            'descricao' => 'Cotação criada para ' . count($request->seguradoras) . ' seguradora(s)'
        ]);

        return redirect()
            ->route('cotacoes.show', $cotacao->id)
            ->with('success', 'Cotação criada com sucesso!');
    }

    public function show(Cotacao $cotacao)
    {
        // Carregar relacionamentos necessários
        $cotacao->load([
            'corretora',
            'produto', 
            'segurado',
            'cotacaoSeguradoras.seguradora',
            'atividades.user',
            'user' // Para exibir responsável da cotação
        ]);

        return view('cotacoes.show', compact('cotacao'));
    }

    public function edit($id)
    {
        $cotacao = Cotacao::with(['cotacaoSeguradoras', 'corretora', 'produto'])->findOrFail($id);
        
        // ✅ VALIDAÇÃO DUPLA DE SEGURANÇA
        $this->authorize('update', $cotacao);
        
        // Verificar se pode editar
        if (!$cotacao->pode_editar) {
            return redirect()
                ->route('cotacoes.show', $cotacao->id)
                ->with('error', 'Esta cotação não pode ser editada.');
        }

        // ✅ ENTIDADES BASE: Todos veem todas (arquitetura correta)
        $corretoras = Corretora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $segurados = Segurado::orderBy('nome')->get();
        
        // BUSCAR SEGURADORAS ELEGÍVEIS (mesma lógica do método seguradoras())
        $seguradoresJaCotadas = $cotacao->cotacaoSeguradoras->pluck('seguradora_id');
        
        $seguradoresDisponiveis = Seguradora::whereHas('corretoras', function($q) use ($cotacao) {
                $q->where('corretora_id', $cotacao->corretora_id);
            })
            ->whereHas('produtos', function($q) use ($cotacao) {
                $q->where('produto_id', $cotacao->produto_id);
            })
            ->whereNotIn('id', $seguradoresJaCotadas)
            ->orderBy('nome')
            ->get();

        return view('cotacoes.edit', compact(
            'cotacao', 
            'corretoras', 
            'produtos', 
            'segurados', 
            'seguradoresDisponiveis'
        ));
    }

    public function update(Request $request, $id)
    {
        $cotacao = Cotacao::findOrFail($id);
        
        // ✅ VALIDAÇÃO DUPLA DE SEGURANÇA
        $this->authorize('update', $cotacao);
        
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
        
        // ⚠️ SEGURANÇA: Nunca permitir alteração de user_id ou outros campos críticos
        $dadosPermitidos = [
            'observacoes' => $request->observacoes,
            'status' => $request->status
        ];
        
        $cotacao->update($dadosPermitidos);

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

    // ===== MÉTODOS ADICIONAIS PARA COTACAOCONTROLLER =====
    // Adicionar estes métodos ao seu CotacaoController existente

    /**
     * Atualizar status da cotação (para index.blade.php)
     * Rota: PATCH /cotacoes/{cotacao}/status
     */
    public function updateStatus(Request $request, Cotacao $cotacao)
    {
        $request->validate([
            'status' => 'required|in:em_andamento,finalizada,cancelada'
        ]);

        // Verificar se pode alterar status
        if (!in_array($cotacao->status, ['em_andamento'])) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cotação não pode ter seu status alterado.'
            ], 400);
        }

        $statusAnterior = $cotacao->status;
        
        $cotacao->update([
            'status' => $request->status
        ]);

        // Registrar atividade
        $cotacao->atividades()->create([
            'user_id' => auth()->id(),
            'tipo' => 'geral',
            'descricao' => "Status alterado de '{$statusAnterior}' para '{$request->status}'"
        ]);

        $mensagem = match($request->status) {
            'finalizada' => 'Cotação finalizada com sucesso!',
            'cancelada' => 'Cotação cancelada.',
            default => 'Status atualizado com sucesso!'
        };

        return response()->json([
            'success' => true,
            'message' => $mensagem,
            'novo_status' => $request->status
        ]);
    }

    /**
     * Marcar cotação como enviada (substitui enviarTodas com novo nome)
     * Rota: POST /cotacoes/{cotacao}/marcar-enviada
     */
    public function marcarEnviada(Cotacao $cotacao)
    {
        if (!$cotacao->pode_enviar) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cotação não pode ser marcada como enviada.'
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

        if ($enviadas === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma seguradora pendente para envio.'
            ], 400);
        }

        // Registrar atividade
        $cotacao->atividades()->create([
            'user_id' => auth()->id(),
            'tipo' => 'geral',
            'descricao' => "Cotação marcada como enviada para {$enviadas} seguradora(s)"
        ]);

        return response()->json([
            'success' => true,
            'message' => "Cotação marcada como enviada para {$enviadas} seguradora(s)!",
            'enviadas' => $enviadas
        ]);
    }

    /**
     * Adicionar comentário rápido (para modal no index)
     * Rota: POST /cotacoes/{cotacao}/comentario
     */
    public function adicionarComentario(Request $request, Cotacao $cotacao)
    {
        $request->validate([
            'comentario' => 'required|string|max:500'
        ]);

        // Registrar como atividade
        $cotacao->atividades()->create([
            'user_id' => auth()->id(),
            'tipo' => 'observacao',
            'descricao' => $request->comentario
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentário adicionado com sucesso!'
        ]);
    }

    /**
     * Adicionar atividade completa (para show.blade.php)
     * Rota: POST /cotacoes/{cotacao}/atividade
     */
    public function adicionarAtividade(Request $request, Cotacao $cotacao)
    {
        $request->validate([
            'tipo' => 'required|in:geral,envio,retorno,negociacao,observacao',
            'descricao' => 'required|string|max:1000'
        ]);

        $cotacao->atividades()->create([
            'user_id' => auth()->id(),
            'tipo' => $request->tipo,
            'descricao' => $request->descricao
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Atividade registrada com sucesso!'
        ]);
    }

    /**
     * Duplicar cotação (para show.blade.php)
     * Rota: POST /cotacoes/{cotacao}/duplicar
     */
    public function duplicar(Cotacao $cotacao)
    {
        // ✅ VALIDAÇÃO DUPLA DE SEGURANÇA
        $this->authorize('view', $cotacao); // Pode ver a original
        $this->authorize('create', Cotacao::class); // Pode criar nova

        try {
            // ⚠️ SEGURANÇA CRÍTICA: SEMPRE user_id do usuário logado
            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }
            
            $novaCotacao = Cotacao::create([
                'corretora_id' => $cotacao->corretora_id,
                'produto_id' => $cotacao->produto_id,
                'segurado_id' => $cotacao->segurado_id,
                'observacoes' => "Duplicada da cotação #{$cotacao->id} - " . ($cotacao->observacoes ?? ''),
                'status' => 'em_andamento',
                'user_id' => $userId // ← SEMPRE do usuário logado
            ]);

            // Duplicar seguradoras (resetando status)
            foreach ($cotacao->cotacaoSeguradoras as $cs) {
                $novaCotacao->cotacaoSeguradoras()->create([
                    'seguradora_id' => $cs->seguradora_id,
                    'status' => 'aguardando'
                ]);
            }

            // Registrar atividade na cotação original
            $cotacao->atividades()->create([
                'user_id' => $userId,
                'tipo' => 'geral',
                'descricao' => "Cotação duplicada - Nova cotação: #{$novaCotacao->id}"
            ]);

            // Registrar atividade na nova cotação
            $novaCotacao->atividades()->create([
                'user_id' => $userId,
                'tipo' => 'geral',
                'descricao' => "Cotação criada a partir da duplicação da cotação #{$cotacao->id}"
            ]);

            return response()->json([
                'success' => true,
                'message' => "Cotação duplicada com sucesso! Nova cotação: #{$novaCotacao->id}",
                'nova_cotacao_id' => $novaCotacao->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao duplicar cotação', [
                'cotacao_id' => $cotacao->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar cotação. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Relatório filtrado (para exportar do index)
     * Rota: GET /cotacoes/relatorio
     */
    public function relatorioFiltrado(Request $request)
    {
        // Reutilizar a mesma lógica de filtros do index
        $query = Cotacao::with([
            'corretora', 
            'produto', 
            'segurado', 
            'cotacaoSeguradoras.seguradora'
        ]);

        // Aplicar os mesmos filtros do index
        if ($request->filled('status_geral')) {
            $query->where('status', $request->status_geral);
        }

        if ($request->filled('corretora_id')) {
            $query->where('corretora_id', $request->corretora_id);
        }

        if ($request->filled('produto_id')) {
            $query->where('produto_id', $request->produto_id);
        }

        if ($request->filled('busca')) {
            $query->whereHas('segurado', function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->busca . '%');
            });
        }

        if ($request->filled('status_consolidado')) {
            $statusFiltro = $request->status_consolidado;
            $query->whereHas('cotacaoSeguradoras', function($q) use ($statusFiltro) {
                $q->where('status', $statusFiltro);
            });
        }

        $cotacoes = $query->orderBy('created_at', 'desc')->get();

        // Determinar formato de exportação
        $formato = $request->input('formato', 'excel');

        switch ($formato) {
            case 'excel':
                return $this->exportarExcelFiltrado($cotacoes, $request);
            case 'pdf':
                return $this->exportarPdfFiltrado($cotacoes, $request);
            default:
                return response()->json(['error' => 'Formato não suportado'], 400);
        }
    }

    /**
     * Exportar Excel com filtros aplicados
     */
    private function exportarExcelFiltrado($cotacoes, $request)
    {
        // Para implementação futura com Laravel Excel
        // Por enquanto, retorna os dados em JSON para download
        
        $dados = $cotacoes->map(function($cotacao) {
            return [
                'ID' => $cotacao->id,
                'Segurado' => $cotacao->segurado->nome ?? 'N/A',
                'Corretora' => $cotacao->corretora->nome ?? 'N/A',
                'Produto' => $cotacao->produto->nome ?? 'N/A',
                'Status' => ucfirst($cotacao->status),
                'Seguradoras' => $cotacao->cotacaoSeguradoras->count(),
                'Aprovadas' => $cotacao->cotacaoSeguradoras->where('status', 'aprovada')->count(),
                'Melhor_Valor' => $cotacao->getMelhorProposta()?->valor_premio ?? null,
                'Criada_em' => $cotacao->created_at->format('d/m/Y H:i'),
                'Observacoes' => $cotacao->observacoes
            ];
        });

        $nomeArquivo = 'cotacoes_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($dados)
            ->header('Content-Disposition', "attachment; filename=\"{$nomeArquivo}\"");
    }

    /**
     * Gerar PDF individual (mantém o método existente se houver)
     * Rota: GET /cotacoes/{id}/pdf
     */
    public function gerarPdf($id)
    {
        $cotacao = Cotacao::with([
            'corretora',
            'produto', 
            'segurado',
            'cotacaoSeguradoras.seguradora'
        ])->findOrFail($id);

        // Por enquanto, simular PDF
        return response()->json([
            'message' => 'Geração de PDF em desenvolvimento',
            'cotacao_id' => $cotacao->id,
            'cotacao' => $cotacao
        ]);
    }

    /**
     * Exportar Excel individual
     * Rota: GET /cotacoes/{id}/excel
     */
    public function exportarExcel($id)
    {
        $cotacao = Cotacao::with([
            'corretora',
            'produto', 
            'segurado',
            'cotacaoSeguradoras.seguradora'
        ])->findOrFail($id);

        // Dados da cotação para Excel
        $dados = [
            'cotacao' => [
                'ID' => $cotacao->id,
                'Segurado' => $cotacao->segurado->nome ?? 'N/A',
                'Corretora' => $cotacao->corretora->nome ?? 'N/A',
                'Produto' => $cotacao->produto->nome ?? 'N/A',
                'Status' => ucfirst($cotacao->status),
                'Criada_em' => $cotacao->created_at->format('d/m/Y H:i'),
                'Observacoes' => $cotacao->observacoes
            ],
            'seguradoras' => $cotacao->cotacaoSeguradoras->map(function($cs) {
                return [
                    'Seguradora' => $cs->seguradora->nome,
                    'Status' => ucfirst($cs->status),
                    'Valor_Premio' => $cs->valor_premio,
                    'Valor_IS' => $cs->valor_is,
                    'Data_Envio' => $cs->data_envio?->format('d/m/Y H:i'),
                    'Data_Retorno' => $cs->data_retorno?->format('d/m/Y H:i'),
                    'Observacoes' => $cs->observacoes
                ];
            })
        ];

        $nomeArquivo = "cotacao_{$cotacao->id}_" . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($dados)
            ->header('Content-Disposition', "attachment; filename=\"{$nomeArquivo}\"");
    }
}