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
     * Cotações desta seguradora
     */
    public function cotacoes()
    {
        return $this->hasMany(Cotacao::class);
    }

    /**
     * Vínculos desta seguradora
     */
    public function vinculos()
    {
        return $this->hasMany(Vinculo::class);
    }

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
     * Contar cotações por status
     */
    public function cotacoesPorStatus()
    {
        return $this->cotacoes()
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');
    }
}