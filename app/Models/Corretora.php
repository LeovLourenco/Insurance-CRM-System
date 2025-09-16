<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corretora extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'usuario_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    
    /**
     * Seguradoras que esta corretora trabalha
     */
    public function seguradoras()
    {
        return $this->belongsToMany(Seguradora::class, 'corretora_seguradora')
                    ->withTimestamps();
    }

    /**
     * Cotações desta corretora
     */
    public function cotacoes()
    {
        return $this->hasMany(Cotacao::class);
    }

    /**
     * Usuario responsável por esta corretora
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes para facilitar consultas

    /**
     * Buscar por nome, email ou telefone
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefone', 'like', "%{$search}%");
    }

    /**
     * Corretoras com seguradoras
     */
    public function scopeComSeguradoras($query)
    {
        return $query->has('seguradoras');
    }

    /**
     * Corretoras com cotações
     */
    public function scopeComCotacoes($query)
    {
        return $query->has('cotacoes');
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
     * Formatar email em lowercase
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ? strtolower($value) : null;
    }

    /**
     * Formatar telefone (remover caracteres especiais)
     */
    public function setTelefoneAttribute($value)
    {
        if ($value) {
            // Remove tudo que não é número
            $cleaned = preg_replace('/[^0-9]/', '', $value);
            $this->attributes['telefone'] = $cleaned;
        } else {
            $this->attributes['telefone'] = null;
        }
    }

    /**
     * Accessor para telefone formatado
     */
    public function getTelefoneFormatadoAttribute()
    {
        if (!$this->telefone) return null;
        
        $telefone = $this->telefone;
        $length = strlen($telefone);
        
        // Celular com DDD (11 dígitos)
        if ($length == 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
        }
        // Telefone fixo com DDD (10 dígitos)
        elseif ($length == 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6, 4);
        }
        
        return $telefone;
    }

    // Métodos auxiliares

    /**
     * Verificar se tem seguradoras ativas
     */
    public function temSeguradoras()
    {
        return $this->seguradoras()->count() > 0;
    }

    /**
     * Verificar se tem cotações
     */
    public function temCotacoes()
    {
        return $this->cotacoes()->count() > 0;
    }

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

    /**
     * Obter todas as seguradoras disponíveis para esta corretora
     */
    public function seguradorasDisponiveis()
    {
        return Seguradora::whereNotIn('id', $this->seguradoras->pluck('id'))
                        ->orderBy('nome')
                        ->get();
    }
}