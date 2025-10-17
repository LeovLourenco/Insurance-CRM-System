<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class CorretorAkad extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'corretores_akad';

    protected $fillable = [
        // Campos essenciais para FORM003
        'razao_social',        // Nome da corretora/empresa
        'cnpj',               // CNPJ da corretora
        'codigo_susep',       // Código SUSEP
        'email',              // Email corporativo
        'nome',               // Nome do responsável legal
        'telefone',           // Telefone de contato
        
        // Campos de sistema
        'status',
        'ip_address',
        'user_agent',
        'documento_enviado_em',
        'assinado_em',
        'recusado_em',
        'motivo_recusa',
        
        // Campos legados (agora opcionais)
        'cpf',
        'creci',
        'estado'
    ];

    protected $casts = [
        'documento_enviado_em' => 'datetime',
        'assinado_em' => 'datetime',
        'recusado_em' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'documento_enviado_em',
        'assinado_em',
        'recusado_em'
    ];

    // Status constants
    const STATUS_PENDENTE = 'pendente';
    const STATUS_DOCUMENTO_ENVIADO = 'documento_enviado';
    const STATUS_ASSINADO = 'assinado';
    const STATUS_RECUSADO = 'recusado';
    const STATUS_ATIVO = 'ativo';
    const STATUS_INATIVO = 'inativo';

    /**
     * Configuração do Log de Atividades
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nome', 'email', 'cpf', 'creci', 'estado', 
                'telefone', 'status', 'motivo_recusa'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relacionamentos
     */
    public function documentos()
    {
        return $this->hasMany(DocumentoAkad::class, 'corretor_akad_id');
    }

    public function logs()
    {
        return $this->hasMany(LogCorretorAkad::class, 'corretor_akad_id');
    }

    public function documentoAtivo()
    {
        return $this->hasOne(DocumentoAkad::class, 'corretor_akad_id')
                    ->whereIn('status', ['enviado', 'assinado'])
                    ->latest();
    }

    /**
     * Relação com eventos/logs do corretor via Spatie Activity Log
     * Permite acessar: $corretor->eventos
     */
    public function eventos()
    {
        return $this->morphMany(
            \Spatie\Activitylog\Models\Activity::class,
            'subject'
        )->orderBy('created_at', 'desc');
    }

    /**
     * Scopes
     */
    public function scopePendentes($query)
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    public function scopeComDocumentoEnviado($query)
    {
        return $query->where('status', self::STATUS_DOCUMENTO_ENVIADO);
    }

    public function scopeAssinados($query)
    {
        return $query->where('status', self::STATUS_ASSINADO);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeBusca($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
              ->orWhere('email', 'like', "%{$termo}%")
              ->orWhere('cpf', 'like', "%{$termo}%")
              ->orWhere('creci', 'like', "%{$termo}%");
        });
    }

    /**
     * Accessors & Mutators
     */
    public function setCpfAttribute($value)
    {
        // Remove formatação e aplica máscara
        $cpf = preg_replace('/\D/', '', $value);
        $this->attributes['cpf'] = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    public function getCpfLimpoAttribute()
    {
        return preg_replace('/\D/', '', $this->cpf);
    }

    public function setTelefoneAttribute($value)
    {
        // Remove formatação
        $telefone = preg_replace('/\D/', '', $value);
        
        // Aplica máscara dependendo do tamanho
        if (strlen($telefone) == 11) {
            $this->attributes['telefone'] = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        } elseif (strlen($telefone) == 10) {
            $this->attributes['telefone'] = preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        } else {
            $this->attributes['telefone'] = $value;
        }
    }

    public function getTelefoneLimpoAttribute()
    {
        return preg_replace('/\D/', '', $this->telefone);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDENTE => ['class' => 'bg-warning', 'text' => 'Pendente'],
            self::STATUS_DOCUMENTO_ENVIADO => ['class' => 'bg-info', 'text' => 'Documento Enviado'],
            self::STATUS_ASSINADO => ['class' => 'bg-success', 'text' => 'Assinado'],
            self::STATUS_RECUSADO => ['class' => 'bg-danger', 'text' => 'Recusado'],
            self::STATUS_ATIVO => ['class' => 'bg-primary', 'text' => 'Ativo'],
            self::STATUS_INATIVO => ['class' => 'bg-secondary', 'text' => 'Inativo'],
        ];

        return $badges[$this->status] ?? ['class' => 'bg-secondary', 'text' => 'Desconhecido'];
    }

    public function getEstadoNomeAttribute()
    {
        $estados = [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins'
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    /**
     * Métodos de verificação de status
     */
    public function isPendente()
    {
        return $this->status === self::STATUS_PENDENTE;
    }

    public function temDocumentoEnviado()
    {
        return $this->status === self::STATUS_DOCUMENTO_ENVIADO;
    }

    public function isAssinado()
    {
        return $this->status === self::STATUS_ASSINADO;
    }

    public function isRecusado()
    {
        return $this->status === self::STATUS_RECUSADO;
    }

    public function isAtivo()
    {
        return $this->status === self::STATUS_ATIVO;
    }

    public function isInativo()
    {
        return $this->status === self::STATUS_INATIVO;
    }

    /**
     * Métodos de ação
     */
    public function marcarDocumentoEnviado($documentoId, $linkAssinatura)
    {
        $this->update([
            'status' => self::STATUS_DOCUMENTO_ENVIADO,
            'documento_enviado_em' => now()
        ]);

        // Criar registro do documento
        return $this->documentos()->create([
            'documento_id' => $documentoId,
            'link_assinatura' => $linkAssinatura ?: 'Email enviado automaticamente',
            'status' => 'enviado'
        ]);
    }

    public function marcarAssinado($motivoRecusa = null)
    {
        $this->update([
            'status' => self::STATUS_ASSINADO,
            'assinado_em' => now(),
            'motivo_recusa' => null
        ]);
    }

    public function marcarRecusado($motivoRecusa)
    {
        $this->update([
            'status' => self::STATUS_RECUSADO,
            'recusado_em' => now(),
            'motivo_recusa' => $motivoRecusa
        ]);
    }

    public function ativar()
    {
        $this->update(['status' => self::STATUS_ATIVO]);
    }

    public function desativar()
    {
        $this->update(['status' => self::STATUS_INATIVO]);
    }

    /**
     * Validações
     */
    public static function validarCPF($cpf)
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }

    public function podeEnviarDocumento()
    {
        return in_array($this->status, [self::STATUS_PENDENTE, self::STATUS_RECUSADO]);
    }

    public function podeReenviarDocumento()
    {
        return $this->status === self::STATUS_RECUSADO;
    }

    /**
     * Logs personalizados
     */
    public function logEvento($evento, $descricao, $dadosExtras = null)
    {
        return $this->logs()->create([
            'evento' => $evento,
            'descricao' => $descricao,
            'dados_extras' => $dadosExtras,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'origem' => 'sistema'
        ]);
    }
}