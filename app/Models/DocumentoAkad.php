<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class DocumentoAkad extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'documentos_akad';

    protected $fillable = [
        'corretor_akad_id',
        'documento_id',
        'nome_documento',
        'link_assinatura',
        'status',
        'assinado_em',
        'recusado_em',
        'expirado_em',
        'motivo_recusa',
        'dados_autentique',
        'token_autentique',
        'data_assinatura',
        'dados_assinatura',
        'motivo_rejeicao',
        'dados_rejeicao',
        'finalizado_em',
        'dados_finalizacao',
        'dados_expiracao'
    ];

    protected $casts = [
        'dados_autentique' => 'array',
        'assinado_em' => 'datetime',
        'recusado_em' => 'datetime',
        'expirado_em' => 'datetime',
        'data_assinatura' => 'datetime',
        'dados_assinatura' => 'array',
        'dados_rejeicao' => 'array',
        'finalizado_em' => 'datetime',
        'dados_finalizacao' => 'array',
        'dados_expiracao' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'assinado_em',
        'recusado_em',
        'expirado_em',
        'data_assinatura',
        'finalizado_em'
    ];

    // Status constants
    const STATUS_ENVIADO = 'enviado';
    const STATUS_ASSINADO = 'assinado';
    const STATUS_RECUSADO = 'recusado';
    const STATUS_REJEITADO = 'rejeitado';
    const STATUS_EXPIRADO = 'expirado';
    const STATUS_FINALIZADO = 'finalizado';
    const STATUS_CANCELADO = 'cancelado';

    /**
     * Configuração do Log de Atividades
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status', 'assinado_em', 'recusado_em', 
                'expirado_em', 'motivo_recusa'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relacionamentos
     */
    public function corretorAkad()
    {
        return $this->belongsTo(CorretorAkad::class, 'corretor_akad_id');
    }

    /**
     * Scopes
     */
    public function scopeEnviados($query)
    {
        return $query->where('status', self::STATUS_ENVIADO);
    }

    public function scopeAssinados($query)
    {
        return $query->where('status', self::STATUS_ASSINADO);
    }

    public function scopeRecusados($query)
    {
        return $query->where('status', self::STATUS_RECUSADO);
    }

    public function scopeExpirados($query)
    {
        return $query->where('status', self::STATUS_EXPIRADO);
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', self::STATUS_CANCELADO);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', self::STATUS_ENVIADO);
    }

    public function scopePorDocumentoId($query, $documentoId)
    {
        return $query->where('documento_id', $documentoId);
    }

    /**
     * Accessors & Mutators
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_ENVIADO => ['class' => 'bg-warning', 'text' => 'Enviado'],
            self::STATUS_ASSINADO => ['class' => 'bg-success', 'text' => 'Assinado'],
            self::STATUS_RECUSADO => ['class' => 'bg-danger', 'text' => 'Recusado'],
            self::STATUS_EXPIRADO => ['class' => 'bg-secondary', 'text' => 'Expirado'],
            self::STATUS_CANCELADO => ['class' => 'bg-dark', 'text' => 'Cancelado'],
        ];

        return $badges[$this->status] ?? ['class' => 'bg-secondary', 'text' => 'Desconhecido'];
    }

    public function getTempoEnvioAttribute()
    {
        if ($this->status === self::STATUS_ENVIADO) {
            return $this->created_at->diffForHumans();
        }
        
        return null;
    }

    public function getTempoAssinaturaAttribute()
    {
        if ($this->assinado_em) {
            return $this->created_at->diffInHours($this->assinado_em);
        }
        
        return null;
    }

    public function getDiasParaExpirarAttribute()
    {
        if ($this->status === self::STATUS_ENVIADO && $this->expirado_em) {
            $diasRestantes = now()->diffInDays($this->expirado_em, false);
            return $diasRestantes > 0 ? $diasRestantes : 0;
        }
        
        return null;
    }

    public function getProximoExpiracaoAttribute()
    {
        if ($this->status === self::STATUS_ENVIADO) {
            // Autentique normalmente expira em 30 dias
            return $this->created_at->addDays(30);
        }
        
        return null;
    }

    /**
     * Métodos de verificação de status
     */
    public function isEnviado()
    {
        return $this->status === self::STATUS_ENVIADO;
    }

    public function isAssinado()
    {
        return $this->status === self::STATUS_ASSINADO;
    }

    public function isRecusado()
    {
        return $this->status === self::STATUS_RECUSADO;
    }

    public function isExpirado()
    {
        return $this->status === self::STATUS_EXPIRADO;
    }

    public function isCancelado()
    {
        return $this->status === self::STATUS_CANCELADO;
    }

    public function isPendente()
    {
        return $this->status === self::STATUS_ENVIADO;
    }

    public function isFinalizado()
    {
        return in_array($this->status, [
            self::STATUS_ASSINADO,
            self::STATUS_RECUSADO,
            self::STATUS_EXPIRADO,
            self::STATUS_CANCELADO
        ]);
    }

    /**
     * Métodos de ação
     */
    public function marcarAssinado($dadosAutentique = null)
    {
        $this->update([
            'status' => self::STATUS_ASSINADO,
            'assinado_em' => now(),
            'dados_autentique' => $dadosAutentique ? array_merge($this->dados_autentique ?? [], $dadosAutentique) : $this->dados_autentique
        ]);

        // Atualizar status do corretor
        $this->corretorAkad->marcarAssinado();

        // Log do evento
        $this->corretorAkad->logEvento(
            'documento_assinado',
            "Documento {$this->nome_documento} foi assinado",
            ['documento_id' => $this->documento_id]
        );

        return $this;
    }

    public function marcarRecusado($motivoRecusa, $dadosAutentique = null)
    {
        $this->update([
            'status' => self::STATUS_RECUSADO,
            'recusado_em' => now(),
            'motivo_recusa' => $motivoRecusa,
            'dados_autentique' => $dadosAutentique ? array_merge($this->dados_autentique ?? [], $dadosAutentique) : $this->dados_autentique
        ]);

        // Atualizar status do corretor
        $this->corretorAkad->marcarRecusado($motivoRecusa);

        // Log do evento
        $this->corretorAkad->logEvento(
            'documento_recusado',
            "Documento {$this->nome_documento} foi recusado: {$motivoRecusa}",
            ['documento_id' => $this->documento_id, 'motivo' => $motivoRecusa]
        );

        return $this;
    }

    public function marcarExpirado($dadosAutentique = null)
    {
        $this->update([
            'status' => self::STATUS_EXPIRADO,
            'expirado_em' => now(),
            'dados_autentique' => $dadosAutentique ? array_merge($this->dados_autentique ?? [], $dadosAutentique) : $this->dados_autentique
        ]);

        // Log do evento
        $this->corretorAkad->logEvento(
            'documento_expirado',
            "Documento {$this->nome_documento} expirou sem assinatura",
            ['documento_id' => $this->documento_id]
        );

        return $this;
    }

    public function cancelar($motivo = 'Cancelado pelo sistema')
    {
        $this->update([
            'status' => self::STATUS_CANCELADO,
            'motivo_recusa' => $motivo
        ]);

        // Log do evento
        $this->corretorAkad->logEvento(
            'documento_cancelado',
            "Documento {$this->nome_documento} foi cancelado: {$motivo}",
            ['documento_id' => $this->documento_id, 'motivo' => $motivo]
        );

        return $this;
    }

    /**
     * Métodos utilitários
     */
    public function podeReenviar()
    {
        return in_array($this->status, [self::STATUS_RECUSADO, self::STATUS_EXPIRADO]);
    }

    public function podeCancelar()
    {
        return $this->status === self::STATUS_ENVIADO;
    }

    public function getUrlAssinatura()
    {
        if ($this->status === self::STATUS_ENVIADO && $this->link_assinatura) {
            return $this->link_assinatura;
        }
        
        return null;
    }

    /**
     * Verificar se documento está próximo do vencimento
     */
    public function isProximoVencimento($diasAlerta = 5)
    {
        if ($this->status === self::STATUS_ENVIADO && $this->dias_para_expirar !== null) {
            return $this->dias_para_expirar <= $diasAlerta;
        }
        
        return false;
    }

    /**
     * Obter dados específicos do Autentique
     */
    public function getDadoAutentique($chave, $padrao = null)
    {
        if ($this->dados_autentique && isset($this->dados_autentique[$chave])) {
            return $this->dados_autentique[$chave];
        }
        
        return $padrao;
    }

    public function setDadoAutentique($chave, $valor)
    {
        $dados = $this->dados_autentique ?? [];
        $dados[$chave] = $valor;
        $this->update(['dados_autentique' => $dados]);
        
        return $this;
    }

    /**
     * Logs específicos do documento
     */
    public function logEvento($evento, $descricao, $dadosExtras = null)
    {
        return $this->corretorAkad->logs()->create([
            'evento' => $evento,
            'descricao' => $descricao,
            'dados_extras' => $dadosExtras,
            'documento_id' => $this->documento_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'origem' => 'sistema'
        ]);
    }

    /**
     * Estatísticas
     */
    public static function estatisticas()
    {
        return [
            'total' => static::count(),
            'enviados' => static::enviados()->count(),
            'assinados' => static::assinados()->count(),
            'recusados' => static::recusados()->count(),
            'expirados' => static::expirados()->count(),
            'cancelados' => static::cancelados()->count(),
            'taxa_assinatura' => static::count() > 0 ? round((static::assinados()->count() / static::count()) * 100, 2) : 0,
        ];
    }
}