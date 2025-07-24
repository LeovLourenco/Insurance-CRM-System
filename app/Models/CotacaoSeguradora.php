<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotacaoSeguradora extends Model
{
    use HasFactory;

    protected $fillable = [
        'cotacao_id',
        'seguradora_id', 
        'status',
        'observacoes',
        'data_envio',
        'data_retorno',
        'valor_premio',
        'valor_is'
    ];

    protected $casts = [
        'data_envio' => 'datetime',
        'data_retorno' => 'datetime',
        'valor_premio' => 'decimal:2',
        'valor_is' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos

    /**
     * Cotação pai (master)
     */
    public function cotacao()
    {
        return $this->belongsTo(Cotacao::class);
    }

    /**
     * Seguradora específica desta tupla
     */
    public function seguradora()
    {
        return $this->belongsTo(Seguradora::class);
    }

    /**
     * Atividades específicas desta cotação-seguradora
     */
    public function atividades()
    {
        return $this->hasMany(AtividadeCotacao::class);
    }

    // Scopes para facilitar consultas

    /**
     * Filtrar por status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Filtrar por seguradora
     */
    public function scopePorSeguradora($query, $seguradoraId)
    {
        return $query->where('seguradora_id', $seguradoraId);
    }

    /**
     * Filtrar por cotação
     */
    public function scopePorCotacao($query, $cotacaoId)
    {
        return $query->where('cotacao_id', $cotacaoId);
    }

    /**
     * Cotações pendentes
     */
    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['aguardando', 'em_analise']);
    }

    /**
     * Cotações aprovadas
     */
    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovada');
    }

    /**
     * Cotações rejeitadas
     */
    public function scopeRejeitadas($query)
    {
        return $query->where('status', 'rejeitada');
    }

    /**
     * Com retorno (data_retorno não nula)
     */
    public function scopeComRetorno($query)
    {
        return $query->whereNotNull('data_retorno');
    }

    /**
     * Sem retorno (aguardando resposta)
     */
    public function scopeSemRetorno($query)
    {
        return $query->whereNull('data_retorno');
    }

    /**
     * Enviadas (data_envio não nula)
     */
    public function scopeEnviadas($query)
    {
        return $query->whereNotNull('data_envio');
    }

    // Accessors

    /**
     * Status formatado para exibição
     */
    public function getStatusFormatadoAttribute()
    {
        switch($this->status) {
            case 'aguardando':
                return 'Aguardando';
            case 'em_analise':
                return 'Em Análise';
            case 'aprovada':
                return 'Aprovada';
            case 'rejeitada':
                return 'Rejeitada';
            case 'repique':
                return 'Repique';
            case 'cancelada':
                return 'Cancelada';
            default:
                return ucfirst($this->status);
        }
    }

    /**
     * Classe CSS para badge do status
     */
    public function getStatusClasseAttribute()
    {
        switch($this->status) {
            case 'aguardando':
                return 'warning';
            case 'em_analise':
                return 'info';
            case 'aprovada':
                return 'success';
            case 'rejeitada':
                return 'danger';
            case 'repique':
                return 'primary';
            case 'cancelada':
                return 'secondary';
            default:
                return 'secondary';
        }
    }

    /**
     * Tempo de resposta em dias
     */
    public function getTempoRespostaAttribute()
    {
        if (!$this->data_envio || !$this->data_retorno) {
            return null;
        }

        return $this->data_envio->diffInDays($this->data_retorno);
    }

    /**
     * Status de urgência baseado no tempo
     */
    public function getUrgenciaAttribute()
    {
        if (!$this->data_envio || $this->data_retorno) {
            return null;
        }

        $diasEspera = $this->data_envio->diffInDays(now());
        
        if ($diasEspera > 7) {
            return 'critica';
        } elseif ($diasEspera > 3) {
            return 'alta';
        } elseif ($diasEspera > 1) {
            return 'media';
        } else {
            return 'baixa';
        }
    }

    // Métodos auxiliares

    /**
     * Verificar se está pendente
     */
    public function isPendente()
    {
        return in_array($this->status, ['aguardando', 'em_analise']);
    }

    /**
     * Verificar se foi finalizada
     */
    public function isFinalizada()
    {
        return in_array($this->status, ['aprovada', 'rejeitada', 'cancelada']);
    }

    /**
     * Marcar como enviada
     */
    public function marcarComoEnviada($userId = null)
    {
        $this->update([
            'data_envio' => now(),
            'status' => 'aguardando'
        ]);

        $this->adicionarAtividade(
            "Cotação enviada para {$this->seguradora->nome}",
            $userId
        );

        return $this;
    }

    /**
     * Registrar retorno
     */
    public function registrarRetorno($novoStatus, $observacoes = null, $userId = null)
    {
        $this->update([
            'status' => $novoStatus,
            'data_retorno' => now(),
            'observacoes' => $observacoes
        ]);

        $this->adicionarAtividade(
            "Retorno da {$this->seguradora->nome}: {$this->status_formatado}" . 
            ($observacoes ? " - {$observacoes}" : ""),
            $userId
        );

        return $this;
    }

    /**
     * Adicionar atividade específica
     */
    public function adicionarAtividade($descricao, $userId = null)
    {
        return AtividadeCotacao::create([
            'cotacao_id' => $this->cotacao_id,
            'cotacao_seguradora_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'tipo' => 'seguradora',
            'descricao' => $descricao
        ]);
    }

    // Constantes
    const STATUS_AGUARDANDO = 'aguardando';
    const STATUS_EM_ANALISE = 'em_analise';  
    const STATUS_APROVADA = 'aprovada';
    const STATUS_REJEITADA = 'rejeitada';
    const STATUS_REPIQUE = 'repique';
    const STATUS_CANCELADA = 'cancelada';

    /**
     * Lista de status disponíveis
     */
    public static function getStatusDisponiveis()
    {
        return [
            self::STATUS_AGUARDANDO => 'Aguardando',
            self::STATUS_EM_ANALISE => 'Em Análise', 
            self::STATUS_APROVADA => 'Aprovada',
            self::STATUS_REJEITADA => 'Rejeitada',
            self::STATUS_REPIQUE => 'Repique',
            self::STATUS_CANCELADA => 'Cancelada'
        ];
    }
}