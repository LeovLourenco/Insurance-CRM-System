<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCotacaosToCotacoesTable extends Migration
{
    public function up()
    {
        Schema::rename('cotacaos', 'cotacoes');
    }

    public function down()
    {
        Schema::rename('cotacoes', 'cotacaos');
    }
}