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
}
