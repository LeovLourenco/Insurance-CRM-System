<?php

namespace App\Http\Controllers;

use App\Models\CotacaoSeguradora;
use Illuminate\Http\Request;

class CotacaoSeguradoraController extends Controller
{
    /**
     * Exibir dados de uma cotacao_seguradora específica
     * Rota: GET /cotacao-seguradoras/{cotacaoSeguradora}
     */
    public function show(CotacaoSeguradora $cotacaoSeguradora)
    {
        $cotacaoSeguradora->load(['cotacao', 'seguradora']);

        return response()->json([
            'success' => true,
            'cotacao_seguradora' => [
                'id' => $cotacaoSeguradora->id,
                'cotacao_id' => $cotacaoSeguradora->cotacao_id,
                'seguradora_id' => $cotacaoSeguradora->seguradora_id,
                'seguradora_nome' => $cotacaoSeguradora->seguradora->nome,
                'status' => $cotacaoSeguradora->status,
                'valor_premio' => $cotacaoSeguradora->valor_premio,
                'valor_is' => $cotacaoSeguradora->valor_is,
                'data_envio' => $cotacaoSeguradora->data_envio?->format('Y-m-d\TH:i'),
                'data_retorno' => $cotacaoSeguradora->data_retorno?->format('Y-m-d\TH:i'),
                'observacoes' => $cotacaoSeguradora->observacoes
            ]
        ]);
    }

    /**
     * Mostrar formulário de edição
     * Rota: GET /cotacao-seguradoras/{cotacaoSeguradora}/edit
     */
    public function edit(CotacaoSeguradora $cotacaoSeguradora)
    {
        $cotacaoSeguradora->load(['cotacao.corretora', 'cotacao.produto', 'cotacao.segurado', 'seguradora']);

        return view('cotacao-seguradoras.edit', compact('cotacaoSeguradora'));
    }

