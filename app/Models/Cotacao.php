<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    use HasFactory;

    protected $fillable = ['corretora_id', 'produto_id', 'seguradora_id', 'observacoes'];

    public function corretora()
    {
        return $this->belongsTo(Corretora::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
    public function atividades()
    {
        return $this->hasMany(AtividadeCotacao::class);
    }
}

