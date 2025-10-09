<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Apolice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cotacao_id',
        'numero_apolice',
        'status',
        'nome_segurado',
        'cnpj_segurado',
        'nome_corretor',
        'cnpj_corretor',
        'seguradora_id',
        'segurado_id',
        'corretora_id',
        'produto_id',
        'usuario_id',
        'premio_liquido',
        'data_emissao',
        'inicio_vigencia',
        'fim_vigencia',
        'endosso',
        'parcela',
        'total_parcelas',
        'ramo',
        'linha_produto',
        'data_pagamento',
        'origem',
        'metodo_matching',
        'confianca_matching',
        'observacoes_endosso',
        'arquivo_sharepoint'
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'inicio_vigencia' => 'date',
        'fim_vigencia' => 'date',
        'data_pagamento' => 'date',
        'premio_liquido' => 'decimal:2',
        'parcela' => 'integer',
        'total_parcelas' => 'integer',
        'confianca_matching' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos

    /**
     * Cotação que originou esta apólice
     */
    public function cotacao()
    {
        return $this->belongsTo(Cotacao::class);
    }

    /**
     * Seguradora da apólice
     */
    public function seguradora()
    {
        return $this->belongsTo(Seguradora::class);
    }

    /**
     * Segurado da apólice
     */
    public function segurado()
    {
        return $this->belongsTo(Segurado::class);
    }

    /**
     * Corretora responsável
     */
    public function corretora()
    {
        return $this->belongsTo(Corretora::class);
    }

    /**
     * Produto da apólice
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Usuário responsável
     */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    /**
     * Apólices ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('status', 'ativa');
    }

    /**
     * Apólices importadas (não originadas de cotação)
     */
    public function scopeImportadas($query)
    {
        return $query->where('origem', 'importacao');
    }

    /**
     * Apólices que têm cotação associada
     */
    public function scopeComCotacao($query)
    {
        return $query->whereNotNull('cotacao_id');
    }

    /**
     * Apólices por status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Apólices com vigência em período
     */
    public function scopeVigentesEm($query, $data)
    {
        return $query->where('inicio_vigencia', '<=', $data)
                    ->where('fim_vigencia', '>=', $data);
    }

    // Métodos auxiliares

    /**
     * Verificar se apólice foi importada (não tem cotação)
     */
    public function ehImportada()
    {
        return $this->origem === 'importacao' || is_null($this->cotacao_id);
    }

    /**
     * Verificar se apólice veio de negociação (tem cotação)
     */
    public function ehDeNegociacao()
    {
        return $this->origem === 'cotacao' && !is_null($this->cotacao_id);
    }

    /**
     * Verificar se apólice está vigente
     */
    public function estaVigente($data = null)
    {
        $data = $data ?? now()->toDateString();
        
        return $this->status === 'ativa' 
            && $this->inicio_vigencia <= $data 
            && $this->fim_vigencia >= $data;
    }

    /**
     * Calcular dias restantes de vigência
     */
    public function diasRestantesVigencia()
    {
        if (!$this->fim_vigencia || $this->status !== 'ativa') {
            return null;
        }

        return now()->diffInDays($this->fim_vigencia, false);
    }

    /**
     * Verificar se precisa de renovação (próximo do vencimento)
     */
    public function precisaRenovacao($diasAntecedencia = 30)
    {
        $diasRestantes = $this->diasRestantesVigencia();
        
        return $diasRestantes !== null 
            && $diasRestantes <= $diasAntecedencia 
            && $diasRestantes >= 0;
    }

    // Accessors e Mutators

    /**
     * Formatar CNPJ do segurado
     */
    public function setCnpjSeguradoAttribute($value)
    {
        if ($value) {
            // Remove tudo que não é número
            $this->attributes['cnpj_segurado'] = preg_replace('/[^0-9]/', '', $value);
        } else {
            $this->attributes['cnpj_segurado'] = null;
        }
    }

    /**
     * Formatar CNPJ do corretor
     */
    public function setCnpjCorretorAttribute($value)
    {
        if ($value) {
            // Remove tudo que não é número
            $this->attributes['cnpj_corretor'] = preg_replace('/[^0-9]/', '', $value);
        } else {
            $this->attributes['cnpj_corretor'] = null;
        }
    }

    /**
     * Accessor para CNPJ formatado do segurado
     */
    public function getCnpjSeguradoFormatadoAttribute()
    {
        if (!$this->cnpj_segurado || strlen($this->cnpj_segurado) !== 14) {
            return $this->cnpj_segurado;
        }

        return substr($this->cnpj_segurado, 0, 2) . '.' .
               substr($this->cnpj_segurado, 2, 3) . '.' .
               substr($this->cnpj_segurado, 5, 3) . '/' .
               substr($this->cnpj_segurado, 8, 4) . '-' .
               substr($this->cnpj_segurado, 12, 2);
    }

    /**
     * Accessor para CNPJ formatado do corretor
     */
    public function getCnpjCorretorFormatadoAttribute()
    {
        if (!$this->cnpj_corretor || strlen($this->cnpj_corretor) !== 14) {
            return $this->cnpj_corretor;
        }

        return substr($this->cnpj_corretor, 0, 2) . '.' .
               substr($this->cnpj_corretor, 2, 3) . '.' .
               substr($this->cnpj_corretor, 5, 3) . '/' .
               substr($this->cnpj_corretor, 8, 4) . '-' .
               substr($this->cnpj_corretor, 12, 2);
    }

    /**
     * Accessor para status formatado
     */
    public function getStatusFormatadoAttribute()
    {
        $statusMap = [
            'pendente_emissao' => 'Pendente de Emissão',
            'ativa' => 'Ativa',
            'renovacao' => 'Em Renovação',
            'cancelada' => 'Cancelada'
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    /**
     * Configuração do ActivityLog
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'numero_apolice', 'status', 'nome_segurado', 'cnpj_segurado',
                'nome_corretor', 'cnpj_corretor', 'seguradora_id', 'segurado_id',
                'corretora_id', 'produto_id', 'usuario_id', 'premio_liquido',
                'data_emissao', 'inicio_vigencia', 'fim_vigencia', 'endosso',
                'parcela', 'total_parcelas', 'ramo', 'linha_produto',
                'data_pagamento', 'origem', 'metodo_matching', 'confianca_matching',
                'observacoes_endosso', 'arquivo_sharepoint'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Apólice foi {$eventName}");
    }
}