    /**
     * Atualizar dados da cotacao_seguradora
     * Rota: PUT /cotacao-seguradoras/{cotacaoSeguradora}
     */
    public function update(Request $request, CotacaoSeguradora $cotacaoSeguradora)
    {
        $request->validate([
            'status' => 'required|in:aguardando,em_analise,aprovada,rejeitada,repique',
            'valor_premio' => 'nullable|numeric|min:0',
            'valor_is' => 'nullable|numeric|min:0',
            'data_retorno' => 'nullable|date',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        $statusAnterior = $cotacaoSeguradora->status;
        $dadosAnteriores = $cotacaoSeguradora->only(['valor_premio', 'valor_is', 'observacoes']);

        // Atualizar dados
        $cotacaoSeguradora->update([
            'status' => $request->status,
            'valor_premio' => $request->valor_premio,
            'valor_is' => $request->valor_is,
            'data_retorno' => $request->data_retorno ? now() : $cotacaoSeguradora->data_retorno,
            'observacoes' => $request->observacoes
        ]);

        // Registrar atividade na cotação principal
        $mudancas = [];
        
        if ($statusAnterior !== $request->status) {
            $mudancas[] = "Status: {$statusAnterior} → {$request->status}";
        }
        
        if ($dadosAnteriores['valor_premio'] != $request->valor_premio) {
            $mudancas[] = "Valor Prêmio: " . 
                ($dadosAnteriores['valor_premio'] ? 'R$ ' . number_format($dadosAnteriores['valor_premio'], 2, ',', '.') : 'N/A') . 
                " → " . 
                ($request->valor_premio ? 'R$ ' . number_format($request->valor_premio, 2, ',', '.') : 'N/A');
        }

        $descricao = "Atualização da {$cotacaoSeguradora->seguradora->nome}";
        if (!empty($mudancas)) {
            $descricao .= ': ' . implode(', ', $mudancas);
        }

        $cotacaoSeguradora->cotacao->atividades()->create([
            'cotacao_seguradora_id' => $cotacaoSeguradora->id,
            'user_id' => auth()->id(),
            'tipo' => 'seguradora',
            'descricao' => $descricao
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Seguradora atualizada com sucesso!',
                'cotacao_seguradora' => $cotacaoSeguradora->fresh()
            ]);
        }

        return redirect()
            ->route('cotacoes.show', $cotacaoSeguradora->cotacao_id)
            ->with('success', 'Seguradora atualizada com sucesso!');
    }

        
    /**
 * Atualizar status - VERSÃO SIMPLES
 */
    public function updateStatus(Request $request, CotacaoSeguradora $cotacaoSeguradora)
    {
        $request->validate([
            'status' => 'required|in:aguardando,em_analise,aprovada,rejeitada,repique',
            'observacoes' => 'required|string|max:2000',
            'data_ocorrencia' => 'nullable|date'
        ]);

        $statusAnterior = $cotacaoSeguradora->status;
        
        // Atualizar seguradora
        $cotacaoSeguradora->update([
            'status' => $request->status,
            'data_retorno' => $request->status !== 'aguardando' ? now() : $cotacaoSeguradora->data_retorno
        ]);

        // ✅ Criar atividade simples na timeline
        $cotacaoSeguradora->cotacao->atividades()->create([
            'cotacao_seguradora_id' => $cotacaoSeguradora->id,
            'user_id' => auth()->id(),
            'tipo' => 'status_change',
            'descricao' => $request->observacoes, // ← SUA MENSAGEM PERSONALIZADA
            'data_ocorrencia' => $request->data_ocorrencia ? \Carbon\Carbon::parse($request->data_ocorrencia) : now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso!',
            'novo_status' => $request->status,
            'seguradora' => $cotacaoSeguradora->seguradora->nome
        ]);
    }

    /**
     * Marcar como enviada para uma seguradora específica
     * Rota: POST /cotacao-seguradoras/{cotacaoSeguradora}/marcar-enviada
     */
    public function marcarEnviada(CotacaoSeguradora $cotacaoSeguradora)
    {
        if ($cotacaoSeguradora->status !== 'aguardando') {
            return response()->json([
                'success' => false,
                'message' => 'Esta seguradora não está aguardando envio.'
            ], 400);
        }

        $cotacaoSeguradora->update([
            'data_envio' => now(),
            'status' => 'em_analise'
        ]);

        // Registrar atividade
        $cotacaoSeguradora->cotacao->atividades()->create([
            'cotacao_seguradora_id' => $cotacaoSeguradora->id,
            'user_id' => auth()->id(),
            'tipo' => 'envio',
            'descricao' => "Cotação enviada para {$cotacaoSeguradora->seguradora->nome}"
        ]);

        return response()->json([
            'success' => true,
            'message' => "Cotação marcada como enviada para {$cotacaoSeguradora->seguradora->nome}!"
        ]);
    }

    /**
     * Adicionar observação específica
     * Rota: POST /cotacao-seguradoras/{cotacaoSeguradora}/observacao
     */
    public function adicionarObservacao(Request $request, CotacaoSeguradora $cotacaoSeguradora)
    {
        $request->validate([
            'observacao' => 'required|string|max:500'
        ]);

        // Adicionar à observação existente ou criar nova
        $observacaoAtual = $cotacaoSeguradora->observacoes;
        $novaObservacao = $request->observacao;
        
        if ($observacaoAtual) {
            $observacaoCompleta = $observacaoAtual . "\n\n[" . now()->format('d/m/Y H:i') . "] " . $novaObservacao;
        } else {
            $observacaoCompleta = "[" . now()->format('d/m/Y H:i') . "] " . $novaObservacao;
        }

        $cotacaoSeguradora->update([
            'observacoes' => $observacaoCompleta
        ]);

        // Registrar atividade
        $cotacaoSeguradora->cotacao->atividades()->create([
            'cotacao_seguradora_id' => $cotacaoSeguradora->id,
            'user_id' => auth()->id(),
            'tipo' => 'observacao',
            'descricao' => "Observação adicionada para {$cotacaoSeguradora->seguradora->nome}: {$novaObservacao}"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Observação adicionada com sucesso!',
            'observacao_completa' => $observacaoCompleta
        ]);
    }

    /**
     * Remover uma cotacao_seguradora (se necessário)
     * Rota: DELETE /cotacao-seguradoras/{cotacaoSeguradora}
     */
    public function destroy(CotacaoSeguradora $cotacaoSeguradora)
    {
        // Verificar se a cotação ainda permite edição
        if (!$cotacaoSeguradora->cotacao->pode_editar) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cotação não permite mais alterações.'
            ], 400);
        }

        $seguradoraNome = $cotacaoSeguradora->seguradora->nome;
        $cotacaoId = $cotacaoSeguradora->cotacao_id;

        // Registrar atividade antes de deletar
        $cotacaoSeguradora->cotacao->atividades()->create([
            'user_id' => auth()->id(),
            'tipo' => 'geral',
            'descricao' => "Seguradora {$seguradoraNome} removida da cotação"
        ]);

        $cotacaoSeguradora->delete();

        return response()->json([
            'success' => true,
            'message' => "Seguradora {$seguradoraNome} removida da cotação!"
        ]);
    }
}