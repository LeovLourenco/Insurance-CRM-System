<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    use HasFactory;

    protected $fillable = ['corretora_id', 'produto'];

    public function corretora()
    {
        return $this->belongsTo(Corretora::class);
    }
}
