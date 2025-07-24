<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSeguradoraIdFromCotacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotacoes', function (Blueprint $table) {
            // Só remover a coluna (não tem foreign key mesmo)
            $table->dropColumn('seguradora_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotacoes', function (Blueprint $table) {
            // Recriar a coluna se precisar fazer rollback
            $table->unsignedBigInteger('seguradora_id')->nullable();
            $table->foreign('seguradora_id')->references('id')->on('seguradoras');
        });
    }
}