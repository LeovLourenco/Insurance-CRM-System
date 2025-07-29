<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Consulta extends Model
{
    // Este model não tem tabela própria - é um helper para consultas complexas
    
    /**
     * Buscar seguradoras por relacionamentos
     * 
     * @param int|null $corretoraId
     * @param int|null $produtoId
     * @return Collection
     */
    public static function buscarSeguradoras($corretoraId = null, $produtoId = null)
    {
        $query = Seguradora::query();

        if ($corretoraId && $produtoId) {
            // Ambos: interseção de corretora E produto
            $query->whereHas('corretoras', function ($q) use ($corretoraId) {
                $q->where('corretoras.id', $corretoraId);
            })->whereHas('produtos', function ($q) use ($produtoId) {
                $q->where('produtos.id', $produtoId);
            });
            
        } elseif ($corretoraId) {
            // Apenas corretora
            $query->whereHas('corretoras', function ($q) use ($corretoraId) {
                $q->where('corretoras.id', $corretoraId);
            });
            
        } elseif ($produtoId) {
            // Apenas produto
            $query->whereHas('produtos', function ($q) use ($produtoId) {
                $q->where('produtos.id', $produtoId);
            });
        }

        return $query->with(['corretoras', 'produtos'])
                    ->orderBy('nome')
                    ->get();
    }

    /**
     * Buscar seguradoras elegíveis para uma cotação
     * Baseado na corretora e produto específicos
     * 
     * @param int $corretoraId
     * @param int $produtoId
     * @param array $excluirIds IDs de seguradoras para excluir
     * @return Collection
     */
    public static function seguradoresElegiveis($corretoraId, $produtoId, $excluirIds = [])
    {
        $query = Seguradora::whereHas('corretoras', function ($q) use ($corretoraId) {
            $q->where('corretora_id', $corretoraId);
        })->whereHas('produtos', function ($q) use ($produtoId) {
            $q->where('produto_id', $produtoId);
        });

        if (!empty($excluirIds)) {
            $query->whereNotIn('id', $excluirIds);
        }

        return $query->orderBy('nome')->get(['id', 'nome']);
    }

    /**
     * Obter estatísticas de relacionamentos
     * 
     * @return array
     */
    public static function estatisticasRelacionamentos()
    {
        return [
            'total_seguradoras' => Seguradora::count(),
            'total_corretoras' => Corretora::count(),
            'total_produtos' => Produto::count(),
            'relacionamentos_corretora_seguradora' => \DB::table('corretora_seguradora')->count(),
            'relacionamentos_seguradora_produto' => \DB::table('seguradora_produto')->count(),
            'seguradoras_sem_corretoras' => Seguradora::doesntHave('corretoras')->count(),
            'seguradoras_sem_produtos' => Seguradora::doesntHave('produtos')->count(),
            'corretoras_sem_seguradoras' => Corretora::doesntHave('seguradoras')->count(),
            'produtos_sem_seguradoras' => Produto::doesntHave('seguradoras')->count(),
        ];
    }

    /**
     * Buscar seguradoras com mais relacionamentos
     * 
     * @param int $limite
     * @return Collection
     */
    public static function seguradoresMaisConectadas($limite = 10)
    {
        return Seguradora::withCount(['corretoras', 'produtos'])
                        ->orderByDesc('corretoras_count')
                        ->orderByDesc('produtos_count')
                        ->limit($limite)
                        ->get();
    }

    /**
     * Buscar corretoras com mais seguradoras parceiras
     * 
     * @param int $limite
     * @return Collection
     */
    public static function corretorasMaisConectadas($limite = 10)
    {
        return Corretora::withCount('seguradoras')
                       ->orderByDesc('seguradoras_count')
                       ->limit($limite)
                       ->get();
    }

    /**
     * Buscar produtos mais oferecidos
     * 
     * @param int $limite
     * @return Collection
     */
    public static function produtosMaisOferecidos($limite = 10)
    {
        return Produto::withCount('seguradoras')
                     ->orderByDesc('seguradoras_count')
                     ->limit($limite)
                     ->get();
    }

    /**
     * Validar se uma combinação corretora-produto-seguradora é válida
     * 
     * @param int $corretoraId
     * @param int $produtoId
     * @param int $seguradoraId
     * @return bool
     */
    public static function validarCombinacao($corretoraId, $produtoId, $seguradoraId)
    {
        // Verificar se a seguradora trabalha com a corretora
        $trabalhaComCorretora = \DB::table('corretora_seguradora')
            ->where('corretora_id', $corretoraId)
            ->where('seguradora_id', $seguradoraId)
            ->exists();

        // Verificar se a seguradora oferece o produto
        $ofereceProduto = \DB::table('seguradora_produto')
            ->where('seguradora_id', $seguradoraId)
            ->where('produto_id', $produtoId)
            ->exists();

        return $trabalhaComCorretora && $ofereceProduto;
    }

    /**
     * Buscar histórico de cotações por relacionamento
     * 
     * @param int|null $corretoraId
     * @param int|null $produtoId
     * @param int|null $seguradoraId
     * @return Collection
     */
    public static function historicoCotacoes($corretoraId = null, $produtoId = null, $seguradoraId = null)
    {
        $query = Cotacao::with(['corretora', 'produto', 'segurado', 'cotacaoSeguradoras.seguradora']);

        if ($corretoraId) {
            $query->where('corretora_id', $corretoraId);
        }

        if ($produtoId) {
            $query->where('produto_id', $produtoId);
        }

        if ($seguradoraId) {
            $query->whereHas('cotacaoSeguradoras', function ($q) use ($seguradoraId) {
                $q->where('seguradora_id', $seguradoraId);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Obter matriz de relacionamentos
     * Útil para visualizações em grade
     * 
     * @return array
     */
    public static function matrizRelacionamentos()
    {
        $corretoras = Corretora::with('seguradoras')->get();
        $seguradoras = Seguradora::all();
        
        $matriz = [];
        
        foreach ($corretoras as $corretora) {
            $matriz[$corretora->nome] = [];
            
            foreach ($seguradoras as $seguradora) {
                $matriz[$corretora->nome][$seguradora->nome] = 
                    $corretora->seguradoras->contains('id', $seguradora->id);
            }
        }
        
        return $matriz;
    }

    /**
     * Sugerir novos relacionamentos baseado em padrões
     * 
     * @param int $seguradoraId
     * @return array
     */
    public static function sugerirRelacionamentos($seguradoraId)
    {
        $seguradora = Seguradora::with(['corretoras', 'produtos'])->findOrFail($seguradoraId);
        
        // Sugerir corretoras baseado nos produtos que a seguradora oferece
        $corretorasSugeridas = Corretora::whereHas('seguradoras.produtos', function ($q) use ($seguradora) {
            $q->whereIn('produto_id', $seguradora->produtos->pluck('id'));
        })->whereDoesntHave('seguradoras', function ($q) use ($seguradoraId) {
            $q->where('seguradora_id', $seguradoraId);
        })->limit(5)->get();

        // Sugerir produtos baseado nas corretoras que trabalham com a seguradora
        $produtosSugeridos = Produto::whereHas('seguradoras.corretoras', function ($q) use ($seguradora) {
            $q->whereIn('corretora_id', $seguradora->corretoras->pluck('id'));
        })->whereDoesntHave('seguradoras', function ($q) use ($seguradoraId) {
            $q->where('seguradora_id', $seguradoraId);
        })->limit(5)->get();

        return [
            'corretoras_sugeridas' => $corretorasSugeridas,
            'produtos_sugeridos' => $produtosSugeridos,
        ];
    }

    /**
     * Relatório de cobertura por região/tipo
     * 
     * @return array
     */
    public static function relatorioCobertura()
    {
        $produtos = Produto::withCount('seguradoras')->get();
        $corretoras = Corretora::withCount('seguradoras')->get();
        
        return [
            'produtos_sem_cobertura' => $produtos->where('seguradoras_count', 0),
            'produtos_baixa_cobertura' => $produtos->where('seguradoras_count', '>', 0)->where('seguradoras_count', '<=', 3),
            'produtos_boa_cobertura' => $produtos->where('seguradoras_count', '>', 3),
            'corretoras_sem_parceiros' => $corretoras->where('seguradoras_count', 0),
            'corretoras_poucos_parceiros' => $corretoras->where('seguradoras_count', '>', 0)->where('seguradoras_count', '<=', 5),
            'corretoras_muitos_parceiros' => $corretoras->where('seguradoras_count', '>', 5),
        ];
    }
}