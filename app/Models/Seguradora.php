<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguradora extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'site',
        'observacoes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    
    /**
     * Produtos que esta seguradora oferece
     */
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'seguradora_produto')
                    ->withTimestamps();
    }

    /**
     * Corretoras que trabalham com esta seguradora
     */
    public function corretoras()
    {
        return $this->belongsToMany(Corretora::class, 'corretora_seguradora')
                    ->withTimestamps();
    }

    /**
     * Vínculos com corretoras (para auditoria)
     */
    public function vinculos()
    {
        return $this->hasMany(CorretoraSeguradora::class);
    }

    /**
     * Cotações desta seguradora (CORRIGIDO para many-to-many)
     */
    public function cotacoes()
    {
        return $this->belongsToMany(Cotacao::class, 'cotacao_seguradoras')
                    ->withPivot(['status', 'observacoes', 'data_envio', 'data_retorno', 'valor_premio', 'valor_is'])
                    ->withTimestamps();
    }

    /**
     * NOVO: Relacionamento direto com cotacao_seguradoras
     */
    public function cotacaoSeguradoras()
    {
        return $this->hasMany(CotacaoSeguradora::class);
    }

    /**
    

    // Scopes para facilitar consultas

    /**
     * Buscar por nome ou site
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('site', 'like', "%{$search}%")
                    ->orWhere('observacoes', 'like', "%{$search}%");
    }

    /**
     * Seguradoras com produtos
     */
    public function scopeComProdutos($query)
    {
        return $query->has('produtos');
    }

    /**
     * Seguradoras com corretoras
     */
    public function scopeComCorretoras($query)
    {
        return $query->has('corretoras');
    }

    // Accessors e Mutators

    /**
     * Formatar nome em title case
     */
    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = ucwords(strtolower($value));
    }

    /**
     * Formatar site (adicionar https se necessário)
     */
    public function setSiteAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            $value = 'https://' . $value;
        }
        $this->attributes['site'] = $value;
    }

    /**
     * Accessor para site formatado
     */
    public function getSiteFormatadoAttribute()
    {
        if (!$this->site) return null;
        
        return str_replace(['https://', 'http://'], '', $this->site);
    }

    // Métodos auxiliares

    /**
     * Verificar se tem produtos ativos
     */
    public function temProdutos()
    {
        return $this->produtos()->count() > 0;
    }

    /**
     * Verificar se tem corretoras ativas
     */
    public function temCorretoras()
    {
        return $this->corretoras()->count() > 0;
    }

    /**
     * Contar cotações por status (CORRIGIDO)
     */
    public function cotacoesPorStatus($userId = null)
    {
        $query = $this->cotacaoSeguradoras();
        
        if ($userId) {
            $query->whereHas('cotacao', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }
        
        return $query->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');
    }
}