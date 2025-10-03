<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CadastroCorretor extends Model
{
    use HasFactory;
    
    protected $table = 'cadastros_corretores';
    
    protected $fillable = [
        'data_hora',
        'corretora',
        'cnpj',
        'email',
        'responsavel',
        'telefone',
        'seguradoras',
        'tipo'
    ];
    
    protected $casts = [
        'data_hora' => 'datetime',
    ];
    
    // Scopes para filtros
    public function scopeRecentes($query, $dias = 30)
    {
        return $query->where('data_hora', '>=', Carbon::now()->subDays($dias));
    }
    
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
    
    public function scopePorPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('data_hora', [$inicio, $fim]);
    }
    
    // Accessors para formatação
    public function getDataHoraFormatadaAttribute()
    {
        return $this->data_hora->format('d/m/Y H:i:s');
    }
    
    public function getCnpjFormatadoAttribute()
    {
        if (!$this->cnpj) return null;
        
        $cnpj = preg_replace('/\D/', '', $this->cnpj);
        if (strlen($cnpj) === 14) {
            return substr($cnpj, 0, 2) . '.' . 
                   substr($cnpj, 2, 3) . '.' . 
                   substr($cnpj, 5, 3) . '/' . 
                   substr($cnpj, 8, 4) . '-' . 
                   substr($cnpj, 12, 2);
        }
        
        return $this->cnpj;
    }
    
    public function getTelefoneFormatadoAttribute()
    {
        if (!$this->telefone) return null;
        
        $telefone = preg_replace('/\D/', '', $this->telefone);
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 5) . '-' . 
                   substr($telefone, 7, 4);
        } elseif (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 4) . '-' . 
                   substr($telefone, 6, 4);
        }
        
        return $this->telefone;
    }
    
    public function getSeguradorasFormatadaAttribute()
    {
        if (!$this->seguradoras) return null;
        
        // Dividir por vírgula, capitalizar cada seguradora e juntar novamente
        $seguradoras = explode(',', $this->seguradoras);
        $seguradorasFormatadas = array_map(function($seguradora) {
            $seguradora = trim($seguradora);
            
            // Casos especiais para siglas que devem permanecer maiúsculas
            $siglasEspeciais = ['HDI', 'BB', 'CEF', 'GPS'];
            
            foreach ($siglasEspeciais as $sigla) {
                if (strtoupper($seguradora) === $sigla) {
                    return $sigla;
                }
            }
            
            // Para outras seguradoras, usar title case
            return ucwords(strtolower($seguradora));
        }, $seguradoras);
        
        return implode(', ', $seguradorasFormatadas);
    }
}
