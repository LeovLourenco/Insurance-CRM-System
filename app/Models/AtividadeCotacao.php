<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtividadeCotacao extends Model
{
    use HasFactory;

    protected $table = 'atividades_cotacao';

    protected $fillable = [
        'cotacao_id',
        'cotacao_seguradora_id', // NOVO
        'user_id',
        'tipo', // NOVO: 'geral' ou 'seguradora'
        'descricao'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos

    /**
     * Cotação principal (sempre presente)
     */
    public function cotacao()
    {
        return $this->belongsTo(Cotacao::class);
    }

    /**
     * NOVO: Cotação-Seguradora específica (opcional)
     */
    public function cotacaoSeguradora()
    {
        return $this->belongsTo(CotacaoSeguradora::class);
    }

    /**
     * Usuário que executou a ação
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * NOVO: Seguradora relacionada (através da cotacaoSeguradora)
     */
    public function seguradora()
    {
        return $this->hasOneThrough(
            Seguradora::class,
            CotacaoSeguradora::class,
            'id', // Foreign key on CotacaoSeguradora table
            'id', // Foreign key on Seguradora table
            'cotacao_seguradora_id', // Local key on AtividadeCotacao table
            'seguradora_id' // Local key on CotacaoSeguradora table
        );
    }

    // Scopes para facilitar consultas

    /**
     * NOVO: Filtrar por tipo de atividade
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * NOVO: Atividades gerais (não específicas de seguradora)
     */
    public function scopeGerais($query)
    {
        return $query->where('tipo', 'geral');
    }

    /**
     * NOVO: Atividades específicas de seguradora
     */
    public function scopePorSeguradora($query)
    {
        return $query->where('tipo', 'seguradora');
    }

    /**
     * NOVO: Atividades de uma seguradora específica
     */
    public function scopeDeSeguradora($query, $seguradoraId)
    {
        return $query->whereHas('cotacaoSeguradora', function($q) use ($seguradoraId) {
            $q->where('seguradora_id', $seguradoraId);
        });
    }

    /**
     * Filtrar por cotação
     */
    public function scopePorCotacao($query, $cotacaoId)
    {
        return $query->where('cotacao_id', $cotacaoId);
    }

    /**
     * Filtrar por usuário
     */
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Atividades recentes (últimas 24h)
     */
    public function scopeRecentes($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    /**
     * NOVO: Atividades de hoje
     */
    public function scopeHoje($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * NOVO: Atividades desta semana
     */
    public function scopeDestaSemana($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * NOVO: Buscar por texto na descrição
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('descricao', 'like', "%{$search}%");
    }

    // Accessors

    /**
     * NOVO: Ícone baseado no tipo de atividade
     */
    public function getIconeAttribute()
    {
        if ($this->tipo === 'geral') {
            return $this->getIconeGeral();
        }

        return $this->getIconeSeguradora();
    }

    /**
     * NOVO: Ícone para atividades gerais
     */
    private function getIconeGeral()
    {
        $descricao = strtolower($this->descricao);

        if (str_contains($descricao, 'criada')) {
            return 'bi-plus-circle text-success';
        }

        if (str_contains($descricao, 'atualizada')) {
            return 'bi-pencil text-info';
        }

        if (str_contains($descricao, 'distribuída')) {
            return 'bi-share text-primary';
        }

        return 'bi-info-circle text-secondary';
    }

    /**
     * NOVO: Ícone para atividades de seguradora
     */
    private function getIconeSeguradora()
    {
        $descricao = strtolower($this->descricao);

        if (str_contains($descricao, 'enviado')) {
            return 'bi-send text-primary';
        }

        if (str_contains($descricao, 'aprovado')) {
            return 'bi-check-circle text-success';
        }

        if (str_contains($descricao, 'rejeitado')) {
            return 'bi-x-circle text-danger';
        }

        if (str_contains($descricao, 'retorno')) {
            return 'bi-reply text-info';
        }

        if (str_contains($descricao, 'repique')) {
            return 'bi-arrow-repeat text-warning';
        }

        return 'bi-building text-secondary';
    }

    /**
     * NOVO: Cor da atividade baseada no tipo e conteúdo
     */
    public function getCorAttribute()
    {
        if ($this->tipo === 'geral') {
            return 'primary';
        }

        $descricao = strtolower($this->descricao);

        if (str_contains($descricao, 'aprovado')) {
            return 'success';
        }

        if (str_contains($descricao, 'rejeitado')) {
            return 'danger';
        }

        if (str_contains($descricao, 'repique')) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * NOVO: Nome da seguradora (se aplicável)
     */
    public function getNomeSeguradoraAttribute()
    {
        if ($this->cotacao_seguradora_id && $this->cotacaoSeguradora) {
            return $this->cotacaoSeguradora->seguradora->nome;
        }

        return null;
    }

    /**
     * NOVO: Formatação amigável da data
     */
    public function getDataFormatadaAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * NOVO: Tempo relativo (ex: "há 2 horas")
     */
    public function getTempoRelativoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Métodos auxiliares

    /**
     * NOVO: Verificar se é atividade geral
     */
    public function isGeral()
    {
        return $this->tipo === 'geral';
    }

    /**
     * NOVO: Verificar se é atividade de seguradora
     */
    public function isSeguradora()
    {
        return $this->tipo === 'seguradora';
    }

    /**
     * NOVO: Verificar se é atividade de aprovação
     */
    public function isAprovacao()
    {
        return str_contains(strtolower($this->descricao), 'aprovado');
    }

    /**
     * NOVO: Verificar se é atividade de rejeição
     */
    public function isRejeicao()
    {
        return str_contains(strtolower($this->descricao), 'rejeitado');
    }

    /**
     * NOVO: Criar atividade geral
     */
    public static function criarGeral($cotacaoId, $descricao, $userId = null)
    {
        return self::create([
            'cotacao_id' => $cotacaoId,
            'cotacao_seguradora_id' => null,
            'user_id' => $userId ?? auth()->id(),
            'tipo' => 'geral',
            'descricao' => $descricao
        ]);
    }

    /**
     * NOVO: Criar atividade específica de seguradora
     */
    public static function criarSeguradora($cotacaoId, $cotacaoSeguradoraId, $descricao, $userId = null)
    {
        return self::create([
            'cotacao_id' => $cotacaoId,
            'cotacao_seguradora_id' => $cotacaoSeguradoraId,
            'user_id' => $userId ?? auth()->id(),
            'tipo' => 'seguradora',
            'descricao' => $descricao
        ]);
    }

    /**
     * NOVO: Timeline completa de uma cotação (geral + seguradoras)
     */
    public static function timelineCotacao($cotacaoId)
    {
        return self::where('cotacao_id', $cotacaoId)
                   ->with(['user', 'cotacaoSeguradora.seguradora'])
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * NOVO: Atividades de performance (aprovações, rejeições)
     */
    public static function atividadesPerformance($periodo = 30)
    {
        return self::where('created_at', '>=', now()->subDays($periodo))
                   ->porSeguradora()
                   ->where(function($query) {
                       $query->where('descricao', 'like', '%aprovado%')
                             ->orWhere('descricao', 'like', '%rejeitado%');
                   })
                   ->with(['cotacaoSeguradora.seguradora'])
                   ->get();
    }

    // Constantes
    const TIPO_GERAL = 'geral';
    const TIPO_SEGURADORA = 'seguradora';

    /**
     * Lista de tipos disponíveis
     */
    public static function getTiposDisponiveis()
    {
        return [
            self::TIPO_GERAL => 'Atividade Geral',
            self::TIPO_SEGURADORA => 'Atividade de Seguradora'
        ];
    }
}