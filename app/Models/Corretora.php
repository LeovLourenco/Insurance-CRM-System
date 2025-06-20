<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corretora extends Model
{
    protected $fillable = ['nome', 'email', 'telefone'];

    public function vinculos()
    {
        return $this->hasMany(Vinculo::class);
    }
    public function cotacoes()
    {
        return $this->hasMany(Cotacao::class);
    }
    public function seguradoras()
    {
        return $this->belongsToMany(Seguradora::class, 'corretora_seguradora');
    }

}
