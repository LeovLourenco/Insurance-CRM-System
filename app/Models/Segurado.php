<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segurado extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'documento',
        'telefone'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    
    /**
     * Cotações deste segurado
     */
    public function cotacoes()
    {
        return $this->hasMany(Cotacao::class);
    }

    /**
     
    // Scopes para facilitar consultas

    /**
     * Buscar por nome ou documento
     */
    public function scopeSearch($query, $search)
    {
        // Remove formatação do documento para busca
        $documentoLimpo = preg_replace('/[^0-9]/', '', $search);
        
        return $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('documento', 'like', "%{$search}%")
                    ->orWhere('documento', 'like', "%{$documentoLimpo}%")
                    ->orWhere('telefone', 'like', "%{$search}%");
    }

    /**
     * Filtrar por tipo de pessoa
     */
    public function scopeTipoPessoa($query, $tipo)
    {
        if ($tipo === 'F') {
            // CPF: 11 dígitos
            return $query->whereRaw('LENGTH(REGEXP_REPLACE(documento, "[^0-9]", "")) = 11');
        } elseif ($tipo === 'J') {
            // CNPJ: 14 dígitos
            return $query->whereRaw('LENGTH(REGEXP_REPLACE(documento, "[^0-9]", "")) = 14');
        }
        
        return $query;
    }

    /**
     * Segurados com cotações
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
     * Limpar e armazenar apenas números do documento
     */
    public function setDocumentoAttribute($value)
    {
        if ($value) {
            // Remove tudo que não é número
            $cleaned = preg_replace('/[^0-9]/', '', $value);
            $this->attributes['documento'] = $cleaned;
        } else {
            $this->attributes['documento'] = null;
        }
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
     * Accessor para documento formatado
     */
    public function getDocumentoFormatadoAttribute()
    {
        if (!$this->documento) return null;
        
        $documento = $this->documento;
        $length = strlen($documento);
        
        // CPF: 000.000.000-00
        if ($length == 11) {
            return substr($documento, 0, 3) . '.' . 
                   substr($documento, 3, 3) . '.' . 
                   substr($documento, 6, 3) . '-' . 
                   substr($documento, 9, 2);
        }
        // CNPJ: 00.000.000/0000-00
        elseif ($length == 14) {
            return substr($documento, 0, 2) . '.' . 
                   substr($documento, 2, 3) . '.' . 
                   substr($documento, 5, 3) . '/' . 
                   substr($documento, 8, 4) . '-' . 
                   substr($documento, 12, 2);
        }
        
        return $documento;
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

    /**
     * Accessor para tipo de pessoa
     */
    public function getTipoPessoaAttribute()
    {
        if (!$this->documento) return null;
        
        $length = strlen($this->documento);
        
        if ($length == 11) {
            return 'F'; // Pessoa Física
        } elseif ($length == 14) {
            return 'J'; // Pessoa Jurídica
        }
        
        return null;
    }

    /**
     * Accessor para tipo de pessoa por extenso
     */
    public function getTipoPessoaTextoAttribute()
    {
        switch ($this->tipo_pessoa) {
            case 'F':
                return 'Pessoa Física';
            case 'J':
                return 'Pessoa Jurídica';
            default:
                return 'Não identificado';
        }
    }

    // Métodos auxiliares

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
    public function cotacoesPorStatus()
    {
        return $this->cotacoes()
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');
    }

    /**
     * Última cotação
     */
    public function ultimaCotacao()
    {
        return $this->cotacoes()->latest()->first();
    }

    /**
     * Validar CPF
     */
    public static function validarCPF($cpf)
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) return false;
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) return false;
        
        // Calcula os dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        
        return true;
    }

    /**
     * Validar CNPJ
     */
    public static function validarCNPJ($cnpj)
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) return false;
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
        
        // Calcula primeiro dígito verificador
        $soma = 0;
        $peso = 2;
        for ($i = 11; $i >= 0; $i--) {
            $soma += $cnpj[$i] * $peso;
            $peso = $peso == 9 ? 2 : $peso + 1;
        }
        $digito1 = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
        
        // Calcula segundo dígito verificador
        $soma = 0;
        $peso = 2;
        for ($i = 12; $i >= 0; $i--) {
            $soma += $cnpj[$i] * $peso;
            $peso = $peso == 9 ? 2 : $peso + 1;
        }
        $digito2 = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
        
        return $cnpj[12] == $digito1 && $cnpj[13] == $digito2;
    }

    /**
     * Validar documento (CPF ou CNPJ)
     */
    public function validarDocumento()
    {
        if (!$this->documento) return false;
        
        $length = strlen($this->documento);
        
        if ($length == 11) {
            return self::validarCPF($this->documento);
        } elseif ($length == 14) {
            return self::validarCNPJ($this->documento);
        }
        
        return false;
    }
}