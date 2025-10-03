<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CorretoraSeguradora extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'corretora_seguradora';
    
    protected $fillable = [
        'corretora_id',
        'seguradora_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    
    /**
     * Corretora relacionada
     */
    public function corretora()
    {
        return $this->belongsTo(Corretora::class);
    }

    /**
     * Seguradora relacionada
     */
    public function seguradora()
    {
        return $this->belongsTo(Seguradora::class);
    }

    // Configuração de auditoria
    
    /**
     * Configurações do log de atividades
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['corretora_id', 'seguradora_id'])
            ->logOnlyDirty()
            ->useLogName('relacionamentos')
            ->setDescriptionForEvent(fn(string $eventName) => "Relacionamento corretora-seguradora {$eventName}")
            ->dontSubmitEmptyLogs();
    }

    /**
     * Customizar a descrição do log
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $corretoraNome = $this->corretora->nome ?? 'N/A';
        $seguradoraNome = $this->seguradora->nome ?? 'N/A';
        
        $actions = [
            'created' => 'vinculada à',
            'updated' => 'atualizada com',
            'deleted' => 'desvinculada da'
        ];
        
        $action = $actions[$eventName] ?? $eventName;
        
        return "Corretora '{$corretoraNome}' {$action} seguradora '{$seguradoraNome}'";
    }
}
