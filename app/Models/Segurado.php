<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segurado extends Model
{
    use HasFactory;
    protected $fillable = ['nome', 'documento', 'telefone'];

    public function cotacoes()
    {
        return $this->hasMany(Cotacao::class);
    }
}
