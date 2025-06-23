<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtividadeCotacao extends Model
{
    protected $table = 'atividades_cotacao';
    protected $fillable = ['cotacao_id', 'descricao', 'user_id'];

    public function cotacao()
    {
        return $this->belongsTo(Cotacao::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}