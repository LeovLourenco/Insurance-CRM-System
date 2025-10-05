<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SeguradoraProduto extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'seguradora_produto';

    protected $fillable = [
        'seguradora_id',
        'produto_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    public function seguradora()
    {
        return $this->belongsTo(Seguradora::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // Configuração de auditoria
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['seguradora_id', 'produto_id'])
            ->logOnlyDirty()
            ->useLogName('relacionamentos')
            ->setDescriptionForEvent(fn(string $eventName) => "Relacionamento seguradora-produto {$eventName}")
            ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        $seguradoraNome = $this->seguradora->nome ?? 'N/A';
        $produtoNome = $this->produto->nome ?? 'N/A';
        
        $actions = [
            'created' => 'passou a oferecer o produto',
            'updated' => 'atualizou o produto',
            'deleted' => 'deixou de oferecer o produto'
        ];
        
        $action = $actions[$eventName] ?? $eventName;
        
        return "Seguradora '{$seguradoraNome}' {$action} '{$produtoNome}'";
    }
}