<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'linha',
        'descricao'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    
    /**
     * Seguradoras que oferecem este produto
     */
    public function seguradoras()
    {
        return $this->belongsToMany(Seguradora::class, 'seguradora_produto')
            ->withTimestamps(); 
    }


    /**
     * Cotações deste produto
     */
    public function cotacoes()
    {
        return $this->hasMany(Cotacao::class);
    }

    // Scopes para facilitar consultas

    /**
     * Filtrar por linha
     */
    public function scopeOfLinha($query, $linha)
    {
        return $query->where('linha', $linha);
    }

    /**
     * Buscar por nome
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%");
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
     * Formatar linha em title case
     */
    public function setLinhaAttribute($value)
    {
        $this->attributes['linha'] = $value ? ucwords(strtolower($value)) : null;
    }

    // Métodos de análise

    /**
     * Contar cotações por status
     */
    public function cotacoesPorStatus($userId = null)
    {
        $query = $this->cotacoes();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');
    }
}