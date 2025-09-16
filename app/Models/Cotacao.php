<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    protected $table = 'cotacoes';
    
    protected $fillable = [
        'corretora_id', 'produto_id', 'segurado_id', 
        'observacoes', 'status', 'user_id'
        // ✅ user_id adicionado ao fillable para permitir mass assignment seguro
    ];
    
    // ⚠️ CAMPOS PROTEGIDOS: Nunca podem ser atribuídos via mass assignment
    protected $guarded = [
        'id', 'created_at', 'updated_at'
        // ✅ user_id removido do guarded para permitir atribuição controlada
    ];

    // Relacionamentos existentes
    public function corretora()
    {
        return $this->belongsTo(Corretora::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function segurado()
    {
        return $this->belongsTo(Segurado::class);
    }

    public function cotacaoSeguradoras()
    {
        return $this->hasMany(CotacaoSeguradora::class);
    }

    public function atividades()
    {
        return $this->hasMany(AtividadeCotacao::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ===== NOVOS ACCESSORS PARA STATUS INTELIGENTE =====

    /**
     * Status consolidado baseado nas seguradoras
     */
    public function getStatusConsolidadoAttribute()
    {
        $seguradoras = $this->cotacaoSeguradoras;
        
        if ($seguradoras->isEmpty()) {
            return 'sem_seguradoras';
        }

        $statusSeguradoras = $seguradoras->pluck('status');
        
        // Se tem alguma aprovada = aprovada
        if ($statusSeguradoras->contains('aprovada')) {
            return 'aprovada';
        }
        
        // Se tem alguma em análise = em_analise
        if ($statusSeguradoras->contains('em_analise')) {
            return 'em_analise';
        }
        
        // Se tem alguma aguardando = aguardando
        if ($statusSeguradoras->contains('aguardando')) {
            return 'aguardando';
        }
        
        // Se todas rejeitadas = rejeitada
        if ($statusSeguradoras->every(fn($status) => $status === 'rejeitada')) {
            return 'rejeitada';
        }
        
        return 'aguardando'; // fallback
    }

    /**
     * Status final para exibição (HIERARQUIA CLARA)
     * Status manual (cancelada/finalizada) tem prioridade
     * Status em_andamento usa o consolidado das seguradoras
     */
    public function getStatusExibicaoAttribute()
    {
        // Status manual tem prioridade absoluta
        if (in_array($this->status, ['cancelada', 'finalizada'])) {
            return $this->status;
        }
        
        // Se em andamento, mostrar status consolidado das seguradoras
        return $this->status_consolidado;
    }

    /**
     * Formatação do status da tabela (manual)
     */
    public function getStatusTabelaFormatadoAttribute()
    {
        $statusMap = [
            'em_andamento' => 'Em Andamento',
            'finalizada' => 'Finalizada',
            'cancelada' => 'Cancelada'
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Formatação do status consolidado
     */
    public function getStatusConsolidadoFormatadoAttribute()
    {
        $statusMap = [
            'aguardando' => 'Aguardando Resposta',
            'em_analise' => 'Em Análise',
            'aprovada' => 'Aprovada',
            'rejeitada' => 'Rejeitada',
            'repique' => 'Repique Solicitado',
            'sem_seguradoras' => 'Sem Seguradoras'
        ];

        return $statusMap[$this->status_consolidado] ?? ucfirst($this->status_consolidado);
    }

    /**
     * Formatação do status para exibição final
     */
    public function getStatusExibicaoFormatadoAttribute()
    {
        // Se é status manual
        if (in_array($this->status, ['cancelada', 'finalizada'])) {
            return $this->status_tabela_formatado;
        }
        
        // Se é consolidado
        return $this->status_consolidado_formatado;
    }

    /**
     * Classe CSS para o status da tabela
     */
    public function getStatusTabelaClasseAttribute()
    {
        $classMap = [
            'em_andamento' => 'primary',
            'finalizada' => 'success',
            'cancelada' => 'danger'
        ];

        return $classMap[$this->status] ?? 'secondary';
    }

    /**
     * Classe CSS para o status consolidado
     */
    public function getStatusConsolidadoClasseAttribute()
    {
        $classMap = [
            'aguardando' => 'warning',
            'em_analise' => 'info',
            'aprovada' => 'success',
            'rejeitada' => 'danger',
            'repique' => 'warning',
            'sem_seguradoras' => 'secondary'
        ];

        return $classMap[$this->status_consolidado] ?? 'secondary';
    }

    /**
     * Classe CSS para exibição final
     */
    public function getStatusExibicaoClasseAttribute()
    {
        // Se é status manual
        if (in_array($this->status, ['cancelada', 'finalizada'])) {
            return $this->status_tabela_classe;
        }
        
        // Se é consolidado
        return $this->status_consolidado_classe;
    }

    /**
     * Verifica se a cotação pode ser editada
     */
    public function getPodeEditarAttribute()
    {
        return !in_array($this->status, ['finalizada', 'cancelada']);
    }

    /**
     * Verifica se pode enviar para seguradoras
     */
    public function getPodeEnviarAttribute()
    {
        return $this->status === 'em_andamento' && $this->cotacaoSeguradoras->isNotEmpty();
    }

    /**
     * Contadores para dashboard (nome melhorado)
     */
    public function getSeguradoraStats()
    {
        return [
            'total' => $this->cotacaoSeguradoras->count(),
            'aguardando' => $this->cotacaoSeguradoras->where('status', 'aguardando')->count(),
            'em_analise' => $this->cotacaoSeguradoras->where('status', 'em_analise')->count(),
            'aprovadas' => $this->cotacaoSeguradoras->where('status', 'aprovada')->count(),
            'rejeitadas' => $this->cotacaoSeguradoras->where('status', 'rejeitada')->count(),
        ];
    }

    /**
     * Buscar melhor proposta (move lógica da view para model)
     */
    public function getMelhorProposta()
    {
        return $this->cotacaoSeguradoras()
            ->where('status', 'aprovada')
            ->whereNotNull('valor_premio')
            ->orderBy('valor_premio', 'asc')
            ->first();
    }

    /**
     * Calcular percentual de respostas das seguradoras
     */
    public function getPercentualRespostaAttribute()
    {
        $respondidas = $this->cotacaoSeguradoras->whereNotIn('status', ['aguardando'])->count();
        $total = $this->cotacaoSeguradoras->count();
        return $total > 0 ? round(($respondidas / $total) * 100) : 0;
    }

    /**
     * Quantidade de seguradoras que já responderam
     */
    public function getQuantidadeRespondidaAttribute()
    {
        return $this->cotacaoSeguradoras->whereNotIn('status', ['aguardando'])->count();
    }

    /**
     * @deprecated Use getSeguradoraStats() instead
     */
    public function getSeguradoras()
    {
        return $this->getSeguradoraStats();
    }
}