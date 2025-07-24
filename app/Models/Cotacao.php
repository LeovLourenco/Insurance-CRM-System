<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    use HasFactory;

    // Corrigir nome da tabela (já foi corrigido)
    protected $table = 'cotacoes';

    protected $fillable = [
        'corretora_id', 
        'produto_id', 
        'segurado_id', 
        'observacoes', 
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos

    /**
     * Corretora responsável pela cotação
     */
    public function corretora()
    {
        return $this->belongsTo(Corretora::class);
    }

    /**
     * Produto cotado
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Cliente segurado
     */
    public function segurado()
    {
        return $this->belongsTo(Segurado::class);
    }

    /**
     * NOVO: Cotações por seguradora (Master-Detail)
     */
    public function cotacaoSeguradoras()
    {
        return $this->hasMany(CotacaoSeguradora::class);
    }

    /**
     * NOVO: Seguradoras através do relacionamento many-to-many
     */
    public function seguradoras()
    {
        return $this->belongsToMany(Seguradora::class, 'cotacao_seguradoras')
                    ->withPivot(['status', 'observacoes', 'data_envio', 'data_retorno', 'valor_premio', 'valor_is'])
                    ->withTimestamps();
    }

    /**
     * Atividades/histórico da cotação
     */
    public function atividades()
    {
        return $this->hasMany(AtividadeCotacao::class);
    }

    /**
     * NOVO: Atividades gerais (não específicas de seguradora)
     */
    public function atividadesGerais()
    {
        return $this->hasMany(AtividadeCotacao::class)->where('tipo', 'geral');
    }

    /**
     * NOVO: Atividades específicas por seguradora
     */
    public function atividadesPorSeguradora()
    {
        return $this->hasMany(AtividadeCotacao::class)->where('tipo', 'seguradora');
    }

    // Scopes para facilitar consultas

    /**
     * Filtrar por status geral da cotação
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Filtrar por corretora
     */
    public function scopePorCorretora($query, $corretoraId)
    {
        return $query->where('corretora_id', $corretoraId);
    }

    /**
     * Filtrar por produto
     */
    public function scopePorProduto($query, $produtoId)
    {
        return $query->where('produto_id', $produtoId);
    }

    /**
     * Filtrar por segurado
     */
    public function scopePorSegurado($query, $seguradoId)
    {
        return $query->where('segurado_id', $seguradoId);
    }

    /**
     * NOVO: Cotações que têm pelo menos uma seguradora com status específico
     */
    public function scopeComSeguradoraStatus($query, $status)
    {
        return $query->whereHas('cotacaoSeguradoras', function($q) use ($status) {
            $q->where('status', $status);
        });
    }

    /**
     * NOVO: Cotações de uma seguradora específica
     */
    public function scopeComSeguradora($query, $seguradoraId)
    {
        return $query->whereHas('cotacaoSeguradoras', function($q) use ($seguradoraId) {
            $q->where('seguradora_id', $seguradoraId);
        });
    }

    /**
     * Buscar por observações ou ID
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('id', 'like', "%{$search}%")
                    ->orWhere('observacoes', 'like', "%{$search}%")
                    ->orWhereHas('segurado', function($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%");
                    });
    }

    /**
     * Cotações com atividades
     */
    public function scopeComAtividades($query)
    {
        return $query->has('atividades');
    }

    /**
     * ATUALIZADO: Cotações pendentes (que têm pelo menos uma seguradora pendente)
     */
    public function scopePendentes($query)
    {
        return $query->whereHas('cotacaoSeguradoras', function($q) {
            $q->whereIn('status', ['aguardando', 'em_analise']);
        });
    }

    /**
     * NOVO: Cotações totalmente finalizadas (todas as seguradoras finalizadas)
     */
    public function scopeTotalmenteFinalizadas($query)
    {
        return $query->whereDoesntHave('cotacaoSeguradoras', function($q) {
            $q->whereIn('status', ['aguardando', 'em_analise']);
        });
    }

    // Accessors

    /**
     * ATUALIZADO: Status consolidado baseado nas seguradoras
     */
    public function getStatusConsolidadoAttribute()
    {
        $seguradoras = $this->cotacaoSeguradoras;
        
        if ($seguradoras->isEmpty()) {
            return 'sem_seguradoras';
        }

        $aprovadas = $seguradoras->where('status', 'aprovada')->count();
        $pendentes = $seguradoras->whereIn('status', ['aguardando', 'em_analise'])->count();
        $total = $seguradoras->count();

        if ($aprovadas > 0 && $pendentes == 0) {
            return 'finalizada_com_aprovacao';
        }

        if ($pendentes == 0) {
            return 'finalizada_sem_aprovacao';
        }

        if ($aprovadas > 0) {
            return 'parcialmente_aprovada';
        }

        return 'em_andamento';
    }

    /**
     * NOVO: Porcentagem de aprovação
     */
    public function getPorcentagemAprovacaoAttribute()
    {
        $total = $this->cotacaoSeguradoras->count();
        
        if ($total == 0) return 0;

        $aprovadas = $this->cotacaoSeguradoras->where('status', 'aprovada')->count();
        
        return round(($aprovadas / $total) * 100, 1);
    }

    /**
     * NOVO: Melhor proposta (menor valor)
     */
    public function getMelhorPropostaAttribute()
    {
        return $this->cotacaoSeguradoras()
                    ->where('status', 'aprovada')
                    ->whereNotNull('valor_premio')
                    ->orderBy('valor_premio', 'asc')
                    ->first();
    }

    /**
     * NOVO: Total de seguradoras cotadas
     */
    public function getTotalSeguradoras()
    {
        return $this->cotacaoSeguradoras->count();
    }

    /**
     * NOVO: Status formatado para exibição
     */
    public function getStatusFormatadoAttribute()
    {
        switch($this->status_consolidado) {
            case 'finalizada_com_aprovacao':
                return 'Finalizada - Aprovada';
            case 'finalizada_sem_aprovacao':
                return 'Finalizada - Sem Aprovação';
            case 'parcialmente_aprovada':
                return 'Parcialmente Aprovada';
            case 'em_andamento':
                return 'Em Andamento';
            case 'sem_seguradoras':
                return 'Sem Seguradoras';
            default:
                return ucfirst($this->status);
        }
    }

    /**
     * NOVO: Classe CSS para status
     */
    public function getStatusClasseAttribute()
    {
        switch($this->status_consolidado) {
            case 'finalizada_com_aprovacao':
                return 'success';
            case 'finalizada_sem_aprovacao':
                return 'danger';
            case 'parcialmente_aprovada':
                return 'warning';
            case 'em_andamento':
                return 'info';
            case 'sem_seguradoras':
                return 'secondary';
            default:
                return 'secondary';
        }
    }

    // Métodos auxiliares

    /**
     * ATUALIZADO: Verificar se a cotação está pendente
     */
    public function isPendente()
    {
        return $this->cotacaoSeguradoras()->whereIn('status', ['aguardando', 'em_analise'])->exists();
    }

    /**
     * NOVO: Verificar se tem aprovações
     */
    public function temAprovacao()
    {
        return $this->cotacaoSeguradoras()->where('status', 'aprovada')->exists();
    }

    /**
     * NOVO: Verificar se está totalmente finalizada
     */
    public function isTotalmenteFinalizada()
    {
        return !$this->cotacaoSeguradoras()->whereIn('status', ['aguardando', 'em_analise'])->exists();
    }

    /**
     * Última atividade da cotação
     */
    public function ultimaAtividade()
    {
        return $this->atividades()->latest()->first();
    }

    /**
     * NOVO: Adicionar nova atividade geral
     */
    public function adicionarAtividade($descricao, $userId = null)
    {
        return $this->atividades()->create([
            'descricao' => $descricao,
            'user_id' => $userId ?? auth()->id(),
            'tipo' => 'geral'
        ]);
    }

    /**
     * NOVO: Criar cotações para seguradoras selecionadas
     */
    public function criarCotacoesSeguradoras(array $seguradoraIds, $userId = null)
    {
        foreach ($seguradoraIds as $seguradoraId) {
            $cotacaoSeguradora = $this->cotacaoSeguradoras()->create([
                'seguradora_id' => $seguradoraId,
                'status' => CotacaoSeguradora::STATUS_AGUARDANDO
            ]);

            $cotacaoSeguradora->adicionarAtividade(
                "Cotação criada para {$cotacaoSeguradora->seguradora->nome}",
                $userId
            );
        }

        $this->adicionarAtividade(
            "Cotação distribuída para " . count($seguradoraIds) . " seguradoras",
            $userId
        );

        return $this;
    }

    /**
     * NOVO: Enviar para todas as seguradoras pendentes
     */
    public function enviarParaTodasSeguradoras($userId = null)
    {
        $pendentes = $this->cotacaoSeguradoras()->where('status', 'aguardando')->get();

        foreach ($pendentes as $cotacaoSeguradora) {
            $cotacaoSeguradora->marcarComoEnviada($userId);
        }

        return $this;
    }

    /**
     * Contar atividades
     */
    public function totalAtividades()
    {
        return $this->atividades()->count();
    }

    // Constantes para status (mantidas para compatibilidade)
    const STATUS_AGUARDANDO = 'aguardando';
    const STATUS_EM_ANALISE = 'em_analise';
    const STATUS_APROVADA = 'aprovada';
    const STATUS_REJEITADA = 'rejeitada';

    /**
     * ATUALIZADO: Lista de status consolidados
     */
    public static function getStatusConsolidados()
    {
        return [
            'em_andamento' => 'Em Andamento',
            'parcialmente_aprovada' => 'Parcialmente Aprovada',
            'finalizada_com_aprovacao' => 'Finalizada com Aprovação',
            'finalizada_sem_aprovacao' => 'Finalizada sem Aprovação',
            'sem_seguradoras' => 'Sem Seguradoras'
        ];
    }
}