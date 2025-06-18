<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = ['nome', 'tipo', 'descricao'];

    public function vinculos()
    {
        return $this->hasMany(Vinculo::class);
    }
    public function cotacoes()
    {
        return $this->hasMany(Cotacao::class);
    }

}