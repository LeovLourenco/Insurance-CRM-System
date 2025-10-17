<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LogCorretorAkad extends Model
{
    use HasFactory;

    protected $table = 'logs_corretores_akad';

    protected $fillable = [
        'corretor_akad_id',
        'evento',
        'descricao',
        'dados_anteriores',
        'dados_novos',
        'dados_extras',
        'ip_address',
        'user_agent',
        'origem',
        'documento_id',
        'evento_externo_id'
    ];

    protected $casts = [
        'dados_anteriores' => 'array',
        'dados_novos' => 'array',
        'dados_extras' => 'array',
    ];

    // Eventos constants
    const EVENTO_CADASTRO_CRIADO = 'cadastro_criado';
    const EVENTO_DADOS_ATUALIZADOS = 'dados_atualizados';
    const EVENTO_DOCUMENTO_ENVIADO = 'documento_enviado';
    const EVENTO_DOCUMENTO_ASSINADO = 'documento_assinado';
    const EVENTO_DOCUMENTO_RECUSADO = 'documento_recusado';
    const EVENTO_DOCUMENTO_EXPIRADO = 'documento_expirado';
    const EVENTO_DOCUMENTO_CANCELADO = 'documento_cancelado';
    const EVENTO_STATUS_ALTERADO = 'status_alterado';
    const EVENTO_CORRETOR_ATIVADO = 'corretor_ativado';
    const EVENTO_CORRETOR_DESATIVADO = 'corretor_desativado';
    const EVENTO_ERRO_API_AUTENTIQUE = 'erro_api_autentique';
    const EVENTO_TENTATIVA_REENVIO = 'tentativa_reenvio';

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
    public function scopePorEvento($query, $evento)
    {
        return $query->where('evento', $evento);
    }

    public function scopePorDocumento($query, $documentoId)
    {
        return $query->where('documento_id', $documentoId);
    }

    public function scopePorOrigem($query, $origem)
    {
        return $query->where('origem', $origem);
    }

    public function scopeDoSistema($query)
    {
        return $query->where('origem', 'sistema');
    }

    public function scopeDoWebhook($query)
    {
        return $query->where('origem', 'webhook');
    }

    public function scopeRecentes($query, $dias = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    public function scopeEventosDocumento($query)
    {
        return $query->whereIn('evento', [
            self::EVENTO_DOCUMENTO_ENVIADO,
            self::EVENTO_DOCUMENTO_ASSINADO,
            self::EVENTO_DOCUMENTO_RECUSADO,
            self::EVENTO_DOCUMENTO_EXPIRADO,
            self::EVENTO_DOCUMENTO_CANCELADO
        ]);
    }

    public function scopeEventosCorretor($query)
    {
        return $query->whereIn('evento', [
            self::EVENTO_CADASTRO_CRIADO,
            self::EVENTO_DADOS_ATUALIZADOS,
            self::EVENTO_STATUS_ALTERADO,
            self::EVENTO_CORRETOR_ATIVADO,
            self::EVENTO_CORRETOR_DESATIVADO
        ]);
    }

    public function scopeErros($query)
    {
        return $query->where('evento', self::EVENTO_ERRO_API_AUTENTIQUE);
    }

    /**
     * Accessors
     */
    public function getEventoDescricaoAttribute()
    {
        $descricoes = [
            self::EVENTO_CADASTRO_CRIADO => 'Cadastro Criado',
            self::EVENTO_DADOS_ATUALIZADOS => 'Dados Atualizados',
            self::EVENTO_DOCUMENTO_ENVIADO => 'Documento Enviado',
            self::EVENTO_DOCUMENTO_ASSINADO => 'Documento Assinado',
            self::EVENTO_DOCUMENTO_RECUSADO => 'Documento Recusado',
            self::EVENTO_DOCUMENTO_EXPIRADO => 'Documento Expirado',
            self::EVENTO_DOCUMENTO_CANCELADO => 'Documento Cancelado',
            self::EVENTO_STATUS_ALTERADO => 'Status Alterado',
            self::EVENTO_CORRETOR_ATIVADO => 'Corretor Ativado',
            self::EVENTO_CORRETOR_DESATIVADO => 'Corretor Desativado',
            self::EVENTO_ERRO_API_AUTENTIQUE => 'Erro API Autentique',
            self::EVENTO_TENTATIVA_REENVIO => 'Tentativa de Reenvio'
        ];

        return $descricoes[$this->evento] ?? 'Evento Desconhecido';
    }

    public function getEventoBadgeAttribute()
    {
        $badges = [
            self::EVENTO_CADASTRO_CRIADO => ['class' => 'bg-primary', 'icon' => 'bi-person-plus'],
            self::EVENTO_DADOS_ATUALIZADOS => ['class' => 'bg-info', 'icon' => 'bi-pencil'],
            self::EVENTO_DOCUMENTO_ENVIADO => ['class' => 'bg-warning', 'icon' => 'bi-send'],
            self::EVENTO_DOCUMENTO_ASSINADO => ['class' => 'bg-success', 'icon' => 'bi-check-circle'],
            self::EVENTO_DOCUMENTO_RECUSADO => ['class' => 'bg-danger', 'icon' => 'bi-x-circle'],
            self::EVENTO_DOCUMENTO_EXPIRADO => ['class' => 'bg-secondary', 'icon' => 'bi-clock'],
            self::EVENTO_DOCUMENTO_CANCELADO => ['class' => 'bg-dark', 'icon' => 'bi-trash'],
            self::EVENTO_STATUS_ALTERADO => ['class' => 'bg-info', 'icon' => 'bi-arrow-repeat'],
            self::EVENTO_CORRETOR_ATIVADO => ['class' => 'bg-success', 'icon' => 'bi-check'],
            self::EVENTO_CORRETOR_DESATIVADO => ['class' => 'bg-secondary', 'icon' => 'bi-pause'],
            self::EVENTO_ERRO_API_AUTENTIQUE => ['class' => 'bg-danger', 'icon' => 'bi-exclamation-triangle'],
            self::EVENTO_TENTATIVA_REENVIO => ['class' => 'bg-warning', 'icon' => 'bi-arrow-clockwise']
        ];

        return $badges[$this->evento] ?? ['class' => 'bg-secondary', 'icon' => 'bi-question'];
    }

    public function getTempoDecorridoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getOrigemBadgeAttribute()
    {
        $badges = [
            'sistema' => ['class' => 'bg-primary', 'text' => 'Sistema'],
            'webhook' => ['class' => 'bg-success', 'text' => 'Webhook'],
            'manual' => ['class' => 'bg-warning', 'text' => 'Manual'],
        ];

        return $badges[$this->origem] ?? ['class' => 'bg-secondary', 'text' => 'Desconhecido'];
    }

    /**
     * Métodos utilitários
     */
    public function isEventoDocumento()
    {
        return in_array($this->evento, [
            self::EVENTO_DOCUMENTO_ENVIADO,
            self::EVENTO_DOCUMENTO_ASSINADO,
            self::EVENTO_DOCUMENTO_RECUSADO,
            self::EVENTO_DOCUMENTO_EXPIRADO,
            self::EVENTO_DOCUMENTO_CANCELADO
        ]);
    }

    public function isEventoCorretor()
    {
        return in_array($this->evento, [
            self::EVENTO_CADASTRO_CRIADO,
            self::EVENTO_DADOS_ATUALIZADOS,
            self::EVENTO_STATUS_ALTERADO,
            self::EVENTO_CORRETOR_ATIVADO,
            self::EVENTO_CORRETOR_DESATIVADO
        ]);
    }

    public function isErro()
    {
        return $this->evento === self::EVENTO_ERRO_API_AUTENTIQUE;
    }

    public function temDadosAlterados()
    {
        return !empty($this->dados_anteriores) && !empty($this->dados_novos);
    }

    public function getDiferencas()
    {
        if (!$this->temDadosAlterados()) {
            return [];
        }

        $anterior = $this->dados_anteriores;
        $novo = $this->dados_novos;
        $diferencas = [];

        foreach ($novo as $campo => $valorNovo) {
            $valorAnterior = $anterior[$campo] ?? null;
            if ($valorAnterior !== $valorNovo) {
                $diferencas[$campo] = [
                    'anterior' => $valorAnterior,
                    'novo' => $valorNovo
                ];
            }
        }

        return $diferencas;
    }

    /**
     * Métodos estáticos para criação de logs
     */
    public static function logCadastro($corretorId, $dadosCorretor, $ip = null, $userAgent = null)
    {
        return static::create([
            'corretor_akad_id' => $corretorId,
            'evento' => self::EVENTO_CADASTRO_CRIADO,
            'descricao' => 'Novo corretor AKAD cadastrado no sistema',
            'dados_novos' => $dadosCorretor,
            'ip_address' => $ip ?: request()->ip(),
            'user_agent' => $userAgent ?: request()->userAgent(),
            'origem' => 'sistema'
        ]);
    }

    public static function logDocumentoEnviado($corretorId, $documentoId, $linkAssinatura)
    {
        return static::create([
            'corretor_akad_id' => $corretorId,
            'evento' => self::EVENTO_DOCUMENTO_ENVIADO,
            'descricao' => 'Documento enviado para assinatura via Autentique',
            'dados_extras' => [
                'documento_id' => $documentoId,
                'link_assinatura' => $linkAssinatura
            ],
            'documento_id' => $documentoId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'origem' => 'sistema'
        ]);
    }

    public static function logErroAPI($corretorId, $erro, $contexto = null)
    {
        return static::create([
            'corretor_akad_id' => $corretorId,
            'evento' => self::EVENTO_ERRO_API_AUTENTIQUE,
            'descricao' => 'Erro na comunicação com API Autentique: ' . $erro,
            'dados_extras' => [
                'erro' => $erro,
                'contexto' => $contexto,
                'timestamp' => now()->toISOString()
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'origem' => 'sistema'
        ]);
    }

    /**
     * Estatísticas
     */
    public static function estatisticasPorEvento($dias = 30)
    {
        return static::recentes($dias)
            ->selectRaw('evento, COUNT(*) as total')
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->toArray();
    }

    public static function estatisticasPorOrigem($dias = 30)
    {
        return static::recentes($dias)
            ->selectRaw('origem, COUNT(*) as total')
            ->groupBy('origem')
            ->pluck('total', 'origem')
            ->toArray();
    }

    public static function ultimosEventos($limite = 10)
    {
        return static::with('corretorAkad')
            ->latest()
            ->limit($limite)
            ->get();
    }
}