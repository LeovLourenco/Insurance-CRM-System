<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    use HasFactory;

    // Corrigir nome da tabela (tem um typo no banco)
    protected $table = 'cotacoes';

    protected $fillable = [
        'corretora_id', 
        'produto_id', 
        'seguradora_id', 
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
     * Seguradora que vai analisar a cotação
     */
    public function seguradora()
    {
        return $this->belongsTo(Seguradora::class);
    }

    /**
     * Cliente segurado
     */
    public function segurado()
    {
        return $this->belongsTo(Segurado::class);
    }

    /**
     * Atividades/histórico da cotação
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
     * Filtrar por seguradora
     */
    public function scopePorSeguradora($query, $seguradoraId)
    {
        return $query->where('seguradora_id', $seguradoraId);
    }

    /**
     * Filtrar por segurado
     */
    public function scopePorSegurado($query, $seguradoId)
    {
        return $query->where('segurado_id', $seguradoId);
    }

    /**
     * Buscar por observações ou ID
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('id', 'like', "%{$search}%")
                    ->orWhere('observacoes', 'like', "%{$search}%");
    }

    /**
     * Cotações com atividades
     */
    public function scopeComAtividades($query)
    {
        return $query->has('atividades');
    }

    /**
     * Cotações pendentes (aguardando ou em análise)
     */
    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['aguardando', 'em_analise']);
    }

    /**
     * Cotações finalizadas (aprovadas ou rejeitadas)
     */
    public function scopeFinalizadas($query)
    {
        return $query->whereIn('status', ['aprovada', 'rejeitada']);
    }

    // Accessors

    /**
     * Accessor para status formatado
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
            default:
                return ucfirst($this->status);
        }
    }

    /**
     * Accessor para classe CSS do status
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
            default:
                return 'secondary';
        }
    }

    // Métodos auxiliares

    /**
     * Verificar se a cotação está pendente
     */
    public function isPendente()
    {
        return in_array($this->status, ['aguardando', 'em_analise']);
    }

    /**
     * Verificar se a cotação foi finalizada
     */
    public function isFinalizada()
    {
        return in_array($this->status, ['aprovada', 'rejeitada']);
    }

    /**
     * Última atividade da cotação
     */
    public function ultimaAtividade()
    {
        return $this->atividades()->latest()->first();
    }

    /**
     * Adicionar nova atividade
     */
    public function adicionarAtividade($descricao, $userId = null)
    {
        return $this->atividades()->create([
            'descricao' => $descricao,
            'user_id' => $userId ?? auth()->id()
        ]);
    }

    /**
     * Alterar status e registrar atividade
     */
    public function alterarStatus($novoStatus, $observacao = null, $userId = null)
    {
        $statusAnterior = $this->status;
        $this->status = $novoStatus;
        $this->save();

        // Registrar atividade
        $descricao = "Status alterado de '{$statusAnterior}' para '{$novoStatus}'";
        if ($observacao) {
            $descricao .= ". Observação: {$observacao}";
        }

        $this->adicionarAtividade($descricao, $userId);

        return $this;
    }

    /**
     * Contar atividades
     */
    public function totalAtividades()
    {
        return $this->atividades()->count();
    }

    // Constantes para status
    const STATUS_AGUARDANDO = 'aguardando';
    const STATUS_EM_ANALISE = 'em_analise';
    const STATUS_APROVADA = 'aprovada';
    const STATUS_REJEITADA = 'rejeitada';

    /**
     * Lista de status disponíveis
     */
    public static function getStatusDisponiveis()
    {
        return [
            self::STATUS_AGUARDANDO => 'Aguardando',
            self::STATUS_EM_ANALISE => 'Em Análise',
            self::STATUS_APROVADA => 'Aprovada',
            self::STATUS_REJEITADA => 'Rejeitada'
        ];
    }
}