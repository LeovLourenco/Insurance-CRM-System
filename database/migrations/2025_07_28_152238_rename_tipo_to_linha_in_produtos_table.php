<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTipoToLinhaInProdutosTable extends Migration
{
    public function up()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->renameColumn('tipo', 'linha');
        });
    }

    public function down()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->renameColumn('linha', 'tipo');
        });
    }
}