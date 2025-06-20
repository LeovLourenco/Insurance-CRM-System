<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguradora extends Model
{
    protected $fillable = ['nome', 'site', 'observacoes'];

    public function vinculos()
    {
        return $this->hasMany(Vinculo::class);
    }
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'seguradora_produto');
    }
    public function corretoras()
    {
        return $this->belongsToMany(Corretora::class, 'corretora_seguradora');
    }

}
