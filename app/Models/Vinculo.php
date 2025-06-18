<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vinculo extends Model
{
    protected $fillable = [
        'corretora_id',
        'seguradora_id',
        'produto_id',
        'canal',
        'observacoes',
    ];

    public function corretora()
    {
        return $this->belongsTo(Corretora::class);
    }

    public function seguradora()
    {
        return $this->belongsTo(Seguradora::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
