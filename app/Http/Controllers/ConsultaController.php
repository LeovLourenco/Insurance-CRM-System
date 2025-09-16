<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Corretora;
use App\Models\Produto;
use App\Models\Seguradora;

class ConsultaController extends Controller
{
    public function index()
    {
        // ✅ ENTIDADES BASE: Todos veem todas (arquitetura correta)
        $corretoras = Corretora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        return view('consultas.seguros', compact('corretoras', 'produtos'));
    }

    public function buscar(Request $request)
    {
        $corretoraId = $request->input('corretora_id');
        $produtoId = $request->input('produto_id');

        // Validar que pelo menos um filtro foi selecionado
        if (!$corretoraId && !$produtoId) {
            return redirect()->back()->with('error', 'Selecione pelo menos uma corretora ou produto para buscar.');
        }

        // Buscar modelos selecionados para exibir na view
        $corretora = $corretoraId ? Corretora::find($corretoraId) : null;
        $produto = $produtoId ? Produto::find($produtoId) : null;

        // Query base das seguradoras
        $query = Seguradora::query();

        // Aplicar filtros conforme seleção
        if ($corretoraId && $produtoId) {
            // AMBOS selecionados: seguradoras que trabalham com a corretora E o produto
            $query->whereHas('corretoras', function ($q) use ($corretoraId) {
                $q->where('corretoras.id', $corretoraId);
            })->whereHas('produtos', function ($q) use ($produtoId) {
                $q->where('produtos.id', $produtoId);
            });
            
            $tipoConsulta = 'ambos';
            
        } elseif ($corretoraId) {
            // APENAS corretora: todas as seguradoras que trabalham com esta corretora
            $query->whereHas('corretoras', function ($q) use ($corretoraId) {
                $q->where('corretoras.id', $corretoraId);
            });
            
            $tipoConsulta = 'corretora';
            
        } elseif ($produtoId) {
            // APENAS produto: todas as seguradoras que oferecem este produto
            $query->whereHas('produtos', function ($q) use ($produtoId) {
                $q->where('produtos.id', $produtoId);
            });
            
            $tipoConsulta = 'produto';
        }

        // Executar query com relacionamentos para mostrar detalhes
        $seguradoras = $query->with(['corretoras', 'produtos'])
                            ->orderBy('nome')
                            ->get();

        // Preparar dados adicionais para a view
        $totalSeguradoras = $seguradoras->count();
        
        // Contar relacionamentos por tipo
        $estatisticas = [
            'total_seguradoras' => $totalSeguradoras,
            'tipo_consulta' => $tipoConsulta,
            'corretoras_envolvidas' => $corretoraId ? 1 : $seguradoras->pluck('corretoras')->flatten()->unique('id')->count(),
            'produtos_envolvidos' => $produtoId ? 1 : $seguradoras->pluck('produtos')->flatten()->unique('id')->count(),
        ];

        // Recarregar listas para os selects
        // ✅ ENTIDADES BASE: Todos veem todas (arquitetura correta)
        $corretoras = Corretora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();

        return view('consultas.seguros', compact(
            'corretoras', 
            'produtos', 
            'seguradoras', 
            'corretora', 
            'produto',
            'estatisticas',
            'corretoraId',
            'produtoId'
        ));
    }
}